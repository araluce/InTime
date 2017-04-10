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
use AppBundle\Runtastic\Runtastic;

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
            $patronAlias = "/[@]{1}/";
            if (preg_match($patronAlias, $DNI, $coincidencia, PREG_OFFSET_CAPTURE)) {
                $ALIAS = strtolower(str_replace('@', '', $DNI));
                $usuario = $doctrine->getRepository('AppBundle:Usuario')->findOneBySeudonimo($ALIAS);
            } else {
                $usuario = $doctrine->getRepository('AppBundle:Usuario')->findOneByDni($DNI);
            }

            if ($usuario !== null && $usuario->getClave() === Usuario::encriptar($usuario, $KEY)) {
                //Crear sesión para el usuario logueado
                $session = $request->getSession();
                $session->set('id_usuario', $usuario->getIdUsuario());

                Usuario::activar_usuario($doctrine, $usuario);
                Usuario::compruebaUsuario($doctrine, $session, '/login');
                Ejercicio::actualizarEjercicioXUsuario($doctrine, $usuario);

                return new RedirectResponse('/');
                //Cargar el menu del usuario según rol
                //return $this->inicio_usuario($usuario, $session);
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
                    Twitter::almacenar_tweet($id_tuitero, $id_tweet, 4, $id_usuario, $id_usuario_destino, $fecha, $doctrine);
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
        if (!DataManager::infoUsu($doctrine, $session)) {
            return new RedirectResponse('/ciudadano/info');
        }
        $DATOS = DataManager::setDefaultData($doctrine, 'Trabajo', $session);
        return $this->render('ciudadano/extensiones/trabajo.html.twig', $DATOS);
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
        if (!DataManager::infoUsu($doctrine, $session)) {
            return new RedirectResponse('/ciudadano/info');
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
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/alimentacion/getEjerciciosAlimentacion', true);
        if ($status) {
            return new RedirectResponse('/');
        }
        $status = Usuario::compruebaUsuario($doctrine, $session, '/coidadano/ocio/amigos');
        if (!$status) {
            return new RedirectResponse('/');
        }
        $DATOS = DataManager::setDefaultData($doctrine, 'Amigos', $session);
        return $this->render('ciudadano/ocio/amigos.html.twig', $DATOS);
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

            $resultado = Usuario::registrarMultiple($doctrine, $DNIs, $TDV);

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
        $JUGADORES = $doctrine->getRepository('AppBundle:Usuario')->findByIdRol($ROL);
        $DATOS['JUGADORES'] = [];
        if (count($JUGADORES)) {
            foreach ($JUGADORES as $JUGADOR) {
                $aux = [];
                $aux['NOMBRE'] = $JUGADOR->getNombre();
                $aux['APELLIDOS'] = $JUGADOR->getApellidos();
                $aux['DNI'] = $JUGADOR->getDni();
                $aux['ALIAS'] = $JUGADOR->getSeudonimo();
                $aux['EMAIL'] = $JUGADOR->getEmail();
                $aux['ESTADO'] = $JUGADOR->getIdEstado()->getDescripcion();
                $aux['DISTRITO'] = null;
                if (null !== $JUGADOR->getIdDistrito()) {
                    $aux['DISTRITO'] = $JUGADOR->getIdDistrito()->getNombre();
                }
                $AHORA = new \DateTime('now');
                $TDV = $JUGADOR->getIdCuenta()->getTdv();
                $RESTANTE = $TDV->getTimestamp() - $AHORA->getTimestamp();
                $aux['TDV'] = Utils::segundosToDias(0);
                $aux['TDV_RED'] = 1;
                if ($RESTANTE > 0) {
                    $aux['TDV_RED'] = 0;
                    $aux['TDV'] = Utils::segundosToDias($RESTANTE);
                }
                $aux['NIVEL'] = 0;
                $aux['XP'] = 0;
                $USUARIO_NIVEL = $doctrine->getRepository('AppBundle:UsuarioNivel')->findOneByIdUsuario($JUGADOR);
                if (null !== $USUARIO_NIVEL) {
                    $aux['NIVEL'] = $USUARIO_NIVEL->getNivel();
                    $aux['XP'] = $USUARIO_NIVEL->getPuntos();
                }
                $infoPuesto = Usuario::getClasificacion($doctrine, $JUGADOR, $JUGADORES);
                $aux['PUESTO'] = $infoPuesto['PUESTO'];
                $infoPuestoMes = Usuario::getClasificacionMes($doctrine, $JUGADOR, $JUGADORES);
                $aux['PUESTO_MES'] = $infoPuestoMes['PUESTO'];
                $DATOS['JUGADORES'][] = $aux;
            }
        }
        $DATOS['TITULO'] = 'Gestión de distritos';
        $DATOS['SECCION'] = 'CIUDADANOS';
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

    /**
     * 
     * @Route("/comprobarInformacionPersonal")
     */
    public function comprobarInformacionAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $id_usuario = $session->get('id_usuario');
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($id_usuario);
        return Usuario::comprobarInformacionPersonalJSON($USUARIO);
    }

    public function inicio_usuario($usuario, $session) {
        $rol_usu = $usuario->getIdRol()->getIdRol();

        if ($rol_usu === 1 || $rol_usu === 5) {

            $render = $this->inicio_ciudadano($usuario, $session);
        }
        if ($rol_usu === 2) {
            $render = $this->inicio_guardian($usuario);
        }
        return $render;
    }

    public function inicio_guardian($USUARIO) {
        $doctrine = $this->getDoctrine();
        $DATOS = ['TITULO' => '$inTIME - Guardián del Tiempo'];
        $DATOS['CHAT'] = DataManager::chatsPendientes($this->getDoctrine(), $USUARIO);
        $DATOS['CITAS'] = DataManager::getCitasPendientesGuardian($doctrine);
        $DATOS['CITAS_HOY'] = DataManager::getCitasDeHoyGuardian($doctrine);
        $DATOS['ALIMENTACION'] = DataManager::numEntregasAlimentacionGuardian($doctrine);
        $DATOS['PAGA'] = DataManager::numEntregasPagaGuardian($doctrine);
        $DATOS['FELICIDAD'] = DataManager::numEntregasFelicidadGuardian($doctrine);
        return $this->render('guardian/guardian.html.twig', $DATOS);
    }

    public function inicio_ciudadano($usuario, $session) {

        $doctrine = $this->getDoctrine();
        $DATOS = [];

        if ($usuario->getNombre() !== null) {
            $DATOS = DataManager::setDefaultData($doctrine, '$inTIME - ' . $usuario->getNombre(), $session);
        } else {
            $session->set('registro_completo', false);
            $DATOS = DataManager::setDefaultData($doctrine, '$inTIME - Desconocido', $session);
        }
        $response = $this->render('ciudadano/ciudadano.html.twig', $DATOS);
        return $response;
    }

    /**
     * @Route("/testEncriptar/{id_usuario}/{dni}", name="testEncriptar")
     */
    public function testEncriptarAction(Request $request, $id_usuario, $dni) {
        $clave = sha1($id_usuario . $dni);
        Utils::pretty_print($clave);
    }

    /**
     * @Route("/testAcentos/{string}", name="testAcentos")
     */
    public function testAcentosAction(Request $request, $string) {
        $fecha = new \DateTime('now');
        //Utils::pretty_print(Utils::replaceAccented($string));
        $hoy = new \DateTime('now');
        $dia_str = strtolower('Viernes');

        $conversores = array('lunes' => 1, 'martes' => 2, 'miercoles' => 3, 'jueves' => 4, 'viernes' => 5);
        $dia_int = strtr($dia_str, $conversores);
        Utils::pretty_print("dia_int(" . $dia_int . ") < hoy(" . $hoy->format('w') . ")");
        if (intval($dia_int) < intval($hoy->format('w'))) {
            return 1;
        }
        return 0;
    }

    /**
     * @Route("/actualizarRuntastic/{alias}", name="actualizarRuntastic")
     */
    public function actualizarRuntasticAction($alias) {
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $qb = $em->createQueryBuilder();
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneBySeudonimo($alias);
        $UR = $doctrine->getRepository('AppBundle:UsuarioRuntastic')->findByIdUsuario($USUARIO);
        if (!count($UR)) {
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Fallo al actualizar la información'), 200);
        }
        $actividades_semana = [];
        foreach ($UR as $U) {
            $SESIONES = $doctrine->getRepository('AppBundle:SesionRuntastic')->findByIdUsuarioRuntastic($U);
            $array_sesiones = [];
            if (count($SESIONES)) {
                foreach ($SESIONES as $S) {
                    $array_sesiones[] = $S->getIdRuntastic();
                }
            }
            $r = new Runtastic();
            $timeout = false;
            $tiempo_inicio = microtime(true);
            $hoy = new \DateTime('now');
            do {
                $r->setUsername($U->getUsername())->setPassword($U->getPassword());
                echo $r->getResponseStatusCode();
                $week_activities = $r->getActivities($hoy->format('W') - 1);
                $tiempo_fin = microtime(true);
                $tiempo = $tiempo_fin - $tiempo_inicio;
                if ($tiempo >= 10.0) {
                    $timeout = true;
                }
            } while ($r->getResponseStatusCode() !== 200 && !$timeout);
            $response['usuario'] = $r->getUsername();
            $response['Uid'] = $r->getUid();
            foreach ($week_activities as $activity) {
                $actividades_semana[] = $activity;
                if (!in_array($activity->id, $array_sesiones)) {
                    $SESION = new \AppBundle\Entity\SesionRuntastic();
                    $SESION->setIdRuntastic($activity->id);
                    $SESION->setIdUsuarioRuntastic($U);
                    $SESION->setTipo('cycling');
                    if ($activity->type === 'running') {
                        $SESION->setTipo('running');
                    }
                    $SESION->setDuracion(Utils::milisegundosToSegundos($activity->duration));
                    $SESION->setDistancia($activity->distance);
                    $SESION->setRitmo($activity->pace);
                    $SESION->setVelocidad($activity->speed);
                    $SESION->setEvaluado(0);
                    $FECHA = new \Datetime();
                    $FECHA->setDate($activity->date->year, $activity->date->month, $activity->date->day);
                    $FECHA->setTime($activity->date->hour, $activity->date->minutes, $activity->date->seconds);
                    $SESION->setFecha($FECHA);
                    $em->persist($SESION);
                }
            }
            $em->flush();
        }
        Utils::pretty_print($actividades_semana);
        if (!count($actividades_semana)) {
            return new JsonResponse(['estatus' => 'ERROR', 'message' => 'No se han actualizado sus sesiones. '
                . 'Puede que hayas excedido el máximo de sesiones abiertas permitidas por Runtastic. Podría ayudar cerrar sesiones '
                . 'de Runtastic que tengas abiertas en los navegadores. Si esto no funciona prueba a cerrar y abrir la sesión de '
                . 'Runtastic en $inTime.'], 200);
        }

        return new JsonResponse(['estatus' => 'OK', 'message' => 'Datos descargados correctamente'], 200);
    }

    /**
     * @Route("/comprobarRetosDeportivosAlias/{alias}", name="comprobarRetosAlias")
     */
    public function comprobarRetosAliasAction(Request $request, $alias) {
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $qb = $em->createQueryBuilder();
        $CIUDADANO = $doctrine->getRepository('AppBundle:Usuario')->findOneBySeudonimo($alias);
        $ok = false;
        $query = $qb->select('ur')
                ->from('\AppBundle\Entity\UsuarioRuntastic', 'ur')
                ->where('ur.idUsuario = :IdUsuario')
                ->setParameters(['IdUsuario' => $CIUDADANO]);
        $CUENTAS_RUNTASTIC = $query->getQuery()->getResult();
        if (!count($CUENTAS_RUNTASTIC)) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Error inesperado')), 200);
        }
        $DEPORTE = $doctrine->getRepository('AppBundle:EjercicioSeccion')->findOneBySeccion('deporte');
        $EJERCICIO = $doctrine->getRepository('AppBundle:Ejercicio')->findOneByIdEjercicioSeccion($DEPORTE);
        if (null !== $EJERCICIO) {
            $comparar = 1;
            $RETOS = $doctrine->getRepository('AppBundle:EjercicioRuntastic')->findByIdEjercicio($EJERCICIO);
            if (!count($RETOS)) {
                $comparar = 0;
            }
            $retoRunning = null;
            $retoCycling = null;
            foreach ($RETOS as $RETO) {
                if ($RETO->getTipo() === 'running') {
                    $retoRunning = $RETO;
                }
                if ($RETO->getTipo() === 'cycling') {
                    $retoCycling = $RETO;
                }
            }
            if (null === $retoCycling || null === $retoRunning) {
                $comparar = 0;
            }
            $duracion_acumulada = 0;
            $id_sesiones = [];
            $n_sesiones = 1;
            $contador = 0;
            $estaSemana = 0;
            foreach ($CUENTAS_RUNTASTIC as $CUENTA) {
                $SESIONES_RUNTASTIC = $doctrine->getRepository('AppBundle:SesionRuntastic')->findByIdUsuarioRuntastic($CUENTA);
                //$cont = 0;
                foreach ($SESIONES_RUNTASTIC as $SESION) {
                    if (!$ok) {
                        $retoDepSemanaPasada = Utils::semanaPasada($SESION->getFecha());
//                        if ($retoDepSemanaPasada) {
//                            Utils::pretty_print("Se evalúa la sesión del " . $SESION->getFecha()->format('d-m-Y'));
//                            $estaSemana++;
//                        } else {
//                            Utils::pretty_print("No se evalúa la sesión del " . $SESION->getFecha()->format('d-m-Y'));
//                        }
                        if ($retoDepSemanaPasada) {
                            $duracion = $SESION->getDuracion();
                            if ($comparar) {
//                                Utils::pretty_print("Se evalúa una sesión");
//                                if ($SESION->getEvaluado()) {
//                                    Utils::pretty_print("Sesión evaluada");
//                                }
                                if (!$SESION->getEvaluado()) {
//                                    Utils::pretty_print("Sesión no evaluada");
                                    if ($SESION->getTipo() === 'running') {
                                        if (($retoRunning->getRitmo() >= $SESION->getRitmo())) {
                                            $duracion_acumulada += $duracion;
                                            $id_sesiones[] = $SESION;
                                            if ($duracion_acumulada >= $retoRunning->getDuracion()) {
                                                $n_sesiones++;
                                                $contador++;
//                                                    Utils::pretty_print('Duracion ac: ' . $duracion_acumulada);
//                                                    Utils::pretty_print('Duracion reto: ' . $retoRunning->getDuracion());
//                                                    Utils::pretty_print('Numero sesiones: ' . $n_sesiones);
                                                if ($n_sesiones >= 2) {
                                                    $ok = true;
                                                    Ejercicio::evaluaFasePartes($doctrine, $EJERCICIO, $CIUDADANO, $id_sesiones);
                                                }
                                            }
                                        }
                                    }
                                    if ($SESION->getTipo() === 'cycling') {
//                                        Utils::pretty_print("Esta sesión marca una velocidad mínima de " . $retoCycling->getVelocidad() . "Km/h");
//                                        Utils::pretty_print("Tu sesión es de " . $SESION->getVelocidad() . "Km/h");
                                        if ($retoCycling->getVelocidad() <= $SESION->getVelocidad()) {
                                            $duracion_acumulada += $duracion;
                                            $id_sesiones[] = $SESION;
                                            if ($duracion_acumulada >= $retoCycling->getDuracion()) {
                                                $n_sesiones++;
                                                $contador++;
//                                                    Utils::pretty_print('Duracion ac: ' . $duracion_acumulada);
//                                                    Utils::pretty_print('Duracion reto: ' . $retoCycling->getDuracion());
//                                                    Utils::pretty_print('Numero sesiones: ' . $n_sesiones);
                                                if ($n_sesiones >= 2) {
                                                    $ok = true;
                                                    Ejercicio::evaluaFasePartes($doctrine, $EJERCICIO, $CIUDADANO, $id_sesiones);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
//            Utils::pretty_print("Esta semana se han contado " . $estaSemana . " sesiones");
        }
//        $em->flush();
        if ($ok) {
            return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => 'Reto deportivo superado')), 200);
        }
        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => 'Reto deportivo no superado')), 200);
    }

    /**
     * @Route("/testComprobarNivelAlias/{alias}", name="testComprobarNivelAlias")
     */
    public function testComprobarNivelAliassAction($alias) {
        $doctrine = $this->getDoctrine();

        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneBySeudonimo($alias);
        $ok = Usuario::comprobarNivel($doctrine, $USUARIO);
        Utils::pretty_print($ok);
        if ($ok) {
            Utils::pretty_print('Sube de nivel');
        } else {
            Utils::pretty_print('No superado');
        }
//        return new JsonResponse(json_encode(array('estado' => 'OK')), 200);
    }
    
    /**
     * @Route("/ok", name="ok")
     */
    public function okAction() {
//        $doctrine = $this->getDoctrine();
//
//        $CIUDADANOS = Usuario::getCiudadanosVivos($doctrine);
//        $unDia = 86400;
//        $motivo = "Fair play";
//        foreach($CIUDADANOS as $CIUDADANO){
//            Usuario::operacionSobreTdV($doctrine, $CIUDADANO, $unDia, $motivo);
//        }
//        return new JsonResponse(json_encode(array('estado' => 'OK')), 200);
    }

}
