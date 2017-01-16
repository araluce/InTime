<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use AppBundle\Utils\Usuario;
use AppBundle\Utils\DataManager;
use AppBundle\Utils\Utils;
use AppBundle\Utils\Twitter;
use AppBundle\Utils\Trabajo;
use AppBundle\Utils\Ejercicio;

class DefaultController extends Controller {

    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request) {

        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        if ($session->get('id_usuario') !== null) {
            Usuario::compruebaUsuario($doctrine, $session, '/guardian/registro');
            $usuario = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get('id_usuario'));

            return $this->inicio_usuario($usuario, $session);
        }
        return $this->render('index.html.twig', ['TITULO' => 'InTime - Login']);
    }

    /**
     *  @Route("/login", name="login")
     */
    public function loginAction(Request $request) {
        if ($request->getMethod() == 'POST') {
            $DNI = strtoupper($request->request->get('DNI'));
            $KEY = $request->request->get('KEY');

            $doctrine = $this->getDoctrine();
            $usuario = $doctrine->getRepository('AppBundle:Usuario')->findOneByDni($DNI);

            if ($usuario !== null && $usuario->getClave() === Usuario::encriptar($usuario, $KEY)) {
                //Crear sesión para el usuario logueado
                $session = $request->getSession();
                $session->set('id_usuario', $usuario->getIdUsuario());

                Usuario::activar_usuario($doctrine, $usuario);
                Usuario::compruebaUsuario($doctrine, $session, '/login');
                Ejercicio::actualizarEjercicioXUsuario($doctrine, $usuario);

                //Cargar el menu del usuario según rol
                return $this->inicio_usuario($usuario, $session);
            }
        }
        $DATOS = [
            'TITULO' => 'InTime - Login',
            'ERROR' => 'El DNI o contraseña introducidos no son correctos'
        ];

        return $this->render('index.html.twig', $DATOS);
    }

    /**
     * @Route("/logout", name="salir")
     */
    public function salirAction(Request $request) {

        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        Usuario::compruebaUsuario($doctrine, $session, '/logout');
        $session->remove('id_usuario');
        return new RedirectResponse('/');
    }

    /**
     * @Route("/ciudadano/trabajo/jornada_laboral", name="twitter")
     */
    public function twitter(Request $request, $msg = null) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/trabajo/jornada_laboral');
        if (!$status) {
            return new RedirectResponse('/');
        }

        $id_usuario = $session->get('id_usuario');
        $usuario = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($id_usuario);
        $tuiteros = $doctrine->getRepository('AppBundle:UsuarioXTuitero')->findByIdUsuario($usuario);
        $TWITEER = $request->request->get('TWITEER');
        if ($request->getMethod() == 'POST' && isset($TWITEER)) {
            $usuario_twitter = $TWITEER;
        } else {
            if ($tuiteros) {
                $usuario_twitter = $tuiteros[0]->getIdTuitero();
            }
        }
        $count = 10;

        $string = 0;
        if (isset($usuario_twitter)) {
            $string = [];
            $string = Twitter::twitter($usuario, $usuario_twitter, $count, $doctrine);
        }

        // Recargamos la lista de usuarios de twitter a la que sigue el jugador
        $tuiteros = $doctrine->getRepository('AppBundle:UsuarioXTuitero')->findByIdUsuario($usuario);

        $alias = [];
        $id_rol = $doctrine->getRepository('AppBundle:Rol')->findOneByIdRol(1);
        $resultados = $doctrine->getRepository('AppBundle:Usuario')->findByIdRol($id_rol);

        foreach ($resultados as $r) {
            if ($r !== $usuario && $r->getSeudonimo() !== null) {
                $res = [];
                $res['alias'] = $r->getSeudonimo();
                $res['id'] = $r->getIdUsuario();
                $alias[] = $res;
            }
        }
        $DATOS = DataManager::setDefaultData($doctrine, 'Jornada Laboral', $session);
        $DATOS['string'] = $string;
        $DATOS['twiteers'] = $tuiteros;
        $DATOS['ALIAS'] = $alias;
        if ($msg !== null) {
            $DATOS['info'] = [];
            $DATOS['info']['message'] = $msg['message'];
            $DATOS['info']['type'] = $msg['type'];
        }
        $DATOS['TDV'] = $usuario->getIdCuenta()->getTdv();
//        Utils::pretty_print($DATOS['string']);
        return $this->render('ciudadano/trabajo/twitter.html.twig', $DATOS);
    }

    /**
     * @Route("ciudadano/trabajo/service/store_tweet/{id_tweet}/{id_tuitero}/{tipo_tweet}/{usuario_share}/{texto_tweet}", name="almacenarTweet")
     */
    public function almacenarTweet($id_tweet, $id_tuitero, $tipo_tweet, $usuario_share, $texto_tweet) {
        preg_match("/@{1}[a-z,-,_]*/i", $texto_tweet, $coincidencias);
        if (count($coincidencias)) {
            $id_tuitero_real = str_replace('@', '', $coincidencias[0]);
        }
        $fecha = Twitter::getFecha($id_tweet);

        if (!$fecha) {
            $fecha = 'ERROR';
        }
        $doctrine = $this->getDoctrine();
        $id_usuario = $this->get('session')->get('id_usuario');
        $id_usuario_destino = $usuario_share;
        if ($usuario_share !== 'null') {
            $id_usuario_destino = Usuario::aliasToId($usuario_share, $doctrine);
        } else {
            $id_usuario_destino = null;
        }
        if (count($coincidencias)) {
            $respuesta = Twitter::almacenar_tweet($id_tuitero_real, $id_tweet, $tipo_tweet, $id_usuario, $id_usuario_destino, $fecha, $doctrine);
        } else {
            $respuesta = Twitter::almacenar_tweet($id_tuitero, $id_tweet, $tipo_tweet, $id_usuario, $id_usuario_destino, $fecha, $doctrine);
        }
        return new JsonResponse(array('datos' => $respuesta), 200);
    }

    /**
     * @Route("ciudadano/trabajo/service/show_tweet/{id_mochila}", name="showTweet")
     */
    public function showTweet($id_mochila) {
        $doctrine = $this->getDoctrine();
        $respuesta = Twitter::getTweetByIdMochila($id_mochila, $doctrine);
        return new JsonResponse($respuesta, 200);
    }

    /**
     * @Route("compartir", name="compartir")
     */
    public function compartir(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/compartir');
        if (!$status) {
            return new RedirectResponse('/');
        }

        if ($request->getMethod() == 'POST') {
            $lista_usuarios_id = $request->request->get('share_list');
            $id_tweet = $request->request->get('id_tweet');
            $id_tuitero = $request->request->get('id_tuitero');
            $fecha = Twitter::getFecha($id_tweet);
            $id_usuario = $this->get('session')->get('id_usuario');
            if (!empty($lista_usuarios_id)) {
                foreach ($lista_usuarios_id as $id_usuario_destino) {
                    $respuesta = Twitter::almacenar_tweet($id_tuitero, $id_tweet, 4, $id_usuario, $id_usuario_destino, $fecha, $doctrine);
                }
            }
        }

        return new RedirectResponse('/ciudadano/trabajo/jornada_laboral');
    }

    /**
     * @Route("/ciudadano/trabajo", name="trabajo")
     */
    public function trabajo_ciudadanoAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/trabajo');

        if (!$status) {
            return new RedirectResponse('/');
        }
        $DATOS = DataManager::setDefaultData($doctrine, 'Trabajo', $session);
        return $this->render('ciudadano/extensiones/trabajo.html.twig', $DATOS);
    }

    /**
     * @Route("/ciudadano/trabajo/inspeccion", name="inspeccion")
     */
    public function inspeccion_trabajoAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/trabajo/inspeccion');
        if (!$status) {
            return new RedirectResponse('/');
        }
        $DATOS = DataManager::setDefaultData($doctrine, 'Inspección de trabajo', $session);
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get('id_usuario'));
        Ejercicio::actualizarEjercicioXUsuario($doctrine, $USUARIO);
        $EJERCICIO_SECCION = $doctrine->getRepository('AppBundle:EjercicioSeccion')->findOneByIdEjercicioSeccion(1);
        $EJERCICIO_X_GRUPO = $doctrine->getRepository('AppBundle:EjercicioXGrupo')->findAll();
        $EJERCICIOS = $doctrine->getRepository('AppBundle:Ejercicio')->findByIdEjercicioSeccion($EJERCICIO_SECCION);
        $DATOS['SECCION'] = $EJERCICIO_SECCION->getSeccion();
        $DATOS['EJERCICIOS'] = [];

        $ids_ejercicios = [];

        if (count($EJERCICIO_X_GRUPO)) {
            $ids_ejercicios_grupos = [];
            foreach ($EJERCICIO_X_GRUPO as $ejercicio_grupo) {
                $ejercicio = $ejercicio_grupo->getIdEjercicio();
                if (!in_array($ejercicio_grupo->getIdGrupoEjercicios(), $ids_ejercicios_grupos)) {
                    $ids_ejercicios_grupos[] = $ejercicio_grupo->getIdGrupoEjercicios();
                    $DATOS['EJERCICIOS'][] = Utils::getDatosInspeccion($doctrine, $USUARIO, null, $ejercicio_grupo->getIdGrupoEjercicios());
                }
                $ids_ejercicios[] = $ejercicio->getIdEjercicio();
            }
        }
        if (count($EJERCICIOS)) {
            foreach ($EJERCICIOS as $ejercicio) {
                if (!in_array($ejercicio->getIdEjercicio(), $ids_ejercicios)) {
                    $DATOS['EJERCICIOS'][] = Utils::getDatosInspeccion($doctrine, $USUARIO, $ejercicio, null);
                }
            }
        }

        return $this->render('ciudadano/trabajo/inspeccion_trabajo.html.twig', $DATOS);
    }

    /**
     * @Route("/ciudadano/trabajo/paga_extra", name="paga_extra")
     */
    public function paga_extraAction(Request $request, $mensaje = null) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/trabajo/paga_extra');
        if (!$status) {
            return new RedirectResponse('/');
        }
        $DATOS = DataManager::setDefaultData($doctrine, 'Paga extra', $session);
        if ($mensaje !== null) {
            $DATOS['info'] = $mensaje['info'];
        }
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get('id_usuario'));
        Ejercicio::actualizarEjercicioXUsuario($doctrine, $USUARIO);
        $EJERCICIO_SECCION = $doctrine->getRepository('AppBundle:EjercicioSeccion')->findOneByIdEjercicioSeccion(2);
        $EJERCICIOS = $doctrine->getRepository('AppBundle:Ejercicio')->findByIdEjercicioSeccion($EJERCICIO_SECCION);
        $DATOS['SECCION'] = $EJERCICIO_SECCION->getSeccion();
        $DATOS['EJERCICIOS'] = [];
        foreach ($EJERCICIOS as $ejercicio) {
            $DATOS['EJERCICIOS'][] = \AppBundle\Utils\Utils::getDatosPaga($doctrine, $USUARIO, $ejercicio);
        }
        return $this->render('ciudadano/trabajo/inspeccion_trabajo.html.twig', $DATOS);
    }

    /**
     * @Route("/arena/{id_ejercicio}", name="arenatest")
     */
    public function arenaAction(Request $request, $id_ejercicio) {
        $session = $this->get('session');
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/arena/' . $id_ejercicio);
        if (!$status) {
            return new RedirectResponse('/');
        }
        $DATOS = DataManager::setDefaultData($doctrine, 'Desafío', $this->get('session'));
        $id_usuario = $session->get('id_usuario');

        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($id_usuario);
        $EJERCICIO = $doctrine->getRepository('AppBundle:Ejercicio')->findOneByIdEjercicio($id_ejercicio);
        $SECCION = $EJERCICIO->getIdEjercicioSeccion();

        if ($EJERCICIO->getIdTipoEjercicio()->getIdTipoEjercicio() !== 3) {
            Utils::getDatosArenaTest($doctrine, $USUARIO, $EJERCICIO, $DATOS);
            $EJERCICIO_X_USUARIO = $doctrine->getRepository('AppBundle:EjercicioXUsuario')->findOneBy([
                'idEjercicio' => $EJERCICIO,
                'idUsu' => $USUARIO
            ]);
        } else {
            $EJERCICIO_X_GRUPO = $doctrine->getRepository('AppBundle:EjercicioXGrupo')->findOneByIdEjercicio($EJERCICIO);
            $GRUPO = $EJERCICIO_X_GRUPO->getIdGrupoEjercicios();
            $EJERCICIO_X_USUARIO = $doctrine->getRepository('AppBundle:EjercicioXUsuario')->findOneBy([
                'idGrupo' => $GRUPO,
                'idUsu' => $USUARIO
            ]);
            $d = Utils::getDatosArenaGrupoTest($doctrine, $USUARIO, $GRUPO);
            $DATOS['EJERCICIOS'] = $d['EJERCICIOS'];
            $DATOS['SECCION'] = $d['SECCION'];
            $DATOS['TIPO'] = $d['TIPO'];
        }
        if ($request->getMethod() == 'POST') {
            $seccion_texto = $SECCION->getSeccion();
            //$n_intentos = 0;
            if ($DATOS['TIPO'] === 'test') {
                if ($seccion_texto === 'inspeccion_trabajo' || $seccion_texto === 'paga_extra') {
                    $n_intentos = count($this->getDoctrine()->getRepository('AppBundle:EjercicioCalificacion')->findByIdEjercicio($EJERCICIO));
                }
                if (!$n_intentos) {
                    $RESP = [];
                    $RESP[] = $request->request->get('RESPUESTAS_CHECK_1');
                    $RESP[] = $request->request->get('RESPUESTAS_CHECK_2');
                    $RESP[] = $request->request->get('RESPUESTAS_CHECK_3');
                    $RESP[] = $request->request->get('RESPUESTAS_CHECK_4');
                    $cont = 0;
                    $nota = 0;
                    foreach ($DATOS['RESPUESTAS'] as $r) {
                        if (($RESP[$cont] === 'on' && $r['CORRECTA']) || ( $RESP[$cont] === null && !$r['CORRECTA'] )) {
                            $nota++;
                        }
                        $cont++;
                    }
                    $nota = ($nota * 10) / 4;
                    $DATOS['RESULTADO'] = Utils::setNota($doctrine, $USUARIO, null, $EJERCICIO, $nota);
                } else {
                    $DATOS['RESULTADO']['TITULO'] = 'Error';
                    $DATOS['RESULTADO']['NOTA_TEXTO'] = 'Este ejercicio ya ha sido evaluado';
                    $DATOS['RESULTADO']['NOTA_ICONO'] = 'error.jpg';
                    $DATOS['RESULTADO']['NOTA_ID'] = 0;
                }
            }
            if ($DATOS['TIPO'] === 'grupo_test') {
                if ($seccion_texto === 'inspeccion_trabajo' || $seccion_texto === 'paga_extra') {
                    $n_intentos = count($this->getDoctrine()->getRepository('AppBundle:EjercicioCalificacion')->findByIdGrupo($GRUPO));
                }
                if (!$n_intentos) {
                    $numero_enunciados = $request->request->get('N_EJERCICIO');
                    $RESP = [];
                    for ($i = 1; $i <= $numero_enunciados; $i++) {
                        $aux = [];
                        $aux[] = $request->request->get('RESPUESTAS_CHECK_' . $i . '1');
                        $aux[] = $request->request->get('RESPUESTAS_CHECK_' . $i . '2');
                        $aux[] = $request->request->get('RESPUESTAS_CHECK_' . $i . '3');
                        $aux[] = $request->request->get('RESPUESTAS_CHECK_' . $i . '4');
                        $RESP[] = $aux;
                    }
                    $cont_ejercicio = 0;
                    $nota_4 = 0;
                    foreach ($DATOS['EJERCICIOS'] as $EJERCICIO) {
                        $cont_resp = 0;
                        $nota = 0;
                        foreach ($EJERCICIO['RESPUESTAS'] as $r) {
                            if (($RESP[$cont_ejercicio][$cont_resp] === 'on' && $r['CORRECTA']) || ( $RESP[$cont_ejercicio][$cont_resp] === null && !$r['CORRECTA'] )) {
                                $nota++;
                            }
                            $cont_resp++;
                        }
                        $nota_4 += $nota;
                        $cont_ejercicio++;
                    }
                    $nota = (($nota_4 / $cont_ejercicio) * 10) / 4;
                    $DATOS['RESULTADO'] = Utils::setNota($doctrine, $USUARIO, $GRUPO, null, $nota);
                } else {
                    $DATOS['RESULTADO']['TITULO'] = 'Error';
                    $DATOS['RESULTADO']['NOTA_TEXTO'] = 'Este ejercicio ya ha sido evaluado';
                    $DATOS['RESULTADO']['NOTA_ICONO'] = 'error.jpg';
                    $DATOS['RESULTADO']['NOTA_ID'] = 0;
                }
            }
            if ($DATOS['TIPO'] === 'entrega') {
                $NOTA = $doctrine->getRepository('AppBundle:Constante')->findOneByClave('nota_por_defecto');
                $DATOS['RESULTADO'] = Utils::setNota($doctrine, $USUARIO, null, $EJERCICIO, intval($NOTA->getValor()));
                $EJERCICIO_CALIFICACION = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findOneBy([
                    'idUsuario' => $USUARIO, 'idEjercicio' => $EJERCICIO
                ]);
                $ENTREGA = $request->files->get('ENTREGA');
                if ($ENTREGA->getClientSize() < $ENTREGA->getMaxFilesize()) {
                    $ruta = 'USUARIOS/' . $USUARIO->getDni() . '/' . $SECCION->getSeccion() . '/' . $EJERCICIO_CALIFICACION->getIdEjercicioCalificacion();
                    if (!file_exists($ruta)) {
                        mkdir($ruta, 0777, true);
                    }
                    $nombre_entrega = $EJERCICIO_CALIFICACION->getFecha()->getTimestamp()
                            . $ENTREGA->getClientOriginalName();
                    $UPLOAD = $ENTREGA->move($ruta, $nombre_entrega);
                    if ($ENTREGA->getError()) {
                        $MENSAJE['info'] = [];
                        $MENSAJE['info']['message'] = getErrorMessage();
                        $MENSAJE['info']['type'] = 'danger';
                        $em->remove($EJERCICIO_CALIFICACION);
                        $em->flush();
                        switch ($SECCION->getSeccion()) {
                            case 'paga_extra':
                                return $this->paga_extraAction($request, $MENSAJE);
                            case 'comida':
                                return $this->paga_extraAction($request, $MENSAJE);
                            case 'bebida':
                                return $this->paga_extraAction($request, $MENSAJE);
                        }
                    }
                    $EJERCICIO_ENTREGA = $doctrine->getRepository('AppBundle:EjercicioEntrega')->findOneBy([
                        'idUsuario' => $USUARIO, 'idEjercicio' => $EJERCICIO
                    ]);
                    if (!count($EJERCICIO_ENTREGA)) {
                        $EJERCICIO_ENTREGA = new \AppBundle\Entity\EjercicioEntrega();
                    }
                    $EJERCICIO_ENTREGA->setIdUsuario($USUARIO);
                    $EJERCICIO_ENTREGA->setIdEjercicio($EJERCICIO);
                    $EJERCICIO_ENTREGA->setNombre($EJERCICIO_CALIFICACION->getFecha()->getTimestamp() . $ENTREGA->getClientOriginalName());
                    $EJERCICIO_ENTREGA->setMime($ENTREGA->getClientMimeType());
                    $EJERCICIO_ENTREGA->setFecha($EJERCICIO_CALIFICACION->getFecha());
                    $em->persist($EJERCICIO_ENTREGA);
                    $em->flush();
                } else {
                    $MENSAJE['info'] = [];
                    $MENSAJE['info']['message'] = 'El archivo que intenta subir supera el tamaño máximo permitido.'
                            . '          Tu archivo: ' . $ENTREGA->getClientSize() / 1024
                            . '          Tamaño máx: ' . $ENTREGA->getMaxFilesize() / 1024;
                    $MENSAJE['info']['type'] = 'danger';
                    $em->remove($EJERCICIO_CALIFICACION);
                    $em->flush();
                    switch ($SECCION->getSeccion()) {
                        case 'paga_extra':
                            return $this->paga_extraAction($request, $MENSAJE);
                        case 'comida':
                            return $this->paga_extraAction($request, $MENSAJE);
                        case 'bebida':
                            return $this->paga_extraAction($request, $MENSAJE);
                    }
                }
                switch ($SECCION->getSeccion()) {
                    case 'paga_extra':
                        return $this->paga_extraAction($request);
                    case 'comida':
                        return $this->paga_extraAction($request);
                    case 'bebida':
                        return $this->paga_extraAction($request);
                }
            }
            //Utils::pretty_print($DATOS);
            return $this->render('ciudadano/extensiones/resumen.twig', $DATOS);
        }

        return $this->render('ciudadano/extensiones/arena.twig', $DATOS);
    }

    /**
     * @Route("/solicitar/{id_ejercicio}", name="solicitar")
     */
    public function solicitarAction(Request $request, $id_ejercicio) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        Usuario::compruebaUsuario($doctrine, $session, '/solicitar/' . $id_ejercicio);

        $id_usuario = $session->get('id_usuario');
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($id_usuario);
        $EJERCICIO = $doctrine->getRepository('AppBundle:Ejercicio')->findOneByIdEjercicio($id_ejercicio);
        $SECCION = $EJERCICIO->getIdEjercicioSeccion()->getSeccion();

        switch ($SECCION) {
            case 'paga_extra':
                return Trabajo::solicitar_paga($doctrine, $USUARIO, $EJERCICIO);
            case 'comida':
                return Trabajo::solicitar_alimentacion($doctrine, $USUARIO, $EJERCICIO, $SECCION);
            case 'bebida':
                return Trabajo::solicitar_alimentacion($doctrine, $USUARIO, $EJERCICIO, $SECCION);
        }
    }

    /**
     * @Route("/ciudadano/ocio", name="ocio")
     */
    public function ocio_ciudadanoAction(Request $request) {


        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/ocio');
        if (!$status) {
            return new RedirectResponse('/');
        }
        $DATOS = DataManager::setDefaultData($doctrine, 'Ocio', $session);
        return $this->render('ciudadano/extensiones/ocio.html.twig', $DATOS);
    }

    /**
     * @Route("/ciudadano/ocio/amigos", name="amigos")
     */
    public function amigos_ciudadanoAction(Request $request) {


        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/coidadano/ocio/amigos');
        if (!$status) {
            return new RedirectResponse('/');
        }
        $DATOS = DataManager::setDefaultData($doctrine, 'Amigos', $session);
        return $this->render('ciudadano/ocio/amigos.html.twig', $DATOS);
    }

    /**
     * @Route("/ciudadano/prestamos", name="prestamos")
     */
    public function prestamos_ciudadanoAction(Request $request) {


        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/prestamos');
        if (!$status) {
            return new RedirectResponse('/');
        }
        $DATOS = DataManager::setDefaultData($doctrine, 'Préstamos', $session);
        return $this->render('ciudadano/extensiones/prestamos.html.twig', $DATOS);
    }

    /**
     * @Route("/guardian/censo/registro", name="registro")
     */
    public function registroAction(Request $request) {

        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/censo/registro', true);
        if (!$status) {
            return new RedirectResponse('/');
        }
        $DATOS = [];
        if ($request->getMethod() == 'POST') {
            $DNIs = strtoupper($request->request->get('DATOS_REGISTRO'));
            $DNIs = explode(PHP_EOL, $DNIs);
            // Recuperamos las horas y minutos marcados y añadimos los segundos 00
            $TDV = str_replace("T", " ", $request->request->get('TDV')) . ":00";

            $resultado = Usuario::registrar_multiple($doctrine, $DNIs, $TDV);

            $DATOS['TITULO'] = 'Registro';
            $DATOS['info'] = [];

            if (count($resultado['correctos'])) {
                $DATOS['info']['correctos'] = '';
                foreach ($resultado['correctos'] as $r) {
                    $DATOS['info']['correctos'] = $DATOS['info']['correctos'] . $r . ' ';
                }
            }
            if (count($resultado['incorrectos'])) {
                $DATOS['info']['incorrectos'] = '';
                foreach ($resultado['incorrectos'] as $r) {
                    $DATOS['info']['incorrectos'] = $DATOS['info']['incorrectos'] . $r . ' ';
                }
            }
            if (count($resultado['repetidos'])) {
                $DATOS['info']['repetidos'] = '';
                foreach ($resultado['repetidos'] as $r) {
                    $DATOS['info']['repetidos'] = $DATOS['info']['repetidos'] . $r . ' ';
                }
            }

            if ($resultado['error']) {
                $DATOS['info']['type'] = 'danger';
            } else {
                $DATOS['info']['type'] = 'succes';
            }
        }
        $DATOS['TITULO'] = 'InTime - Censo';
        $DATOS['SECCION'] = 'CENSO';
        return $this->render('guardian/registro.html.twig', $DATOS);
    }

    /**
     * 
     * @Route("/guardian/censo/ciudadanos", name="ciudadanos")
     */
    public function ciudadanosAction(Request $request) {

        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $DATOS = [];
        // Comprobamos que el usuario es admin, si no, redireccionamos a /
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/censo/ciudadanos', true);
        if (!$status) {
            return new RedirectResponse('/');
        }
        $ROL = $doctrine->getRepository('AppBundle:Rol')->findOneByNombre('Jugador');
        $DATOS['JUGADORES'] = $doctrine->getRepository('AppBundle:Usuario')->findByIdRol($ROL);
        $DATOS['TITULO'] = 'Gestión de distritos';
        $DATOS['SECCION'] = 'CIUDADANOS';
        //\AppBundle\Utils\Utils::pretty_print($DATOS);
        return $this->render('guardian/registro.html.twig', $DATOS);
    }

    /**
     * 
     * @Route("/guardian/obtenerCiudadanosDistrito/{idDistrito}", name="obtenerCiudadanosDistrito")
     */
    public function obtenerCiudadanosDistritoAction(Request $request, $idDistrito) {

        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $DATOS = [];
        // Comprobamos que el usuario es admin, si no, redireccionamos a /
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/obtenerCiudadanosDistritoAction/' . $idDistrito, true);
        if (!$status) {
            return new JsonResponse(array('respuesta' => 'error'), 200);
        }
        $ROL = $doctrine->getRepository('AppBundle:Rol')->findOneByNombre('Jugador');
        $DISTRITO = $doctrine->getRepository('AppBundle:UsuarioDistrito')->findOneByIdUsuarioDistrito($idDistrito);
        $DATOS['respuesta'] = 'OK';

        if ($ROL === null) {
            return new JsonResponse(array('respuesta' => 'ERROR - ROL (Jugador) no existe'), 200);
        }
        if ($DISTRITO === null) {
            return new JsonResponse(array('respuesta' => 'ERROR - DISTRITO ' . $idDistrito . ' no existe'), 200);
        }

        $CIUDADANOS = $doctrine->getRepository('AppBundle:Usuario')->findBy([
            'idRol' => $ROL, 'idDistrito' => $DISTRITO
        ]);
        $DATOS['ciudadanos'] = [];
        if (count($CIUDADANOS)) {
            foreach ($CIUDADANOS as $CIUDADANO) {
                $aux = [];
                $aux['DNI'] = $CIUDADANO->getDni();
                $aux['SEUDONIMO'] = $CIUDADANO->getSeudonimo();
                $aux['NOMBRE'] = $CIUDADANO->getNombre();
                $aux['APELLIDOS'] = $CIUDADANO->getApellidos();
                $aux['ID'] = $CIUDADANO->getIdUsuario();
                if ($aux['SEUDONIMO'] !== null && $aux['NOMBRE'] !== null && $aux['APELLIDOS'] !== null) {
                    $DATOS['ciudadanos'][] = $aux;
                }
            }
        } else {
            $DATOS['respuesta'] = 'OK';
            $DATOS['ciudadanos'][] = '<div class="col-md-12" style="width: 100%; font-size: 13px; font-weight: bold; text-transform: uppercase;">'
                    . 'En el distrito <span style="color:red;">' . $DISTRITO->getNombre() . '</span> aún no hay usuarios'
                    . '</div>';
        }

        return new JsonResponse($DATOS, 200);
    }

    /**
     * 
     * @Route("/guardian/obtenerCiudadanosSinDistrito", name="obtenerCiudadanosSinDistrito")
     */
    public function obtenerCiudadanosSinDistritoAction(Request $request) {

        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $DATOS = [];
        // Comprobamos que el usuario es admin, si no, redireccionamos a /
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/obtenerCiudadanosDistritoDistritoAction', true);
        if (!$status) {
            return new JsonResponse(array('respuesta' => 'error'), 200);
        }
        $ROL = $doctrine->getRepository('AppBundle:Rol')->findOneByNombre('Jugador');
        $DATOS['respuesta'] = 'OK';

        if ($ROL === null) {
            $DATOS['respuesta'] = 'ERROR - ROL Jugador no existe';
        }

        $CIUDADANOS = $doctrine->getRepository('AppBundle:Usuario')->findBy([
            'idRol' => $ROL, 'idDistrito' => null
        ]);
        $DATOS['ciudadanos'] = [];
        if (count($CIUDADANOS)) {
            foreach ($CIUDADANOS as $CIUDADANO) {
                $aux = [];
                $aux['DNI'] = $CIUDADANO->getDni();
                $aux['SEUDONIMO'] = $CIUDADANO->getSeudonimo();
                $aux['NOMBRE'] = $CIUDADANO->getNombre();
                $aux['APELLIDOS'] = $CIUDADANO->getApellidos();
                $aux['ID'] = $CIUDADANO->getIdUsuario();
                if ($aux['SEUDONIMO'] !== null && $aux['NOMBRE'] !== null && $aux['APELLIDOS'] !== null) {
                    $DATOS['ciudadanos'][] = $aux;
                }
            }
        } else {
            $DATOS['respuesta'] = 'OK';
            $DATOS['ciudadanos'][] = '<div class="col-md-12" style="width: 100%; font-size: 13px; font-weight: bold; text-transform: uppercase;">Todos los ciudadanos tienen un distrito asignado<br>'
                    . '<center>Si echa de menos a algún ciudadano en esta lista puede'
                    . 'deberse a que el ciudadano no haya rellenado sus datos de perfil</center></div>';
        }

        return new JsonResponse($DATOS, 200);
    }

    /**
     * 
     * @Route("/guardian/aniadeCiudadanoADistrito/{idCiudadano}/{idDistrito}", name="aniadeCiudadanoADistrito")
     */
    public function aniadeCiudadanoADistritoAction(Request $request, $idCiudadano, $idDistrito) {

        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $em = $doctrine->getManager();

        // Comprobamos que el usuario es admin, si no, redireccionamos a /
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/aniadeCiudadanoADistrito/' . $idCiudadano . '/' . $idDistrito, true);
        if (!$status) {
            return new JsonResponse(array('respuesta' => 'error'), 200);
        }
        $ROL = $doctrine->getRepository('AppBundle:Rol')->findOneByNombre('Jugador');
        $DISTRITO = $doctrine->getRepository('AppBundle:UsuarioDistrito')->findOneByIdUsuarioDistrito($idDistrito);
        $CIUDADANO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($idCiudadano);

        if ($ROL === null) {
            return new JsonResponse(array('respuesta' => 'ERROR - El rol Jugador no existe'), 200);
        }
        if ($DISTRITO === null) {
            return new JsonResponse(array('respuesta' => 'ERROR - DISTRITO ' . $idDistrito . ' no existe'), 200);
        }
        if ($CIUDADANO === null) {
            return new JsonResponse(array('respuesta' => 'ERROR - CIUDADANO ' . $idCiudadano . ' no existe'), 200);
        }

        $CIUDADANO->setIdDistrito($DISTRITO);
        $em->persist($CIUDADANO);
        $em->flush();

        return new JsonResponse(array('respuesta' => 'OK'), 200);
    }

    /**
     * 
     * @Route("/guardian/censo/distritos", name="distritos")
     */
    public function distritosAction(Request $request) {

        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $DATOS = [];
        // Comprobamos que el usuario es admin, si no, redireccionamos a /
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/censo/distritos', true);
        if (!$status) {
            return new RedirectResponse('/');
        }
        $ROL = $doctrine->getRepository('AppBundle:Rol')->findOneByNombre('Jugador');
        $DATOS['DISTRITOS'] = $doctrine->getRepository('AppBundle:UsuarioDistrito')->findAll();
        $DATOS['JUGADORES'] = $doctrine->getRepository('AppBundle:Usuario')->findByIdRol($ROL);
        $DATOS['TITULO'] = 'Gestión de distritos';
        $DATOS['SECCION'] = 'DISTRITOS';
        //\AppBundle\Utils\Utils::pretty_print($DATOS);
        return $this->render('guardian/registro.html.twig', $DATOS);
    }

    /**
     * 
     * @Route("/guardian/censo/creardistrito", name="crearDistrito")
     */
    public function crearDistritoAction(Request $request) {

        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        // Comprobamos que el usuario es admin, si no, redireccionamos a /
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/censo/crearDistrito', true);
        if (!$status) {
            return new RedirectResponse('/');
        }
        if ($request->getMethod() == 'POST') {
            $NOMBRE_DISTRITO = strtoupper($request->request->get('NOMBRE'));
            if (trim($NOMBRE_DISTRITO) != '') {
                // Buscamos el distrito por si existiera ya
                $DISTRITO = $doctrine->getRepository('AppBundle:UsuarioDistrito')->findOneByNombre($NOMBRE_DISTRITO);
                if ($DISTRITO === null) {
                    $DISTRITO = new \AppBundle\Entity\UsuarioDistrito();
                    $DISTRITO->setNombre($NOMBRE_DISTRITO);
                    $doctrine->getManager()->persist($DISTRITO);
                    $doctrine->getManager()->flush();
                }
            }
        }
        return new RedirectResponse('/guardian/censo/distritos');
    }

    /**
     * 
     * @Route("/guardian/ejercicios_entregas", name="ejerciciosYentregas")
     */
    public function ejercicios_entregas(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        // Comprobamos que el usuario es admin, si no, redireccionamos a /
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/ejercicios_entregas', true);
        if (!$status) {
            return new RedirectResponse('/');
        }

        // Si se ha enviado un formulario
        if ($request->getMethod() == 'POST') {
            $em = $doctrine->getManager();
            // Obtenemos la sección donde se va a publicar el ejercicio
            $SECCION = $request->request->get('SECCION');
            // Obtenemos el objeto asociado a esa sección
            $EJERCICIO_SECCION = $doctrine->getRepository('AppBundle:EjercicioSeccion')->findOneBySeccion($SECCION);
            // Obtenemos el tipo de ejercicio
            $TIPO = $request->request->get('TIPO');
            // Obtenemos el objeto asociado a ese tipo
            $EJERCICIO_TIPO = $doctrine->getRepository('AppBundle:EjercicioTipo')->findOneByTipo($TIPO);
            // Es un grupo de ejercicios o por separado?
            $GRUPO_O_NO = $request->request->get('GRUPO_O_NO');

            $rol_jugador = $doctrine->getRepository('AppBundle:Rol')->findOneByIdRol(1);
            $usuarios = $doctrine->getRepository('AppBundle:Usuario')->findByIdRol($rol_jugador);

            if ($GRUPO_O_NO === 'grupo') {
                $GRUPO_EJERCICIOS = new \AppBundle\Entity\GrupoEjercicios();
                $em->persist($GRUPO_EJERCICIOS);
                $em->flush();
            }

            if ($TIPO === 'test' || $TIPO === 'grupo_test') {
                // Obtenemos todos los enunciados del formulario
                $ENUNCIADOS = $request->request->get('ENUNCIADO');

                // Iniciamos el contador para saber en qué número de enunciado estamos
                // nos servirá para obtener el checkbox
                $numero_enunciado = 1;
                // Para cada enunciado
                foreach ($ENUNCIADOS as $enunciado) {
                    // Buscamos si el enunciado ya existía para ese tipo y esa sección
                    $EJERCICIO = $doctrine->getRepository('AppBundle:Ejercicio')->findOneBy([
                        'idTipoEjercicio' => $EJERCICIO_TIPO,
                        'idEjercicioSeccion' => $EJERCICIO_SECCION,
                        'enunciado' => $enunciado
                    ]);
                    // Si el ejercicio no existe se crea uno nuevo
                    if (!count($EJERCICIO)) {
                        $EJERCICIO = new \AppBundle\Entity\Ejercicio();
                        $EJERCICIO->setIdTipoEjercicio($EJERCICIO_TIPO);
                        $EJERCICIO->setIdEjercicioSeccion($EJERCICIO_SECCION);
                        $EJERCICIO->setEnunciado($enunciado);
                        $EJERCICIO->setFecha(new \DateTime('now'));
                        $em->persist($EJERCICIO);
                    }
                    // Si existe s eliminan las respuestas/entregas asociadas a ese ejercicio
                    else {
                        $EJERCICIOS_RESPUESTAS = $doctrine->getRepository('AppBundle:EjercicioRespuesta')->findByIdEjercicio($EJERCICIO);
                        $EJERCICIO_ENTREGA = $doctrine->getRepository('AppBundle:EjercicioEntrega')->findOneByIdEjercicio($EJERCICIO);
                        if (count($EJERCICIOS_RESPUESTAS)) {
                            foreach ($EJERCICIOS_RESPUESTAS as $resp) {
                                $em->remove($resp);
                            }
                        }
                        if (count($EJERCICIO_ENTREGA)) {
                            $em->remove($EJERCICIO_ENTREGA);
                        }
                    }
                    // Obtenemos las respuestas del enunciado
                    $RESPUESTAS = $request->request->get('RESPUESTA_' . $numero_enunciado);
                    // Establecemos un contador para recorrer cada respuesta
                    $numero_respuesta = 1;
                    foreach ($RESPUESTAS as $respuesta) {
                        // Comprobamos si el checkbox de la respuesta está activado o no
                        $checkbox = $request->request->get('RESPUESTAS_CHECK_' . $numero_enunciado . '' . $numero_respuesta);
                        if ($checkbox === 'on') {
                            $CORRECTA = 1;
                        } else {
                            $CORRECTA = 0;
                        }
                        // Almacenamos cada respuesta en la BD asociadas al ejercicio
                        $EJERCICIO_RESPUESTA = new \AppBundle\Entity\EjercicioRespuesta();
                        $EJERCICIO_RESPUESTA->setIdEjercicio($EJERCICIO);
                        $EJERCICIO_RESPUESTA->setRespuesta($respuesta);
                        $EJERCICIO_RESPUESTA->setCorrecta($CORRECTA);
                        $em->persist($EJERCICIO_RESPUESTA);

                        $numero_respuesta++;
                    }
                    $numero_enunciado++;

                    if ($GRUPO_O_NO === 'grupo') {
                        $EJERCICIO_X_GRUPO = new \AppBundle\Entity\EjercicioXGrupo();
                        $EJERCICIO_X_GRUPO->setIdEjercicio($EJERCICIO);
                        $EJERCICIO_X_GRUPO->setIdGrupoEjercicios($GRUPO_EJERCICIOS);
                        $em->persist($EJERCICIO_X_GRUPO);
                        $em->flush();
                    }
                    if ($TIPO === 'test') {
                        foreach ($usuarios as $usuario) {
                            $EJERCICIO_X_USUARIO = new \AppBundle\Entity\EjercicioXUsuario();
                            $EJERCICIO_X_USUARIO->setIdEjercicio($EJERCICIO);
                            $EJERCICIO_X_USUARIO->setIdSeccion($EJERCICIO_SECCION);
                            $EJERCICIO_X_USUARIO->setIdUsu($usuario);
                            $em->persist($EJERCICIO_X_USUARIO);
                        }
                    }
                    $em->flush();
                }
                if ($GRUPO_O_NO === 'grupo') {
                    foreach ($usuarios as $usuario) {
                        $EJERCICIO_X_USUARIO = new \AppBundle\Entity\EjercicioXUsuario();
                        $EJERCICIO_X_USUARIO->setIdGrupo($GRUPO_EJERCICIOS);
                        $EJERCICIO_X_USUARIO->setIdSeccion($EJERCICIO_SECCION);
                        $EJERCICIO_X_USUARIO->setIdUsu($usuario);
                        $em->persist($EJERCICIO_X_USUARIO);
                    }
                    $em->flush();
                }
            }
            if ($TIPO === 'entrega' || $TIPO === 'grupo_entrega') {
                // Obtenemos todos los enunciados del formulario
                $ENUNCIADOS = $request->request->get('ENUNCIADO');

                // Iniciamos el contador para saber en qué número de enunciado estamos
                // nos servirá para obtener el checkbox
                $numero_enunciado = 1;
                // Para cada enunciado
                foreach ($ENUNCIADOS as $enunciado) {
                    // Buscamos si el enunciado ya existía para ese tipo y esa sección
                    $EJERCICIO = $doctrine->getRepository('AppBundle:Ejercicio')->findOneBy([
                        'idTipoEjercicio' => $EJERCICIO_TIPO,
                        'idEjercicioSeccion' => $EJERCICIO_SECCION,
                        'enunciado' => $enunciado
                    ]);
                    // Si el ejercicio no existe se crea uno nuevo
                    if (!count($EJERCICIO)) {
                        $EJERCICIO = new \AppBundle\Entity\Ejercicio();
                        $EJERCICIO->setIdTipoEjercicio($EJERCICIO_TIPO);
                        $EJERCICIO->setIdEjercicioSeccion($EJERCICIO_SECCION);
                        $EJERCICIO->setEnunciado($enunciado);
                        $EJERCICIO->setFecha(new \DateTime('now'));
                        $em->persist($EJERCICIO);
                        $em->flush();
                    }
                    // Si existe se eliminan las respuestas asociadas a ese ejercicio
                    else {
                        $EJERCICIOS_RESPUESTAS = $doctrine->getRepository('AppBundle:EjercicioRespuesta')->findByIdEjercicio($EJERCICIO);
                        $EJERCICIO_ENTREGA = $doctrine->getRepository('AppBundle:EjercicioEntrega')->findOneByIdEjercicio($EJERCICIO);
                        if (count($EJERCICIOS_RESPUESTAS)) {
                            foreach ($EJERCICIOS_RESPUESTAS as $resp) {
                                $em->remove($resp);
                            }
                        }
                        if (count($EJERCICIO_ENTREGA)) {
                            $em->remove($EJERCICIO_ENTREGA);
                        }
                        $em->flush();
                    }

                    if ($GRUPO_O_NO === 'grupo') {
                        $EJERCICIO_X_GRUPO = new \AppBundle\Entity\EjercicioXGrupo();
                        $EJERCICIO_X_GRUPO->setIdEjercicio($EJERCICIO);
                        $EJERCICIO_X_GRUPO->setIdGrupoEjercicios($GRUPO_EJERCICIOS);
                        $em->persist($EJERCICIO_X_GRUPO);
                        $em->flush();
                    }

                    foreach ($usuarios as $usuario) {
                        $EJERCICIO_X_USUARIO = new \AppBundle\Entity\EjercicioXUsuario();
                        $EJERCICIO_X_USUARIO->setIdEjercicio($EJERCICIO);
                        $EJERCICIO_X_USUARIO->setIdSeccion($EJERCICIO_SECCION);
                        $EJERCICIO_X_USUARIO->setIdUsu($usuario);
                        $em->persist($EJERCICIO_X_USUARIO);
                    }
                    $em->flush();

                    $numero_enunciado++;
                }
                if ($TIPO === 'grupo_test' || $TIPO === 'grupo_entrega') {
                    foreach ($usuarios as $usuario) {
                        $EJERCICIO_X_USUARIO = new \AppBundle\Entity\EjercicioXUsuario();
                        $EJERCICIO_X_USUARIO->setIdGrupo($GRUPO_EJERCICIOS);
                        $EJERCICIO_X_USUARIO->setIdSeccion($EJERCICIO_SECCION);
                        $EJERCICIO_X_USUARIO->setIdUsu($usuario);
                        $em->persist($EJERCICIO_X_USUARIO);
                    }
                }
                $em->flush();
            }
        }
        $DATOS['TITULO'] = 'Ejercicios y entregas';
        return $this->render('guardian/ejercicios/ejercicios_entregas.twig', $DATOS);
    }

    /**
     * 
     * @Route("/guardian/apuestas/proponer", name="proponerApuesta")
     */
    public function proponerApuestaAction(Request $request) {

        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $em = $doctrine->getManager();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/apuestas/proponer', true);
        if (!$status) {
            return new RedirectResponse('/');
        }
        $DATOS = ['TITULO' => 'Apuestas - Proponer', 'SECCION' => 'APUESTAS', 'SUBSECCION' => 'PROPONER'];
        if ($request->getMethod() == 'POST') {
            $DESCRIPCION = $request->request->get('APUESTA');
            $GET_POSIBILIDADES = $request->request->get('POSIBILIDADES');
            $POSIBILIDADES = [];
            foreach ($GET_POSIBILIDADES as $POSIBILIDAD) {
                if (trim($POSIBILIDAD) !== '') {
                    $POSIBILIDADES[] = $POSIBILIDAD;
                }
            }
            if (count($POSIBILIDADES) > 1) {
                $APUESTA = new \AppBundle\Entity\Apuesta();
                $APUESTA->setDescripcion($DESCRIPCION);
                $em->persist($APUESTA);

                foreach ($POSIBILIDADES as $POSIBILIDAD) {
                    $APUESTA_POSIBILIDAD = new \AppBundle\Entity\ApuestaPosibilidad();
                    $APUESTA_POSIBILIDAD->setPosibilidad($POSIBILIDAD);
                    $APUESTA_POSIBILIDAD->setIdApuesta($APUESTA);
                    $em->persist($APUESTA_POSIBILIDAD);
                }
                $em->flush();
            }
        }
        return $this->render('guardian/loteriasApuestas.twig', $DATOS);
    }

    /**
     * 
     * @Route("/guardian/apuestas/gestion", name="gestionarApuestas")
     */
    public function gestionarApuestasAction(Request $request) {

        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/apuestas/gestion', true);
        if (!$status) {
            return new RedirectResponse('/');
        }
        $DATOS = ['TITULO' => 'Apuestas - Gestión', 'SECCION' => 'APUESTAS', 'SUBSECCION' => 'GESTIONAR'];

        return $this->render('guardian/loteriasApuestas.twig', $DATOS);
    }

    /**
     * 
     * @Route("/guardian/apuestas/actualizarApuestas", name="actualizarApuestas")
     */
    public function actualizarApuestas() {
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $qb = $em->createQueryBuilder();
        $resultado['resultado'] = 'OK';
        $APUESTAS_ACTUALES = [];

        $query = $qb->select('a')
                ->from('\AppBundle\Entity\Apuesta', 'a');
        ;
        //->orderBy('a.fecha', 'DESC');
        $APUESTAS = $query->getQuery()->getResult();
        if (!count($APUESTAS)) {
            return new JsonResponse(array('resultado' => 'No hay apuestas'), 200);
        }
        foreach ($APUESTAS as $APUESTA) {
//            $aniadir = true;
            $aux = [];
            $aux['DESCRIPCION'] = $APUESTA->getDescripcion();
            $aux['ID'] = $APUESTA->getIdApuesta();
            $aux['ESTADO'] = $APUESTA->getDisponible();
            $aux['TIEMPO_TOTAL'] = 0;
            $aux['N_APUESTAS'] = 0;
            $APUESTA_POSIBILIDAD = $doctrine->getRepository('AppBundle:ApuestaPosibilidad')->findByidApuesta($APUESTA);
            foreach ($APUESTA_POSIBILIDAD as $POSIBILIDAD) {
//                if ($POSIBILIDAD->getResultado() === null) {
                $aux2 = [];
                $aux2['ENUNCIADO'] = $POSIBILIDAD->getPosibilidad();
                $aux2['ID'] = $POSIBILIDAD->getIdApuestaPosibilidad();
                $aux2['TdV'] = 0;
                $aux2['N_APUESTAS'] = 0;
                $USUARIOS_APUESTA = $doctrine->getRepository('AppBundle:UsuarioApuesta')->findByIdApuestaPosibilidad($POSIBILIDAD);
                if (count($USUARIOS_APUESTA)) {
                    foreach ($USUARIOS_APUESTA as $USUARIO_APUESTA) {
                        $aux['TIEMPO_TOTAL'] += $USUARIO_APUESTA->getTdvApostado();
                        $aux2['TdV'] += $USUARIO_APUESTA->getTdvApostado();
                        $aux['N_APUESTAS'] += 1;
                        $aux2['N_APUESTAS'] += 1;
                    }
                }
                $aux['POSIBILIDAD'][] = $aux2;
//                } else {
//                    $aniadir = false;
//                }
            }
//            if ($aniadir) {
            $APUESTAS_ACTUALES[] = $aux;
//            }
        }
        $resultado['apuestas'] = $APUESTAS_ACTUALES;
        return new JsonResponse($resultado, 200);
    }

    /**
     * @Route("/guardian/apuestas/terminarApuesta/{id_apuesta_posibilidad}", name="terminarApuesta")
     */
    public function terminarApuestaAction(Request $request, $id_apuesta_posibilidad) {

        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $em = $doctrine->getManager();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/apuestas/terminarApuesta/' . $id_apuesta_posibilidad, true);

        if (!$status) {
            return new RedirectResponse('/');
        }

        $POSIBILIDAD = $doctrine->getRepository('AppBundle:ApuestaPosibilidad')->findOneByIdApuestaPosibilidad($id_apuesta_posibilidad);
        $POSIBILIDAD->setResultado(1);
        $em->persist($POSIBILIDAD);
        $em->flush();
        $APUESTA = $POSIBILIDAD->getIdApuesta();
        $POSIBILIDADES = $doctrine->getRepository('AppBundle:ApuestaPosibilidad')->findByIdApuesta($APUESTA);
        foreach ($POSIBILIDADES as $P) {
            $P->setResultado(0);
            $em->persist($P);
        }
        $em->flush();

        // Obtenemos todas las apuestas de usuarios de esta apuesta
        $APUESTAS_USUARIO_ALL = $doctrine->getRepository('AppBundle:UsuarioApuesta')->findAll();
        $APUESTAS_USUARIO_ESTA_APUESTA = [];
        $RECAUDACION = 0;
        // Si hay alguna apuesta
        if (count($APUESTAS_USUARIO_ALL)) {
            foreach ($APUESTAS_USUARIO_ALL AS $UA) {
                if (in_array($UA->getIdApuestaPosibilidad(), $POSIBILIDADES)) {
                    $APUESTAS_USUARIO_ESTA_APUESTA[] = $UA;
                    $RECAUDACION += $UA->getTdvApostado();
                    Usuario::operacionSobreTdV($doctrine, $UA->getIdUsuario(), $UA->getTdvApostado() * (-1), 'Cobro - Apuestas');
                }
            }

            // Obtenemos las apuestas ganadoras, calculamos las ganancias y las repartimos entre los ganadores
            $APUESTAS_GANADORAS = $doctrine->getRepository('AppBundle:UsuarioApuesta')->findByIdApuestaPosibilidad($POSIBILIDAD);
            if (count($APUESTAS_GANADORAS)) {
                $DISPARADOR_APUESTAS = $doctrine->getRepository('AppBundle:Constante')->findOneByClave('disparador_apuesta')->getValor();
                $GANANCIAS = round(($RECAUDACION * $DISPARADOR_APUESTAS) / count($APUESTAS_GANADORAS));
                foreach ($APUESTAS_GANADORAS as $A) {
                    Usuario::operacionSobreTdV($doctrine, $A->getIdUsuario(), $GANANCIAS, 'Ingreso - Apuesta ganadora');
                }
            }
        }

        return new JsonResponse(array('respuesta' => 'OK'), 200);
    }

    /**
     * @Route("/guardian/apuestas/pararApuesta/{id_apuesta}/{desactivar}", name="pararApuesta")
     */
    public function pararApuestaAction(Request $request, $id_apuesta, $desactivar) {

        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $em = $doctrine->getManager();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/apuestas/pararApuesta/' . $id_apuesta . '/' . $desactivar, true);

        if (!$status) {
            return new RedirectResponse('/');
        }

        $APUESTA = $doctrine->getRepository('AppBundle:Apuesta')->findOneByIdApuesta($id_apuesta);
        if ($APUESTA === null) {
            return new JsonResponse(array('respuesta' => 'No existe la apuesta' . $id_apuesta), 300);
        }
        if (intval($desactivar) !== 0 && intval($desactivar) !== 1) {
            return new JsonResponse(array('respuesta' => 'No existe el estado ' . $desactivar . ' para una apuesta'), 300);
        }
        $APUESTA->setDisponible($desactivar);
        $em->persist($APUESTA);
        $em->flush();

        return new JsonResponse(array('respuesta' => 'OK'), 200);
    }

    /**
     * 
     * @Route("/guardian/mensajeria", name="mensajeria")
     */
    public function mensajeria(Request $request) {

        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/mensajeria', true);

        if (!$status) {
            return new RedirectResponse('/');
        }

        if ($request->getMethod() == 'POST') {
            $OPCION_MENSAJE = strtoupper($request->request->get('OPCION_MENSAJE'));
            $TITULO = $request->request->get('TITULO');
            $MENSAJE_TEXTO = $request->request->get('MENSAJE');
            $em = $this->getDoctrine()->getManager();

            switch ($OPCION_MENSAJE) {
                case 'DIFUSION':
                    $TIPO_MENSAJE = 1;
                    break;
                case 'DISTRITO':
                    $TIPO_MENSAJE = 2;
                    break;
                case 'DIRECTO':
                    $TIPO_MENSAJE = 3;
                    break;
            }
            $id_tipo_mensaje = $doctrine->getRepository('AppBundle:TipoMensaje')->findOneByIdTipoMensaje($TIPO_MENSAJE);
            $fecha = new \DateTime('now');
            $MENSAJE = new \AppBundle\Entity\Mensaje();
            $MENSAJE->setIdTipoMensaje($id_tipo_mensaje);
            $MENSAJE->setTitulo($TITULO);
            $MENSAJE->setMensaje($MENSAJE_TEXTO);
            $MENSAJE->setFecha($fecha);
            $em->persist($MENSAJE);
            $em->flush();

            $rol_jugador = $doctrine->getRepository('AppBundle:Rol')->findOneByIdRol(1);
            $usuarios = $doctrine->getRepository('AppBundle:Usuario')->findByIdRol($rol_jugador);

            // Enviar mensaje por difusión
            if ($TIPO_MENSAJE === 1) {
                foreach ($usuarios as $usuario) {
                    $USUARIO_X_MENSAJE = new \AppBundle\Entity\MensajeXUsuario();
                    $USUARIO_X_MENSAJE->setIdMensaje($MENSAJE);
                    $USUARIO_X_MENSAJE->setIdUsuario($usuario);
                    $em->persist($USUARIO_X_MENSAJE);
                    $em->flush();
                }
            }
        }

        $DATOS['TITULO'] = 'Mensajería';
        return $this->render('guardian/mensajeria.twig', $DATOS);
    }

    public function inicio_usuario($usuario, $session) {
        $rol_usu = $usuario->getIdRol()->getIdRol();

        if ($rol_usu === 1) {

            $render = $this->inicio_ciudadano($usuario, $session);
        }
        if ($rol_usu === 2) {
            $render = $this->inicio_guardian();
        }
        return $render;
    }

    public function inicio_guardian() {
        $DATOS = ['TITULO' => 'InTime - Guardián del Tiempo'];
        return $this->render('guardian/guardian.html.twig', $DATOS);
    }

    public function inicio_ciudadano($usuario, $session) {

        $doctrine = $this->getDoctrine();
        $DATOS = [];

        if ($usuario->getNombre() !== null) {
            $DATOS = DataManager::setDefaultData($doctrine, 'InTime - ' . $usuario->getNombre(), $session);
        } else {
            $session->set('registro_completo', false);
            $DATOS = DataManager::setDefaultData($doctrine, 'InTime - Desconocido', $session);
        }
        return $this->render('ciudadano/ciudadano.html.twig', $DATOS);
    }
}
