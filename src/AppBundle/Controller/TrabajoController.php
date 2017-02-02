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

/**
 * Description of TrabajoController
 *
 * @author araluce
 */
class TrabajoController extends Controller {

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
        $DATOS['ULT_CAL'] = Utils::getUltimasCalificacionesSeccion($doctrine, 'inspeccion_trabajo');
        return $this->render('guardian/ejercicios/ejerciciosInspeccionTrabajo.twig', $DATOS);
    }

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
     * @Route("/ciudadano/trabajo/jornada_laboral/descargarTuits/{usuario_tw}", name="descargarTuits")
     */
    public function descargarTuitsAction(Request $request, $usuario_tw) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/trabajo/jornada_laboral/descargarTuits/' . $usuario_tw);
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado')), 200);
        }
        $id_usuario = $session->get('id_usuario');
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($id_usuario);
        $count = 10;
        $SEGUIDOS = 0;
        if (isset($usuario_tw)) {
            $SEGUIDOS = Twitter::twitter2($doctrine, $USUARIO, $usuario_tw, $count);
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
            $SEGUIDOS = Twitter::twitter2($doctrine, $USUARIO, $usuario_tw, $count);

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
            $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/trabajo/jornada_laboral/seguir');
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
            if ($alias_usu_dest !== null || $alias_usu_dest !== 'null') {
                $alias_usu_dest = Usuario::aliasToId($alias_usu_dest, $doctrine);
            } else {
                $alias_usu_dest = null;
            }
            $fecha = Twitter::getFecha($id_tweet);
            if (!$fecha) {
                $fecha = 'ERROR';
            }
            $id_usuario = $this->get('session')->get('id_usuario');

            $respuesta = Twitter::almacenar_tweet($id_tuitero, $id_tweet, $tipo_tweet, $id_usuario, $alias_usu_dest, $fecha, $doctrine);
            return new JsonResponse(json_encode(array('datos' => $respuesta)), 200);
        }
        return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No se han enviado datos')), 200);
    }

    /**
     * 
     * @Route("/guardian/ejercicios/inspeccion/publicar", name="inspeccionPublicarDeporte")
     */
    public function guardianPublicarInspeccionAction(Request $request) {
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
            $EJERCICIO->setFecha(new \DateTime('now'));
            $EJERCICIO->setCoste($COSTE);
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
