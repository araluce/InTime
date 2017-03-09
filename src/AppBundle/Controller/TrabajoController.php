<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use \Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Utils\Usuario;
use AppBundle\Utils\Utils;
use AppBundle\Utils\Twitter;
use AppBundle\Utils\DataManager;
use AppBundle\Utils\Ejercicio;
use AppBundle\Utils\Trabajo;

/**
 * Description of TrabajoController
 *
 * @author araluce
 */
class TrabajoController extends Controller {

    /**
     * @Route("/ciudadano/trabajo/jornada_laboral", name="jornadaLaboral")
     */
    public function jornadaLaboralAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/trabajo/jornada_laboral');
        if (!$status) {
            return new RedirectResponse('/');
        }
        $DATOS = DataManager::setDefaultData($doctrine, 'Jornada Laboral', $session);
        return $this->render('ciudadano/trabajo/jornada_laboral.twig', $DATOS);
    }

    /**
     * @Route("/ciudadano/trabajo/jornada_laboral/getSeguidos", name="getSeguidos")
     */
    public function getSeguidosAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/trabajo/jornada_laboral/getSeguidos');
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado')), 200);
        }
        $id_usuario = $session->get('id_usuario');
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($id_usuario);
        $USUARIO_X_TUITERO = $doctrine->getRepository('AppBundle:UsuarioXTuitero')->findByIdUsuario($USUARIO);
        $SEGUIDOS = [];
        foreach ($USUARIO_X_TUITERO as $SEGUIDO) {
            $aux = [];
            $aux['ID'] = $SEGUIDO->getIdTuitero();
            $SEGUIDOS[] = $aux;
        }
        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $SEGUIDOS)), 200);
    }

    /**
     * @Route("/ciudadano/trabajo/jornada_laboral/descargarTuits/{usuario_tw}/{offset}", name="descargarTuits")
     */
    public function descargarTuitsAction(Request $request, $usuario_tw, $offset) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/trabajo/jornada_laboral/descargarTuits/' . $usuario_tw);
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado')), 200);
        }
        $id_usuario = $session->get('id_usuario');
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($id_usuario);
        $SEGUIDOS = 0;
        if (isset($usuario_tw)) {
            $SEGUIDOS = Twitter::twitter($doctrine, $USUARIO, $usuario_tw, $offset);
        }

        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $SEGUIDOS)), 200);
    }

    /**
     * @Route("/ciudadano/trabajo/jornada_laboral/seguir", name="seguir")
     */
    public function seguirAction(Request $request) {
        if ($request->getMethod() == 'POST') {
            $doctrine = $this->getDoctrine();
            $session = $request->getSession();
            $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/trabajo/jornada_laboral/seguir');
            if (!$status) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado')), 200);
            }
            $id_usuario = $session->get('id_usuario');
            $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($id_usuario);

            $usuario_tw = $request->request->get('usuario_tw');
            $count = 10;
            $SEGUIDOS = Twitter::twitter($doctrine, $USUARIO, $usuario_tw, $count);

            return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $SEGUIDOS)), 200);
        }
        return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No se han enviado datos')), 200);
    }

    /**
     * @Route("/ciudadano/trabajo/jornada_laboral/almacenarTweet", name="almacenarTweet")
     */
    public function almacenarTweet(Request $request) {
        if ($request->getMethod() == 'POST') {
            $doctrine = $this->getDoctrine();
            $session = $request->getSession();
            $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/trabajo/jornada_laboral/almacenarTweet');
            if (!$status) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado')), 200);
            }
            $id_tweet = $request->request->get('id_tweet');
            $id_tuitero = $request->request->get('id_tuitero');
            $tipo_tweet = $request->request->get('tipo_tweet');
            $alias_usu_dest = $request->request->get('alias_usu_dest');
            $text_tweet = $request->request->get('text_tweet');
            $coincidencias = [];
            preg_match("/@{1}[a-z,-,_]*/i", $text_tweet, $coincidencias);
            if (count($coincidencias)) {
                $id_tuitero = str_replace('@', '', $coincidencias[0]);
            }
            $id_usuario = $this->get('session')->get('id_usuario');
            if ($alias_usu_dest === null || $alias_usu_dest === 'null' || $alias_usu_dest === '') {
                $alias_usu_dest = $id_usuario;
            }
            $fecha = Twitter::getFecha($id_tweet);
            if (!$fecha) {
                $fecha = 'ERROR';
            }

            Twitter::almacenar_tweet($id_tuitero, $id_tweet, $tipo_tweet, $id_usuario, $alias_usu_dest, $fecha, $doctrine);
            return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => 'Tweet almacenado correctamente')), 200);
        }
        return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No se han enviado datos')), 200);
    }
    
    /**
     * @Route("/ciudadano/trabajo/jornada_laboral/eliminarTweet", name="eliminarTweet")
     */
    public function eliminarTweet(Request $request) {
        if ($request->getMethod() == 'POST') {
            $doctrine = $this->getDoctrine();
            $em = $doctrine->getManager();
            $session = $request->getSession();
            $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/trabajo/jornada_laboral/eliminarTweet');
            if (!$status) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado')), 200);
            }
            $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get('id_usuario'));
            $id_tweet = $request->request->get('id_tweet');
            $id_mochila = $request->request->get('id_mochila');
            $TIPO_TWEET = $doctrine->getRepository('AppBundle:TipoTweet')->findOneById($id_mochila);
            if(null === $TIPO_TWEET){
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Error inesperado')), 200);
            }
            $mochilaEliminar = $doctrine->getRepository('AppBundle:MochilaTweets')->findOneBy([
                'idTweet' => $id_tweet, 'idUsuario' => $USUARIO, 'idTipoTweet' => $TIPO_TWEET
            ]);
            if(null !== $mochilaEliminar){
                $em->remove($mochilaEliminar);
                $em->flush();
                return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => 'Tweet eliminado correctamente')), 200);
            }
            
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No se ha encontrado el tweet: ' . $id_mochila)), 200);
        }
        return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No se han enviado datos')), 200);
    }

    /**
     * @Route("/ciudadano/trabajo/jornada_laboral/mostrarMochila/{id_tipo_tweet}", name="mostrarMochilaTipo")
     */
    public function mostrarMochilaAction(Request $request, $id_tipo_tweet) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/trabajo/jornada_laboral/mostrarMochila/' . $id_tipo_tweet);
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado')), 200);
        }
        $id_usuario = $session->get('id_usuario');
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($id_usuario);
        $TIPO_TWEET = $doctrine->getRepository('AppBundle:TipoTweet')->findOneById($id_tipo_tweet);
        if ($TIPO_TWEET === null) {
            Utils::setError($doctrine, 1, 'Se ha intentado descargar una mochila no identificada con el id: ' . $id_tipo_tweet);
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Este tipo de mochila no existe')), 200);
        }
        $TWEETS = Twitter::getMochila($doctrine, $USUARIO, $TIPO_TWEET);
        if (!$TWEETS) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Mochila vacía')), 200);
        }
        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $TWEETS)), 200);
    }

    /**
     * 
     * @Route("/ciudadano/trabajo/jornada_laboral/getAlias", name="mostrarMochila")
     */
    public function getAliasAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/trabajo/jornada_laboral/getAlias');
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado')), 200);
        }
        $USUARIOS = Usuario::getCiudadanosVivos($doctrine);
        $id_usuario = $session->get('id_usuario');
        $MI_USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($id_usuario);
        $DATOS = [];
        if (!count($USUARIOS)) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No hay usuarios vivos aún')), 200);
        }
        foreach ($USUARIOS as $USUARIO) {
            if ($USUARIO !== $MI_USUARIO && $USUARIO->getSeudonimo() !== null) {
                $aux = [];
                $aux['ALIAS'] = $USUARIO->getSeudonimo();
                $aux['ID'] = $USUARIO->getIdUsuario();
                $DATOS[] = $aux;
            }
        }
        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $DATOS)), 200);
    }

    /**
     * @Route("/ciudadano/trabajo/paga_extra", name="paga_extra")
     */
    public function paga_extraAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/trabajo/paga_extra');
        if (!$status) {
            return new RedirectResponse('/');
        }
        $DATOS = DataManager::setDefaultData($doctrine, 'Paga extra', $session);

        return $this->render('ciudadano/trabajo/paga_extra.twig', $DATOS);
    }

    /**
     * @Route("/ciudadano/trabajo/getEjerciciosPaga", name="getEjerciciosPaga")
     */
    public function getEjerciciosPagaAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/trabajo/inspeccion/getEjerciciosPaga');
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado')), 200);
        }
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get('id_usuario'));
        Ejercicio::actualizarEjercicioXUsuario($doctrine, $USUARIO);
        $EJERCICIO_SECCION = $doctrine->getRepository('AppBundle:EjercicioSeccion')->findOneBySeccion('paga_extra');
        $EJERCICIOS = $doctrine->getRepository('AppBundle:Ejercicio')->findByIdEjercicioSeccion($EJERCICIO_SECCION);
        if (!count($EJERCICIOS)) {
            return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => 'Aún no hay ejercicios en esta sección')), 200);
        }
        $DATOS = [];
        $DATOS['SOLICITADO'] = false;
        if (Utils::ejercicios_solicitados_en($doctrine, $USUARIO, 'paga_extra')) {
            $DATOS['SOLICITADO'] = true;
        }
        $DATOS['EJERCICIOS'] = [];
        foreach ($EJERCICIOS as $ejercicio) {
            $DATOS['EJERCICIOS'][] = DataManager::getDatosEjercicioPaga($doctrine, $USUARIO, $ejercicio);
        }
        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $DATOS)), 200);
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

        return $this->render('ciudadano/trabajo/inspeccion_trabajo.twig', $DATOS);
    }

    /**
     * @Route("/ciudadano/trabajo/getEjerciciosInspeccion", name="getEjerciciosInspeccion")
     */
    public function getEjerciciosInspeccionAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/trabajo/inspeccion/getEjerciciosInspeccion');
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado')), 200);
        }
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get('id_usuario'));
        Ejercicio::actualizarEjercicioXUsuario($doctrine, $USUARIO);
        $EJERCICIO_SECCION = $doctrine->getRepository('AppBundle:EjercicioSeccion')->findOneBySeccion('inspeccion_trabajo');
        $EJERCICIOS = $doctrine->getRepository('AppBundle:Ejercicio')->findByIdEjercicioSeccion($EJERCICIO_SECCION);
        $DATOS = [];
        $DATOS['EJERCICIOS'] = [];
        if (!count($EJERCICIOS)) {
            return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => 'Aún no hay ejercicios en esta sección')), 200);
        }
        foreach ($EJERCICIOS as $EJERCICIO) {
            $DATOS['EJERCICIOS'][] = DataManager::getDatosEjercicioInspeccion($doctrine, $USUARIO, $EJERCICIO);
        }
        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $DATOS)), 200);
    }

//    public function inspeccion_trabajoAction(Request $request) {
//        $doctrine = $this->getDoctrine();
//        $session = $request->getSession();
//        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/trabajo/inspeccion');
//        if (!$status) {
//            return new RedirectResponse('/');
//        }
//        $DATOS = DataManager::setDefaultData($doctrine, 'Inspección de trabajo', $session);
//        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get('id_usuario'));
//        Ejercicio::actualizarEjercicioXUsuario($doctrine, $USUARIO);
//        $EJERCICIO_SECCION = $doctrine->getRepository('AppBundle:EjercicioSeccion')->findOneByIdEjercicioSeccion(1);
//        $EJERCICIO_X_GRUPO = $doctrine->getRepository('AppBundle:EjercicioXGrupo')->findAll();
//        $EJERCICIOS = $doctrine->getRepository('AppBundle:Ejercicio')->findByIdEjercicioSeccion($EJERCICIO_SECCION);
//        $DATOS['SECCION'] = $EJERCICIO_SECCION->getSeccion();
//        $DATOS['EJERCICIOS'] = [];
//
//        $ids_ejercicios = [];
//
//        if (count($EJERCICIO_X_GRUPO)) {
//            $ids_ejercicios_grupos = [];
//            foreach ($EJERCICIO_X_GRUPO as $ejercicio_grupo) {
//                $ejercicio = $ejercicio_grupo->getIdEjercicio();
//                if (!in_array($ejercicio_grupo->getIdGrupoEjercicios(), $ids_ejercicios_grupos)) {
//                    $ids_ejercicios_grupos[] = $ejercicio_grupo->getIdGrupoEjercicios();
//                    $DATOS['EJERCICIOS'][] = Utils::getDatosInspeccion($doctrine, $USUARIO, null, $ejercicio_grupo->getIdGrupoEjercicios());
//                }
//                $ids_ejercicios[] = $ejercicio->getIdEjercicio();
//            }
//        }
//        if (count($EJERCICIOS)) {
//            foreach ($EJERCICIOS as $ejercicio) {
//                if (!in_array($ejercicio->getIdEjercicio(), $ids_ejercicios)) {
//                    $DATOS['EJERCICIOS'][] = Utils::getDatosInspeccion($doctrine, $USUARIO, $ejercicio, null);
//                }
//            }
//        }
//
//        return $this->render('ciudadano/trabajo/inspeccion_trabajo.html.twig', $DATOS);
//    }
    /**
     * @Route("/ciudadano/trabajo/getEjercicioInspeccion/{id_ejercicio}", name="getEjercicioInspeccion")
     */
    public function getEjercicioInspeccionAction(Request $request, $id_ejercicio) {
        $session = $this->get('session');
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/trabajo/getEjercicioInspeccion/' . $id_ejercicio);
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado')), 200);
        }
        $EJERCICIO = $doctrine->getRepository('AppBundle:Ejercicio')->findOneByIdEjercicio($id_ejercicio);
        if ($EJERCICIO === null) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'El ejercicio no existe')), 200);
        }
        $RESPUESTAS = $doctrine->getRepository('AppBundle:EjercicioRespuesta')->findByIdEjercicio($EJERCICIO);
        if (!count($RESPUESTAS)) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Este ejercicio no tiene respuestas')), 200);
        }
        $DATOS = [];
        $DATOS['ENUNCIADO'] = $EJERCICIO->getEnunciado();
        $DATOS['RESPUESTAS'] = [];
        foreach ($RESPUESTAS as $RESPUESTA) {
            $DATOS['RESPUESTAS'][] = $RESPUESTA->getRespuesta();
        }
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get('id_usuario'));
        $EJERCICIO_USUARIO = $doctrine->getRepository('AppBundle:EjercicioXUsuario')->findOneBy([
            'idEjercicio' => $EJERCICIO, 'idUsu' => $USUARIO
        ]);
        $EJERCICIO_USUARIO->setVisto(1);
        $em->persist($EJERCICIO_USUARIO);
        $em->flush();
        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $DATOS)), 200);
    }

    /**
     * @Route("/ciudadano/trabajo/inspeccion/corregir/{id_ejercicio}", name="corregirInspeccion")
     */
    public function corregirInspeccionAction(Request $request, $id_ejercicio) {
        if ($request->getMethod() == 'POST') {
            $session = $this->get('session');
            $doctrine = $this->getDoctrine();
            $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/trabajo/inspeccion/corregir/' . $id_ejercicio);
            if (!$status) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado')), 200);
            }
            $EJERCICIO = $doctrine->getRepository('AppBundle:Ejercicio')->findOneByIdEjercicio($id_ejercicio);
            if ($EJERCICIO === null) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'El ejercicio no existe')), 200);
            }
            $RESPUESTAS = $doctrine->getRepository('AppBundle:EjercicioRespuesta')->findByIdEjercicio($EJERCICIO);
            if (!count($RESPUESTAS)) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Este ejercicio no tiene respuestas')), 200);
            }
            $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get('id_usuario'));
            $RESP = [];
            $RESP[] = $request->request->get('resp1');
            $RESP[] = $request->request->get('resp2');
            $RESP[] = $request->request->get('resp3');
            $RESP[] = $request->request->get('resp4');
            $nota = Trabajo::calcularTest($RESPUESTAS, $RESP);
            $return = Trabajo::setCalificacionInspeccion($doctrine, $USUARIO, $EJERCICIO, $nota);

            return new JsonResponse(json_encode($return), 200);
        }
        return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No se han enviado datos')), 200);
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
     * @Route("/ciudadano/trabajo/paga/solicitar/{id_ejercicio}", name="solicitarPaga")
     */
    public function solicitarPagaAction(Request $request, $id_ejercicio) {
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/trabajo/paga/solicitar/' . $id_ejercicio);
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado')), 200);
        }
        $id_usuario = $session->get('id_usuario');
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($id_usuario);
        $EJERCICIO = $doctrine->getRepository('AppBundle:Ejercicio')->findOneByIdEjercicio($id_ejercicio);
        if ($EJERCICIO === null) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Este ejercicio no existe')), 200);
        }
        $ESTADO_SOLICITADO = $doctrine->getRepository('AppBundle:EjercicioEstado')->findOneByEstado('solicitado');

        $EJERCICIO_CALIFICACION = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findOneBy([
            'idEjercicio' => $EJERCICIO, 'idUsuario' => $USUARIO
        ]);
        if ($EJERCICIO_CALIFICACION !== null) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Este ejercicio ya había sido solicitado')), 200);
        }

        $EJERCICIO_CALIFICACION = new \AppBundle\Entity\EjercicioCalificacion();
        $EJERCICIO_CALIFICACION->setFecha(new \DateTime('now'));
        $EJERCICIO_CALIFICACION->setIdCalificaciones(null);
        $EJERCICIO_CALIFICACION->setIdEjercicio($EJERCICIO);
        $EJERCICIO_CALIFICACION->setIdEjercicioEstado($ESTADO_SOLICITADO);
        $EJERCICIO_CALIFICACION->setIdUsuario($USUARIO);
        $EJERCICIO_CALIFICACION->setIdEvaluador(null);
        $EJERCICIO_CALIFICACION->setIdGrupo(null);
        $em->persist($EJERCICIO_CALIFICACION);


        $EJERCICIO_USUARIO = $doctrine->getRepository('AppBundle:EjercicioXUsuario')->findOneBy([
            'idEjercicio' => $EJERCICIO, 'idUsu' => $USUARIO
        ]);
        if ($EJERCICIO_USUARIO !== null) {
            $EJERCICIO_USUARIO->setVisto(1);
            $em->persist($EJERCICIO_USUARIO);
        }
        $em->flush();
        Usuario::operacionSobreTdV($doctrine, $USUARIO, (-1) * $EJERCICIO->getCoste(), 'Cobro - Compra de ejercicio en Paga extra');
        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => 'Ejercicio solicitado correctamente')), 200);
    }

    /**
     * @Route("/ciudadano/trabajo/paga/entregarPaga", name="entregarPaga")
     */
    public function entregarPagaAction(Request $request) {
        $session = $request->getSession();
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/trabajo/paga/entregarPaga');
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado')), 200);
        }
        if ($request->getMethod() == 'POST') {
            $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get("id_usuario"));
            $ENTREGA = $request->files->get('entrega');
            if (null === $ENTREGA) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No se ha seleccionado ningún archivo')), 200);
            }
            $id_ejercicio = $request->request->get('id_ejercicio');
            $EJERCICIO = $doctrine->getRepository('AppBundle:Ejercicio')->findOneByIdEjercicio($id_ejercicio);
            if (null === $EJERCICIO) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'El ejercicio no existe ' . $id_ejercicio)), 200);
            }
            $EJERCICIO_CALIFICACION = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findOneBy([
                'idUsuario' => $USUARIO, 'idEjercicio' => $EJERCICIO
            ]);
            if ($ENTREGA->getClientSize() < $ENTREGA->getMaxFilesize()) {
                $ruta = 'USUARIOS/' . $USUARIO->getDni() . '/paga_extra/' . $EJERCICIO_CALIFICACION->getIdEjercicioCalificacion();
                if (!file_exists($ruta)) {
                    mkdir($ruta, 0777, true);
                }
                $nombre_entrega = Utils::replaceAccented($ENTREGA->getClientOriginalName());
                $UPLOAD = $ENTREGA->move($ruta, $nombre_entrega);
                if ($ENTREGA->getError()) {
                    return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => $ENTREGA->getErrorMessage())), 200);
                }
                $CALIFICACION_MEDIA = $doctrine->getRepository('AppBundle:Calificaciones')->findOneByIdCalificaciones(5);
                $RESULTADOS = Utils::setNota($doctrine, $USUARIO, $EJERCICIO, $CALIFICACION_MEDIA);

                $EJERCICIO_ENTREGA = $doctrine->getRepository('AppBundle:EjercicioEntrega')->findOneBy([
                    'idUsuario' => $USUARIO, 'idEjercicio' => $EJERCICIO
                ]);
                if ($EJERCICIO_ENTREGA === null) {
                    $EJERCICIO_ENTREGA = new \AppBundle\Entity\EjercicioEntrega();
                }
                $EJERCICIO_ENTREGA->setIdUsuario($USUARIO);
                $EJERCICIO_ENTREGA->setIdEjercicio($EJERCICIO);
                $EJERCICIO_ENTREGA->setNombre($nombre_entrega);
                $EJERCICIO_ENTREGA->setMime($ENTREGA->getClientMimeType());
                $EJERCICIO_ENTREGA->setFecha($EJERCICIO_CALIFICACION->getFecha());
                $em->persist($EJERCICIO_ENTREGA);

                $ESTADO_ENTREGADO = $doctrine->getRepository('AppBundle:EjercicioEstado')->findOneByEstado('entregado');
                $EJERCICIO_CALIFICACION->setIdEjercicioEstado($ESTADO_ENTREGADO);
                $em->persist($EJERCICIO_CALIFICACION);
                $em->flush();
                return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => 'Reto entregado correctamente')), 200);
            }
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'El archivo supera el límite máximo permitido')), 200);
        }
        return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No se han recibido datos')), 200);
    }

    /**
     * @Route("/ciudadano/trabajo/paga/obtenerCalificacion", name="obtenerCalificacion")
     */
    public function obtenerCalificacionAction(Request $request) {
        $session = $request->getSession();
        $doctrine = $this->getDoctrine();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/trabajo/paga/obtenerCalificacion');
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado')), 200);
        }
        if ($request->getMethod() == 'POST') {
            $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get("id_usuario"));
            $id_ejercicio = $request->request->get('id_ejercicio');
            $EJERCICIO = $doctrine->getRepository('AppBundle:Ejercicio')->findOneByIdEjercicio($id_ejercicio);
            if (null === $EJERCICIO) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'El ejercicio no existe ' . $id_ejercicio)), 200);
            }
            $EJERCICIO_CALIFICACION = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findOneBy([
                'idUsuario' => $USUARIO, 'idEjercicio' => $EJERCICIO
            ]);
            $DATOS = [];
            if ($EJERCICIO->getIdEjercicioSeccion()->getSeccion() === 'paga_extra') {
                if (preg_match('/"([^"]+)"/', $EJERCICIO->getEnunciado(), $coincidencias)) {
                    $DATOS['TITULO'] = $coincidencias[1];
                }
                if(trim($DATOS['TITULO']) === ''){
                    $DATOS['TITULO'] = 'Sin título';
                }
            }
            $DATOS['ICONO'] = $EJERCICIO_CALIFICACION->getIdCalificaciones()->getCorrespondenciaIcono();
            $DATOS['TEXTO'] = $EJERCICIO_CALIFICACION->getIdCalificaciones()->getCorrespondenciaTexto();
            return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $DATOS)), 200);
        }
        return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No se han recibido datos')), 200);
    }

    /**
     * @Route("/guardian/ejercicios/inspeccion", name="ejerciciosInspeccion")
     */
    public function ejerciciosInspeccionAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        // Comprobamos que el usuario es admin, si no, redireccionamos a /
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/ejercicios/inspeccion', true);
        if (!$status) {
            return new RedirectResponse('/');
        }
        $DATOS['TITULO'] = 'Inspección';
        return $this->render('guardian/ejercicios/ejerciciosInspeccionTrabajo.twig', $DATOS);
    }

    /**
     * 
     * @Route("/guardian/ejercicios/inspeccion/publicar", name="publicarInspeccion")
     */
    public function publicarInspeccionAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/ejercicios/inspeccion/publicar', true);
        if (!$status) {
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado'));
        }

        // Si se ha enviado un formulario
        if ($request->getMethod() == 'POST') {
            $em = $doctrine->getManager();
            $EJERCICIO_SECCION = $doctrine->getRepository('AppBundle:EjercicioSeccion')->findOneBySeccion('inspeccion_trabajo');
            if ($EJERCICIO_SECCION === null) {
                return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Sección no existe'));
            }
            $EJERCICIO_TIPO = $doctrine->getRepository('AppBundle:EjercicioTipo')->findOneByTipo('test');
            if ($EJERCICIO_TIPO === null) {
                return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Entrega no existe'));
            }
            // Obtenemos todos los enunciados del formulario
            $ENUNCIADO = $request->request->get('ENUNCIADO');
            // Buscamos si el enunciado ya existía para ese tipo y esa sección
            $EJERCICIO = $doctrine->getRepository('AppBundle:Ejercicio')->findOneBy([
                'idEjercicioSeccion' => $EJERCICIO_SECCION,
                'enunciado' => $ENUNCIADO
            ]);
            // Si el ejercicio no existe se crea uno nuevo
            if ($EJERCICIO === null) {
                $EJERCICIO = new \AppBundle\Entity\Ejercicio();
            }
            $EJERCICIO->setIdTipoEjercicio($EJERCICIO_TIPO);
            $EJERCICIO->setIdEjercicioSeccion($EJERCICIO_SECCION);
            $EJERCICIO->setEnunciado($ENUNCIADO);
            $EJERCICIO->setFecha(new \DateTime('now'));
            $EJERCICIO->setCoste(0);
            $em->persist($EJERCICIO);
            $em->flush();

            // Creamos las respuestas
            $RESPUESTAS = $request->request->get('RESPUESTAS');
            $checkbox = $request->request->get('RESPUESTAS_CHECK');
            $size = count($RESPUESTAS);
            for ($n = 0; $n < $size; $n++) {
                if ($checkbox[$n] !== 'on') {
                    $CORRECTA = 0;
                } else {
                    $CORRECTA = 1;
                }
                // Almacenamos cada respuesta en la BD asociadas al ejercicio
                $EJERCICIO_RESPUESTA = new \AppBundle\Entity\EjercicioRespuesta();
                $EJERCICIO_RESPUESTA->setIdEjercicio($EJERCICIO);
                $EJERCICIO_RESPUESTA->setRespuesta($RESPUESTAS[$n]);
                $EJERCICIO_RESPUESTA->setCorrecta($CORRECTA);
                $em->persist($EJERCICIO_RESPUESTA);
            }
            $em->flush();
            
            $CIUDADANOS = Usuario::getCiudadanosVivos($doctrine);
            $PENALIZACION = Utils::getConstante($doctrine, 'test_incorrecto');
            if(count($CIUDADANOS)){
                foreach($CIUDADANOS as $CIUDADANO){
                    Usuario::operacionSobreTdV($doctrine, $CIUDADANO, (-1)*$PENALIZACION, 'Cobro - Inspección de trabajo');
                }
            }
            return new JsonResponse(array('estado' => 'OK', 'message' => 'Ejercicio publicado correctamente'));
        }
        return new JsonResponse(array('estado' => 'ERROR', 'message' => 'No se han enviado datos'));
    }

    /**
     * @Route("/guardian/ejercicios/paga", name="ejerciciosPaga")
     */
    public function ejerciciosPagaAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        // Comprobamos que el usuario es admin, si no, redireccionamos a /
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/ejercicios/paga', true);
        if (!$status) {
            return new RedirectResponse('/');
        }
        $DATOS['TITULO'] = 'Paga extra';
        $DATOS['ULT_CAL'] = Utils::getUltimasCalificacionesSeccion($doctrine, 'paga_extra');
        return $this->render('guardian/ejercicios/ejerciciosPagaExtra.twig', $DATOS);
    }

    /**
     * 
     * @Route("/guardian/ejercicios/paga/publicar", name="publicarPaga")
     */
    public function publicarPagaAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/ejercicios/paga/publicar', true);
        if (!$status) {
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado'));
        }

        // Si se ha enviado un formulario
        if ($request->getMethod() == 'POST') {
            $em = $doctrine->getManager();
            $EJERCICIO_SECCION = $doctrine->getRepository('AppBundle:EjercicioSeccion')->findOneBySeccion('paga_extra');
            if ($EJERCICIO_SECCION === null) {
                return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Sección no existe'));
            }
            $EJERCICIO_TIPO = $doctrine->getRepository('AppBundle:EjercicioTipo')->findOneByTipo('entrega');
            if ($EJERCICIO_TIPO === null) {
                return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Entrega no existe'));
            }
            // Obtenemos todos los enunciados del formulario
            $ENUNCIADO = $request->request->get('ENUNCIADO');
            // Obtenemos la fecha de presentación
            $FECHA = str_replace("T", " ", $request->request->get('FECHA')) . ":00";
            $FECHA_FORMATO = \DateTime::createFromFormat('Y-m-d H:i:s', $FECHA);
            // Obtenemos el coste del ejercicio
            $COSTE = $request->request->get('COSTE');
            // Buscamos si el enunciado ya existía para ese tipo y esa sección
            $EJERCICIO = $doctrine->getRepository('AppBundle:Ejercicio')->findOneBy([
                'idEjercicioSeccion' => $EJERCICIO_SECCION,
                'enunciado' => $ENUNCIADO
            ]);
            // Si el ejercicio no existe se crea uno nuevo
            if ($EJERCICIO === null) {
                $EJERCICIO = new \AppBundle\Entity\Ejercicio();
            }
            $EJERCICIO->setIdTipoEjercicio($EJERCICIO_TIPO);
            $EJERCICIO->setIdEjercicioSeccion($EJERCICIO_SECCION);
            $EJERCICIO->setEnunciado($ENUNCIADO);
            $EJERCICIO->setFecha($FECHA_FORMATO);
            $EJERCICIO->setCoste($COSTE);
            $em->persist($EJERCICIO);
            $em->flush();

            $CALIFICACIONES = $doctrine->getRepository('AppBundle:Calificaciones')->findAll();
            foreach ($CALIFICACIONES as $CALIFICACION) {
                $b = $request->request->get('BONIFICACION_' . $CALIFICACION->getIdCalificaciones());
                $BONIFICACION = new \AppBundle\Entity\EjercicioBonificacion();
                $BONIFICACION->setIdEjercicio($EJERCICIO);
                $BONIFICACION->setIdCalificacion($CALIFICACION);
                $BONIFICACION->setBonificacion($b);
                $em->persist($BONIFICACION);
            }
            $em->flush();
            return new JsonResponse(array('estado' => 'OK', 'message' => 'Ejercicio publicado correctamente'));
        }
        return new JsonResponse(array('estado' => 'ERROR', 'message' => 'No se han enviado datos'));
    }

}
