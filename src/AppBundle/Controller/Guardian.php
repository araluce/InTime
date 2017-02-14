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
     * @Route("/guardian/ajustes/setJornadaLaboral", name="setJornadaLaboral")
     */
    public function setJornadaLaboralAction(Request $request) {
        if ($request->getMethod() == 'POST') {
            $doctrine = $this->getDoctrine();
            $em = $doctrine->getManager();
            $session = $request->getSession();
            $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/ajustes/setJornadaLaboral', true);
            if (!$status) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado')), 200);
            }
            $n_tweets = $request->request->get('n_tweets');
            $pago_jornada = $request->request->get('pago_jornada');
            if($n_tweets <= 0 || $pago_jornada <= 0){
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Error inesperado')), 200);
            }
            $CONSTANTE_N_TWEETS = $doctrine->getRepository('AppBundle:Constante')->findOneByClave('jornada_laboral_tweets');
            if ($CONSTANTE_N_TWEETS === null) {
                Utils::setError($doctrine, 1, 'setJornadaLaboralAction no existe constante jornada_laboral_tweets');
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Error inesperado')), 200);
            }
            $CONSTANTE_PAGO_JORNADA = $doctrine->getRepository('AppBundle:Constante')->findOneByClave('jornada_laboral');
            if ($CONSTANTE_PAGO_JORNADA === null) {
                Utils::setError($doctrine, 1, 'setJornadaLaboralAction no existe constante jornada_laboral');
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Error inesperado')), 200);
            }
            $CONSTANTE_N_TWEETS->setValor($n_tweets);
            $CONSTANTE_PAGO_JORNADA->setValor($pago_jornada);
            $em->persist($CONSTANTE_N_TWEETS);
            $em->persist($CONSTANTE_PAGO_JORNADA);
            $em->flush();
            return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => 'Cambios realizados correctamente')), 200);
        }
        return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No se han enviado datos')), 200);
    }
    
    /**
     * 
     * @Route("/guardian/ajustes/setPagaExtra", name="setPagaExtra")
     */
    public function setPagaExtraAction(Request $request) {
        if ($request->getMethod() == 'POST') {
            $doctrine = $this->getDoctrine();
            $em = $doctrine->getManager();
            $session = $request->getSession();
            $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/ajustes/setPagaExtra', true);
            if (!$status) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado')), 200);
            }
            $num_max_solicitantes_paga = $request->request->get('num_max_solicitantes_paga');
            if($num_max_solicitantes_paga <= 0){
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'La constante debe ser mayor que 0')), 200);
            }
            $CONSTANTE_N_MAX = $doctrine->getRepository('AppBundle:Constante')->findOneByClave('num_max_solicitantes_paga');
            if ($CONSTANTE_N_MAX === null) {
                Utils::setError($doctrine, 1, 'setJornadaLaboralAction no existe constante num_max_solicitantes_paga');
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Error inesperado')), 200);
            }
            $CONSTANTE_N_MAX->setValor($num_max_solicitantes_paga);
            $em->persist($CONSTANTE_N_MAX);
            $em->flush();
            return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => 'Cambios realizados correctamente')), 200);
        }
        return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No se han enviado datos')), 200);
    }
    
    /**
     * 
     * @Route("/guardian/ajustes/setAlimentacion", name="setAlimentacion")
     */
    public function setAlimentacionAction(Request $request) {
        if ($request->getMethod() == 'POST') {
            $doctrine = $this->getDoctrine();
            $em = $doctrine->getManager();
            $session = $request->getSession();
            $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/ajustes/setAlimentacion', true);
            if (!$status) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado')), 200);
            }
            $n_dias_entrega = $request->request->get('n_dias_entrega');
            $tsc = $request->request->get('tsc');
            $tsb = $request->request->get('tsb');
            if($n_dias_entrega <= 0 || $tsc <= 0 || $tsb <= 0){
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Error inesperado')), 200);
            }
            $CONSTANTE_N_DIAS = $doctrine->getRepository('AppBundle:Constante')->findOneByClave('diasDifEntregas');
            if ($CONSTANTE_N_DIAS === null) {
                Utils::setError($doctrine, 1, 'setJornadaLaboralAction no existe constante diasDifEntregas');
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Error inesperado')), 200);
            }
            $CONSTANTE_TSC = $doctrine->getRepository('AppBundle:Constante')->findOneByClave('tiempo_acabar_de_comer');
            if ($CONSTANTE_TSC === null) {
                Utils::setError($doctrine, 1, 'setJornadaLaboralAction no existe constante tiempo_acabar_de_comer');
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Error inesperado')), 200);
            }
            $CONSTANTE_TSB = $doctrine->getRepository('AppBundle:Constante')->findOneByClave('tiempo_acabar_de_beber');
            if ($CONSTANTE_TSB === null) {
                Utils::setError($doctrine, 1, 'setJornadaLaboralAction no existe constante tiempo_acabar_de_beber');
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Error inesperado')), 200);
            }
            $CONSTANTE_N_DIAS->setValor($n_dias_entrega);
            $em->persist($CONSTANTE_N_DIAS);
            $CONSTANTE_TSC->setValor($tsc);
            $em->persist($CONSTANTE_TSC);
            $CONSTANTE_TSB->setValor($tsb);
            $em->persist($CONSTANTE_TSB);
            $em->flush();
            return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => 'Cambios realizados correctamente')), 200);
        }
        return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No se han enviado datos')), 200);
    }
    
    /**
     * 
     * @Route("/guardian/ajustes/setApuestas", name="setApuestas")
     */
    public function setApuestasAction(Request $request) {
        if ($request->getMethod() == 'POST') {
            $doctrine = $this->getDoctrine();
            $em = $doctrine->getManager();
            $session = $request->getSession();
            $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/ajustes/setApuestas', true);
            if (!$status) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado')), 200);
            }
            $disparador_apuesta = $request->request->get('disparador_apuesta');
            if($disparador_apuesta <= 0){
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'La constante debe ser mayor que 0')), 200);
            }
            $CONSTANTE_DISP = $doctrine->getRepository('AppBundle:Constante')->findOneByClave('disparador_apuesta');
            if ($CONSTANTE_DISP === null) {
                Utils::setError($doctrine, 1, 'setJornadaLaboralAction no existe constante disparador_apuesta');
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Error inesperado')), 200);
            }
            $CONSTANTE_DISP->setValor($disparador_apuesta);
            $em->persist($CONSTANTE_DISP);
            $em->flush();
            return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => 'Cambios realizados correctamente')), 200);
        }
        return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No se han enviado datos')), 200);
    }
    
    /**
     * 
     * @Route("/guardian/ajustes/setInspeccion", name="setInspeccion")
     */
    public function setInspeccionAction(Request $request) {
        if ($request->getMethod() == 'POST') {
            $doctrine = $this->getDoctrine();
            $em = $doctrine->getManager();
            $session = $request->getSession();
            $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/ajustes/setInspeccion', true);
            if (!$status) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado')), 200);
            }
            $pago_inspeccion = $request->request->get('pago_inspeccion');
            if($pago_inspeccion <= 0){
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'La constante debe ser mayor que 0')), 200);
            }
            $CONSTANTE_INSP = $doctrine->getRepository('AppBundle:Constante')->findOneByClave('test_correcto');
            if ($CONSTANTE_INSP === null) {
                Utils::setError($doctrine, 1, 'setJornadaLaboralAction no existe constante test_correcto');
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Error inesperado')), 200);
            }
            $CONSTANTE_INSP->setValor($pago_inspeccion);
            $em->persist($CONSTANTE_INSP);
            $em->flush();
            return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => 'Cambios realizados correctamente')), 200);
        }
        return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No se han enviado datos')), 200);
    }
    
    /**
     * 
     * @Route("/guardian/ajustes/setMina", name="setInspeccion")
     */
    public function setMinaAction(Request $request) {
        if ($request->getMethod() == 'POST') {
            $doctrine = $this->getDoctrine();
            $em = $doctrine->getManager();
            $session = $request->getSession();
            $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/ajustes/setMina', true);
            if (!$status) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado')), 200);
            }
            $pago_mina = $request->request->get('pago_mina');
            $pago_base_mina = $request->request->get('pago_base_mina');
            if($pago_mina <= 0 || $pago_base_mina <= 0){
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Error inesperado')), 200);
            }
            $CONSTANTE_PAGO_MINA = $doctrine->getRepository('AppBundle:Constante')->findOneByClave('premio_mina');
            if ($CONSTANTE_PAGO_MINA === null) {
                Utils::setError($doctrine, 1, 'setJornadaLaboralAction no existe constante premio_mina');
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Error inesperado')), 200);
            }
            $CONSTANTE_PAGO_BASE_MINA = $doctrine->getRepository('AppBundle:Constante')->findOneByClave('premio_base_mina');
            if ($CONSTANTE_PAGO_BASE_MINA === null) {
                Utils::setError($doctrine, 1, 'setJornadaLaboralAction no existe constante premio_base_mina');
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Error inesperado')), 200);
            }
            $CONSTANTE_PAGO_MINA->setValor($pago_mina);
            $CONSTANTE_PAGO_BASE_MINA->setValor($pago_base_mina);
            $em->persist($CONSTANTE_PAGO_MINA);
            $em->persist($CONSTANTE_PAGO_BASE_MINA);
            $em->flush();
            return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => 'Cambios realizados correctamente')), 200);
        }
        return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No se han enviado datos')), 200);
    }
    
    /**
     * 
     * @Route("/guardian/ajustes/setPrestamos", name="setPrestamos")
     */
    public function setPrestamosAction(Request $request) {
        if ($request->getMethod() == 'POST') {
            $doctrine = $this->getDoctrine();
            $em = $doctrine->getManager();
            $session = $request->getSession();
            $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/ajustes/setPrestamos', true);
            if (!$status) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado')), 200);
            }
            $interes = $request->request->get('interes');
            $tiempo_max = $request->request->get('max_prestado');
            if($interes <= 0.00 || $tiempo_max <= 0){
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Error inesperado')), 200);
            }
            $CONSTANTE_INTERES = $doctrine->getRepository('AppBundle:Constante')->findOneByClave('interes_prestamo');
            if ($CONSTANTE_INTERES === null) {
                Utils::setError($doctrine, 1, 'setJornadaLaboralAction no existe constante interes_prestamo');
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Error inesperado')), 200);
            }
            $CONSTANTE_TMP = $doctrine->getRepository('AppBundle:Constante')->findOneByClave('tiempo_max_prestamo');
            if ($CONSTANTE_TMP === null) {
                Utils::setError($doctrine, 1, 'setJornadaLaboralAction no existe constante tiempo_max_prestamo');
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Error inesperado')), 200);
            }
            $CONSTANTE_INTERES->setValor($interes);
            $CONSTANTE_TMP->setValor($tiempo_max);
            $em->persist($CONSTANTE_INTERES);
            $em->persist($CONSTANTE_TMP);
            $em->flush();
            return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => 'Cambios realizados correctamente')), 200);
        }
        return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No se han enviado datos')), 200);
    }

}
