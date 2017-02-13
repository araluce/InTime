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
use AppBundle\Utils\Ejercicio;
use AppBundle\Utils\Trabajo;

/**
 * Description of Guardian
 *
 * @author araluce
 */
class Guardian extends Controller {
    
    /**
     * @Route("/guardian/ajustes", name="guardianAjustes")
     */
    public function guardianAjustesAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        // Comprobamos que el usuario es admin, si no, redireccionamos a /
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/ajustes', true);
        if (!$status) {
            return new RedirectResponse('/');
        }
        $DATOS['TITULO'] = 'Ajustes del sistema';
        return $this->render('guardian/ajustes/ajustes.twig', $DATOS);
    }
    
    /**
     * @Route("/guardian/ajustes/getNumTweetsDiarios", name="getNumTweetsDiarios")
     */
    public function getNumTweetsDiariosAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        // Comprobamos que el usuario es admin, si no, redireccionamos a /
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/ajustes/getNumTweetsDiarios', true);
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso denegado')), 200);
        }
        $tweets_diarios = Utils::getConstante($doctrine, 'jornada_laboral_tweets');
        
        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $tweets_diarios)), 200);
    }
    
    /**
     * @Route("/guardian/ajustes/getPagoJornadaLaboral", name="getPagoJornadaLaboral")
     */
    public function getPagoJornadaLaboralAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        // Comprobamos que el usuario es admin, si no, redireccionamos a /
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/ajustes/getPagoJornadaLaboral', true);
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso denegado')), 200);
        }
        $pago_jornada = Utils::segundosToDias(Utils::getConstante($doctrine, 'jornada_laboral'));
        
        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $pago_jornada)), 200);
    }
    
    /**
     * @Route("/guardian/ajustes/getNumeroSolicitantesPaga", name="getNumeroSolicitantesPaga")
     */
    public function getNumeroSolicitantesPagaAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        // Comprobamos que el usuario es admin, si no, redireccionamos a /
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/ajustes/getNumeroSolicitantesPaga', true);
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso denegado')), 200);
        }
        $n_solicitantes = Utils::getConstante($doctrine, 'num_max_solicitantes_paga');
        
        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $n_solicitantes)), 200);
    }
    
    /**
     * @Route("/guardian/ajustes/getNumeroDiasEntrega", name="getNumeroDiasEntrega")
     */
    public function getNumeroDiasEntregaAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        // Comprobamos que el usuario es admin, si no, redireccionamos a /
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/ajustes/getNumeroDiasEntrega', true);
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso denegado')), 200);
        }
        $n_dias_entrega = Utils::getConstante($doctrine, 'diasDifEntregas');
        
        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $n_dias_entrega)), 200);
    }
    
    /**
     * @Route("/guardian/ajustes/getTiempoComida", name="getTiempoComida")
     */
    public function getTiempoComidaAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        // Comprobamos que el usuario es admin, si no, redireccionamos a /
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/ajustes/getTiempoComida', true);
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso denegado')), 200);
        }
        $TsC = Utils::segundosToDias(Utils::getConstante($doctrine, 'tiempo_acabar_de_comer'));
        
        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $TsC)), 200);
    }
    
    /**
     * @Route("/guardian/ajustes/getTiempoBebida", name="getTiempoBebida")
     */
    public function getTiempoBebidaAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        // Comprobamos que el usuario es admin, si no, redireccionamos a /
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/ajustes/getTiempoBebida', true);
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso denegado')), 200);
        }
        $TsB = Utils::segundosToDias(Utils::getConstante($doctrine, 'tiempo_acabar_de_beber'));
        
        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $TsB)), 200);
    }
    
    /**
     * @Route("/guardian/ajustes/getDisparadorApuesta", name="getDisparadorApuesta")
     */
    public function getDisparadorApuestaAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        // Comprobamos que el usuario es admin, si no, redireccionamos a /
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/ajustes/getDisparadorApuesta', true);
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso denegado')), 200);
        }
        $disparador_apuesta = Utils::getConstante($doctrine, 'disparador_apuesta');
        
        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $disparador_apuesta)), 200);
    }
    
    /**
     * @Route("/guardian/ajustes/getTestCorrecto", name="getTestCorrecto")
     */
    public function getTestCorrectoAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        // Comprobamos que el usuario es admin, si no, redireccionamos a /
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/ajustes/getPagoJornadaLaboral', true);
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso denegado')), 200);
        }
        $pago_jornada = Utils::segundosToDias(Utils::getConstante($doctrine, 'test_correcto'));
        
        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $pago_jornada)), 200);
    }
    
    /**
     * @Route("/guardian/ajustes/getPremioMina", name="getPremioMina")
     */
    public function getPremioMinaAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        // Comprobamos que el usuario es admin, si no, redireccionamos a /
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/ajustes/getPremioMina', true);
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso denegado')), 200);
        }
        $pago_jornada = Utils::segundosToDias(Utils::getConstante($doctrine, 'premio_mina'));
        
        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $pago_jornada)), 200);
    }
        
        
    /**
     * @Route("/guardian/ajustes/getPremioBaseMina", name="getPremioBaseMina")
     */
    public function getPremioBaseMinaAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        // Comprobamos que el usuario es admin, si no, redireccionamos a /
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/ajustes/getPremioBaseMina', true);
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso denegado')), 200);
        }
        $pago_jornada = Utils::segundosToDias(Utils::getConstante($doctrine, 'premio_base_mina'));
        
        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $pago_jornada)), 200);
    }
    
    /**
     * @Route("/guardian/ajustes/getInteresPrestamo", name="getInteresPrestamo")
     */
    public function getInteresPrestamoAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        // Comprobamos que el usuario es admin, si no, redireccionamos a /
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/ajustes/getInteresPrestamo', true);
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso denegado')), 200);
        }
        $pago_jornada = Utils::getConstante($doctrine, 'interes_prestamo');
        
        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $pago_jornada)), 200);
    }
    
    /**
     * @Route("/guardian/ajustes/getTiempoMaximoPrestado", name="getTiempoMaximoPrestado")
     */
    public function getTiempoMaximoPrestadoAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        // Comprobamos que el usuario es admin, si no, redireccionamos a /
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/ajustes/getTiempoMaximoPrestado', true);
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso denegado')), 200);
        }
        $pago_jornada = Utils::segundosToDias(Utils::getConstante($doctrine, 'tiempo_max_prestamo'));
        
        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $pago_jornada)), 200);
    }
    
    /**
     * 
     * @Route("/guardian/ejemploPost", name="publicarPaga")
     */
    public function ejemploPostAction(Request $request) {
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
