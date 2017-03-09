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
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use AppBundle\Utils\Usuario;
use AppBundle\Utils\Utils;
use AppBundle\Utils\Ejercicio;
use AppBundle\Utils\Trabajo;
use AppBundle\Utils\Distrito;

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
     * @Route("/guardian/ajustes/getFelicidad", name="getFelicidadDias")
     */
    public function getFelicidadDiasAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        // Comprobamos que el usuario es admin, si no, redireccionamos a /
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/ajustes/getFelicidad', true);
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso denegado')), 200);
        }
        $diasDifEntregasFelicidad = Utils::getConstante($doctrine, 'diasDifEntregasFelicidad');

        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $diasDifEntregasFelicidad)), 200);
    }
    
    /**
     * @Route("/guardian/ajustes/getFelicidadBonificacion5", name="getFelicidadBonificacion5")
     */
    public function getFelicidadBonificacion5Action(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        // Comprobamos que el usuario es admin, si no, redireccionamos a /
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/ajustes/getFelicidadBonificacion5', true);
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso denegado')), 200);
        }
        $diasDifEntregasFelicidad = Utils::segundosToDias(Utils::getConstante($doctrine, 'felicidadBonificacion5'));

        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $diasDifEntregasFelicidad)), 200);
    }
    
    /**
     * @Route("/guardian/ajustes/getFelicidadBonificacion15", name="getFelicidadBonificacion15")
     */
    public function getFelicidadBonificacion15Action(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        // Comprobamos que el usuario es admin, si no, redireccionamos a /
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/ajustes/getFelicidadBonificacion15', true);
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso denegado')), 200);
        }
        $diasDifEntregasFelicidad = Utils::segundosToDias(Utils::getConstante($doctrine, 'felicidadBonificacion15'));

        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $diasDifEntregasFelicidad)), 200);
    }
    
    /**
     * @Route("/guardian/ajustes/getFelicidadBonificacion25", name="getFelicidadBonificacion25")
     */
    public function getFelicidadBonificacion25Action(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        // Comprobamos que el usuario es admin, si no, redireccionamos a /
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/ajustes/getFelicidadBonificacion25', true);
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso denegado')), 200);
        }
        $diasDifEntregasFelicidad = Utils::segundosToDias(Utils::getConstante($doctrine, 'felicidadBonificacion25'));

        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $diasDifEntregasFelicidad)), 200);
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
     * @Route("/guardian/ajustes/getTestIncorrecto", name="getTestIncorrecto")
     */
    public function getTestIncorrectoAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        // Comprobamos que el usuario es admin, si no, redireccionamos a /
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/ajustes/getTestIncorrecto', true);
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso denegado')), 200);
        }
        $pago_jornada = Utils::segundosToDias(Utils::getConstante($doctrine, 'test_incorrecto'));

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
     * @Route("/guardian/ajustes/getCosteMinaPista", name="getCosteMinaPista")
     */
    public function getCosteMinaPistaAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        // Comprobamos que el usuario es admin, si no, redireccionamos a /
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/ajustes/getCosteMinaPista', true);
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso denegado')), 200);
        }
        $coste_mina_pista = Utils::segundosToDias(Utils::getConstante($doctrine, 'coste_pista'));

        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $coste_mina_pista)), 200);
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
     * @Route("/guardian/pagaExtra/getEjerciciosPaga", name="getEjerciciosPagaGuardian")
     */
    public function getEjerciciosPagaGuardianAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/pagaExtra/getEjerciciosPaga', true);
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso denegado')), 200);
        }
        $SECCION_PAGA = $doctrine->getRepository('AppBundle:EjercicioSeccion')->findOneBySeccion('paga_extra');
        if (null === $SECCION_PAGA) {
            Utils::setError($doctrine, 1, 'getEjerciciosPagaAction - No existe la seccion paga_extra');
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Error inesperado')), 200);
        }
        $EJERCICIOS = $doctrine->getRepository('AppBundle:Ejercicio')->findByIdEjercicioSeccion($SECCION_PAGA);
        if (!count($EJERCICIOS)) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No hay ejercicios en esta sección aún. Antes debes publicarlos en el apartado PROPONER')), 200);
        }
        $ENTREGADO = $doctrine->getRepository('AppBundle:EjercicioEstado')->findOneByEstado('entregado');
        if (null === $ENTREGADO) {
            Utils::setError($doctrine, 1, 'getEjerciciosPagaAction - No existe el estado entregado');
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Error inesperado')), 200);
        }
        $DATOS = [];
        foreach ($EJERCICIOS as $EJERCICIO) {
            $aux = [];
            $aux['ENUNCIADO'] = $EJERCICIO->getEnunciado();
            $aux['COSTE'] = Utils::segundosToDias($EJERCICIO->getCoste());
            $aux['ID'] = $EJERCICIO->getIdEjercicio();
            $ENTREGAS = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findBy([
                'idEjercicio' => $EJERCICIO, 'idEjercicioEstado' => $ENTREGADO
            ]);
            $aux['ENTREGAS_PENDIENTES'] = count($ENTREGAS);
            $DATOS[] = $aux;
        }

        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $DATOS)), 200);
    }

    /**
     * @Route("/guardian/alimentacion/getEjerciciosAlimentacion", name="getEjerciciosAlimentacionGuardian")
     */
    public function getEjerciciosAlimentacionGuardianAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/alimentacion/getEjerciciosAlimentacion', true);
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso denegado')), 200);
        }
        $SECCION_COMIDA = $doctrine->getRepository('AppBundle:EjercicioSeccion')->findOneBySeccion('comida');
        $SECCION_BEBIDA = $doctrine->getRepository('AppBundle:EjercicioSeccion')->findOneBySeccion('bebida');
        if (null === $SECCION_COMIDA || null === $SECCION_BEBIDA) {
            Utils::setError($doctrine, 1, 'getEjerciciosPagaAction - No existe la seccion comida o bebida');
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Error inesperado')), 200);
        }
        $EJERCICIOS = [];
        $EJERCICIOS_COMIDA = $doctrine->getRepository('AppBundle:Ejercicio')->findByIdEjercicioSeccion($SECCION_COMIDA);
        $EJERCICIOS_BEBIDA = $doctrine->getRepository('AppBundle:Ejercicio')->findByIdEjercicioSeccion($SECCION_BEBIDA);
        if (!count($EJERCICIOS_COMIDA) || !count($EJERCICIOS_BEBIDA)) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No hay ejercicios en esta sección aún. Antes debes publicarlos en el apartado PROPONER')), 200);
        }
        foreach ($EJERCICIOS_COMIDA as $E) {
            $EJERCICIOS[] = $E;
        }
        foreach ($EJERCICIOS_BEBIDA as $E) {
            $EJERCICIOS[] = $E;
        }
        $ENTREGADO = $doctrine->getRepository('AppBundle:EjercicioEstado')->findOneByEstado('entregado');
        if (null === $ENTREGADO) {
            Utils::setError($doctrine, 1, 'getEjerciciosPagaAction - No existe el estado entregado');
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Error inesperado')), 200);
        }
        $DATOS = [];
        foreach ($EJERCICIOS as $EJERCICIO) {
            $aux = [];
            $aux['DISTRITO'] = 0;
            if (Ejercicio::esEjercicioDistrito($doctrine, $EJERCICIO)) {
                $aux['DISTRITO'] = 1;
            }
            $coste = Utils::segundosToDias($EJERCICIO->getCoste());
            $aux['ENUNCIADO'] = $EJERCICIO->getEnunciado();
            $aux['COSTE'] = $coste;
            $aux['ID'] = $EJERCICIO->getIdEjercicio();
            $ENTREGAS = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findBy([
                'idEjercicio' => $EJERCICIO, 'idEjercicioEstado' => $ENTREGADO
            ]);
            $aux['ENTREGAS_PENDIENTES'] = count($ENTREGAS);
            $DATOS[] = $aux;
        }

        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $DATOS)), 200);
    }

    /**
     * @Route("/guardian/felicidad/getEjerciciosFelicidad", name="getEjerciciosFelicidadGuardian")
     */
    public function getEjerciciosFelicidadGuardianAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/felicidad/getEjerciciosFelicidad', true);
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso denegado')), 200);
        }
        $EJERCICIOS_FELICIDAD = $doctrine->getRepository('AppBundle:EjercicioFelicidad')->findAll();
        if (!count($EJERCICIOS_FELICIDAD)) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No hay entregas en Felicidad')), 200);
        }
        $EJERCICIOS = [];
        foreach ($EJERCICIOS_FELICIDAD as $F) {
            $aux['PORCENTAJE'] = $F->getPorcentaje();
            $aux['ID'] = $F->getIdEjercicioFelicidad();
            $aux['PROPUESTO'] = 0;
            $aux['ENTREGADO'] = 0;

            $CIUDADANO = $F->getIdUsuario();
            $aux['CIUDADANO'] = [];
            $aux['CIUDADANO']['NOMBRE'] = $CIUDADANO->getNombre();
            $aux['CIUDADANO']['APELLIDOS'] = $CIUDADANO->getApellidos();
            $aux['CIUDADANO']['DNI'] = $CIUDADANO->getDni();
            $aux['CIUDADANO']['ALIAS'] = $CIUDADANO->getSeudonimo();

            $PROPUESTA = $F->getIdEjercicioPropuesta();
            $ENTREGA_F = $F->getIdEjercicioEntrega();
            if (null !== $PROPUESTA) {
                $aux['PROPUESTO'] = 1;
                $aux['PROPUESTA'] = [];
                $aux['PROPUESTA']['FECHA'] = $PROPUESTA->getFecha();
                $CALIFICACION = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findOneByIdEjercicio($PROPUESTA);
                $ENTREGA = $doctrine->getRepository('AppBundle:EjercicioEntrega')->findOneByIdEjercicio($PROPUESTA);
                $aux['PROPUESTA']['NOMBRE'] = $ENTREGA->getNombre();
                $aux['PROPUESTA']['RUTA'] = $aux['CIUDADANO']['DNI'] . '/felicidad/' . $CALIFICACION->getIdEjercicioCalificacion() . '/' . $aux['PROPUESTA']['NOMBRE'];
            }
            
            if ($aux['PROPUESTO'] && null !== $ENTREGA_F) {
                $aux['ENTREGADO'] = 1;
                $aux['ENTREGA'] = [];
                $aux['ENTREGA']['FECHA'] = $ENTREGA_F->getFecha();
                $CALIFICACION = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findOneByIdEjercicio($ENTREGA_F);
                $ENTREGA = $doctrine->getRepository('AppBundle:EjercicioEntrega')->findOneByIdEjercicio($ENTREGA_F);
                $aux['ENTREGA']['NOMBRE'] = $ENTREGA->getNombre();
                $aux['ENTREGA']['RUTA'] = $aux['CIUDADANO']['DNI'] . '/felicidad/' . $CALIFICACION->getIdEjercicioCalificacion() . '/' . $aux['ENTREGA']['NOMBRE'];
            }

            if ($aux['PROPUESTO']) {
                $EJERCICIOS[] = $aux;
            }
        }
        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $EJERCICIOS)), 200);
    }

    /**
     * @Route("/guardian/pagaExtra/getEntregasPaga/{id_ejercicio}", name="getEntregasPaga")
     */
    public function getEntregasPagaAction(Request $request, $id_ejercicio) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/pagaExtra/getEntregasPaga/' . $id_ejercicio, true);
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso denegado')), 200);
        }
        $EJERCICIO = $doctrine->getRepository('AppBundle:Ejercicio')->findOneByIdEjercicio($id_ejercicio);
        if (null === $EJERCICIO) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Error inesperado')), 200);
        }
        $CALIFICACIONES = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findByIdEjercicio($EJERCICIO);
        if (!count($CALIFICACIONES)) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No hay entregas para este ejercicio.')), 200);
        }
        $DATOS = [];
        $DATOS['NOTAS'] = [];
        $NOTAS = $doctrine->getRepository('AppBundle:Calificaciones')->findAll();
        if (!count($NOTAS)) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Error inesperado')), 200);
        }
        foreach ($NOTAS as $NOTA) {
            $aux = [];
            $aux['ICONO'] = $NOTA->getCorrespondenciaIcono();
            $aux['ID'] = $NOTA->getIdCalificaciones();
            $DATOS['NOTAS'][] = $aux;
        }
        $DATOS['CALIFICACIONES'] = [];
        foreach ($CALIFICACIONES as $CALIFICACION) {
            $saltar = false;
            $aux = [];
            $aux['CIUDADANO'] = [];
            $aux['CALIFICACION'] = [];
            $aux['ENTREGA'] = [];
            $CIUDADANO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($CALIFICACION->getIdUsuario());
            $aux['CIUDADANO']['ID'] = $CIUDADANO->getIdUsuario();
            $aux['CIUDADANO']['NOMBRE'] = $CIUDADANO->getNombre();
            $aux['CIUDADANO']['APELLIDOS'] = $CIUDADANO->getApellidos();
            $aux['CIUDADANO']['ALIAS'] = $CIUDADANO->getSeudonimo();
            $aux['CIUDADANO']['DNI'] = $CIUDADANO->getDni();
            $aux['CALIFICACION']['ESTADO'] = $CALIFICACION->getIdEjercicioEstado()->getEstado();
            $aux['CALIFICACION']['CALIFICADO'] = 0;
            $aux['CALIFICACION']['FECHA'] = $CALIFICACION->getFecha();
            if (null != $CALIFICACION->getIdCalificaciones()) {
                $aux['CALIFICACION']['CALIFICADO'] = 1;
                $aux['CALIFICACION']['CALIFICACION'] = $CALIFICACION->getIdCalificaciones()->getIdCalificaciones();
                $aux['CALIFICACION']['ICONO'] = $CALIFICACION->getIdCalificaciones()->getCorrespondenciaIcono();
            }
            if ($aux['CALIFICACION']['ESTADO'] !== 'solicitado') {
                $ENTREGA = $doctrine->getRepository('AppBundle:EjercicioEntrega')->findOneBy([
                    'idUsuario' => $CALIFICACION->getIdUsuario(), 'idEjercicio' => $EJERCICIO
                ]);
                if (null === $ENTREGA) {
                    $saltar = true;
                } else {
//                    return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $EJERCICIO->getIdEjercicio())), 200);
                    $aux['ENTREGA']['NOMBRE'] = $ENTREGA->getNombre();
                    $aux['ENTREGA']['FECHA'] = $ENTREGA->getFecha();
                    $aux['ENTREGA']['ID'] = $CALIFICACION->getIdEjercicioCalificacion();
                }
            }
            if (!$saltar) {
                $DATOS['CALIFICACIONES'][] = $aux;
            }
        }

        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $DATOS)), 200);
    }

    /**
     * @Route("/guardian/alimentacion/getEntregasAlimentacion/{id_ejercicio}", name="getEntregasAlimentacion")
     */
    public function getEntregasAlimentacionAction(Request $request, $id_ejercicio) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/alimentacion/getEntregasAlimentacion/' . $id_ejercicio, true);
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso denegado')), 200);
        }
        $EJERCICIO = $doctrine->getRepository('AppBundle:Ejercicio')->findOneByIdEjercicio($id_ejercicio);
        if (null === $EJERCICIO) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Error inesperado')), 200);
        }
        $DATOS = [];
        if (Ejercicio::esEjercicioDistrito($doctrine, $EJERCICIO)) {
            $DATOS['DISTRITO'] = 1;
        } else {
            $DATOS['DISTRITO'] = 0;
        }
        $DATOS['NOTAS'] = [];
        $NOTAS = $doctrine->getRepository('AppBundle:Calificaciones')->findAll();
        if (!count($NOTAS)) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Error inesperado')), 200);
        }
        foreach ($NOTAS as $NOTA) {
            $aux = [];
            $aux['ICONO'] = $NOTA->getCorrespondenciaIcono();
            $aux['ID'] = $NOTA->getIdCalificaciones();
            $DATOS['NOTAS'][] = $aux;
        }
        $DATOS['CALIFICACIONES'] = [];
        if (!$DATOS['DISTRITO']) {
            $CALIFICACIONES = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findByIdEjercicio($EJERCICIO);
            if (!count($CALIFICACIONES)) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No hay entregas para este ejercicio.')), 200);
            }
            foreach ($CALIFICACIONES as $CALIFICACION) {
                $aux = [];
                $aux['CIUDADANO'] = [];
                $aux['CALIFICACION'] = [];
                $aux['ENTREGA'] = [];
                $CIUDADANO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($CALIFICACION->getIdUsuario());
                $aux['CIUDADANO']['ID'] = $CIUDADANO->getIdUsuario();
                $aux['CIUDADANO']['NOMBRE'] = $CIUDADANO->getNombre();
                $aux['CIUDADANO']['APELLIDOS'] = $CIUDADANO->getApellidos();
                $aux['CIUDADANO']['ALIAS'] = $CIUDADANO->getSeudonimo();
                $aux['CIUDADANO']['DNI'] = $CIUDADANO->getDni();
                $aux['CALIFICACION']['ESTADO'] = $CALIFICACION->getIdEjercicioEstado()->getEstado();
                $aux['CALIFICACION']['CALIFICADO'] = 0;
                $aux['CALIFICACION']['FECHA'] = $CALIFICACION->getFecha();
                $aux['SECCION'] = $CALIFICACION->getIdEjercicio()->getIdEjercicioSeccion()->getSeccion();
                if (null != $CALIFICACION->getIdCalificaciones()) {
                    $aux['CALIFICACION']['CALIFICADO'] = 1;
                    $aux['CALIFICACION']['CALIFICACION'] = $CALIFICACION->getIdCalificaciones()->getIdCalificaciones();
                    $aux['CALIFICACION']['ICONO'] = $CALIFICACION->getIdCalificaciones()->getCorrespondenciaIcono();
                }
                if ($aux['CALIFICACION']['ESTADO'] !== 'solicitado') {
                    $ENTREGA = $doctrine->getRepository('AppBundle:EjercicioEntrega')->findOneBy([
                        'idUsuario' => $CALIFICACION->getIdUsuario(), 'idEjercicio' => $EJERCICIO
                    ]);
                    $aux['ENTREGA']['NOMBRE'] = $ENTREGA->getNombre();
                    $aux['ENTREGA']['FECHA'] = $ENTREGA->getFecha();
                    $aux['ENTREGA']['ID'] = $CALIFICACION->getIdEjercicioCalificacion();
                }
                $DATOS['CALIFICACIONES'][] = $aux;
            }
        } else {
            $DISTRITOS = $doctrine->getRepository('AppBundle:UsuarioDistrito')->findAll();
            if (!count($DISTRITOS)) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No hay entregas para este ejercicio.')), 200);
            }
            foreach ($DISTRITOS as $DISTRITO) {
                $CIUDADANOS = $doctrine->getRepository('AppBundle:Usuario')->findByIdDistrito($DISTRITO);
                $ENTREGAS = $doctrine->getRepository('AppBundle:EjercicioEntrega')->findByIdEjercicio($EJERCICIO);
                if (count($CIUDADANOS) && count($ENTREGAS)) {
                    $CALIFICACION2 = null;
                    foreach ($ENTREGAS as $ENTREGA) {
                        $CIUDADANO = $ENTREGA->getIdUsuario();
                        if (in_array($CIUDADANO, $CIUDADANOS)) {
                            $CALIFICACION2 = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findOneBy([
                                'idEjercicio' => $EJERCICIO, 'idUsuario' => $CIUDADANO
                            ]);
                        }
                    }
                    if ($CALIFICACION2 !== null) {
                        $aux = [];
                        $aux['CIUDADANO'] = [];
                        $aux['CALIFICACION'] = [];
                        $aux['ENTREGA'] = [];
                        $CIUDADANO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($CALIFICACION2->getIdUsuario());
                        $aux['CIUDADANO']['DNI'] = $CIUDADANO->getDni();
                        $aux['DISTRITO'] = $DISTRITO->getNombre();
                        $aux['SECCION'] = $EJERCICIO->getIdEjercicioSeccion()->getSeccion();
                        $aux['CALIFICACION']['ESTADO'] = $CALIFICACION2->getIdEjercicioEstado()->getEstado();
                        $aux['CALIFICACION']['CALIFICADO'] = 0;
                        $aux['CALIFICACION']['FECHA'] = $CALIFICACION2->getFecha();
                        if (null != $CALIFICACION2->getIdCalificaciones()) {
                            $aux['CALIFICACION']['CALIFICADO'] = 1;
                            $aux['CALIFICACION']['CALIFICACION'] = $CALIFICACION2->getIdCalificaciones()->getIdCalificaciones();
                            $aux['CALIFICACION']['ICONO'] = $CALIFICACION2->getIdCalificaciones()->getCorrespondenciaIcono();
                        }
                        $ENTREGA = $doctrine->getRepository('AppBundle:EjercicioEntrega')->findOneBy([
                            'idUsuario' => $CALIFICACION2->getIdUsuario(), 'idEjercicio' => $EJERCICIO
                        ]);
                        $aux['ENTREGA']['NOMBRE'] = $ENTREGA->getNombre();
                        $aux['ENTREGA']['FECHA'] = $ENTREGA->getFecha();
                        $aux['ENTREGA']['ID'] = $CALIFICACION2->getIdEjercicioCalificacion();
                        $DATOS['CALIFICACIONES'][] = $aux;
                    }
                }
            }
            if (!count($DATOS['CALIFICACIONES'])) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No hay entregas')), 200);
            }
        }

        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $DATOS)), 200);
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
            if ($n_tweets <= 0 || $pago_jornada <= 0) {
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
            if ($num_max_solicitantes_paga <= 0) {
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
            if ($n_dias_entrega <= 0 || $tsc <= 0 || $tsb <= 0) {
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
            if ($disparador_apuesta <= 0) {
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
            if ($pago_inspeccion <= 0) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'La constante debe ser mayor que 0')), 200);
            }
            $CONSTANTE_INSP = $doctrine->getRepository('AppBundle:Constante')->findOneByClave('test_correcto');
            if ($CONSTANTE_INSP === null) {
                Utils::setError($doctrine, 1, 'setJornadaLaboralAction no existe constante test_correcto');
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Error inesperado')), 200);
            }
            $CONSTANTE_INSP->setValor($pago_inspeccion);
            $em->persist($CONSTANTE_INSP);
            
            $penalizacion_paga = $request->request->get('test_incorrecto');
            if ($penalizacion_paga <= 0) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'La constante debe ser mayor que 0')), 200);
            }
            $CONSTANTE_P = $doctrine->getRepository('AppBundle:Constante')->findOneByClave('test_incorrecto');
            if ($CONSTANTE_P === null) {
                Utils::setError($doctrine, 1, 'setJornadaLaboralAction no existe constante test_incorrecto');
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Error inesperado')), 200);
            }
            $CONSTANTE_P->setValor($penalizacion_paga);
            $em->persist($CONSTANTE_P);
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
            $coste_mina_pista = $request->request->get('coste_mina_pista');
            if ($pago_mina <= 0 || $pago_base_mina <= 0) {
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
            $CONSTANTE_COSTE_MINA_PISTA = $doctrine->getRepository('AppBundle:Constante')->findOneByClave('coste_pista');
            if ($CONSTANTE_COSTE_MINA_PISTA === null) {
                Utils::setError($doctrine, 1, 'setJornadaLaboralAction no existe constante coste_pista');
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Error inesperado')), 200);
            }
            $CONSTANTE_PAGO_MINA->setValor($pago_mina);
            $CONSTANTE_PAGO_BASE_MINA->setValor($pago_base_mina);
            $CONSTANTE_COSTE_MINA_PISTA->setValor($coste_mina_pista);
            $em->persist($CONSTANTE_PAGO_MINA);
            $em->persist($CONSTANTE_PAGO_BASE_MINA);
            $em->persist($CONSTANTE_COSTE_MINA_PISTA);
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
            if ($interes <= 0.00 || $tiempo_max <= 0) {
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

    /**
     * 
     * @Route("/guardian/setCalificacion", name="setCalificacion")
     */
    public function setCalificacionAction(Request $request) {
        if ($request->getMethod() == 'POST') {
            $doctrine = $this->getDoctrine();
            $em = $doctrine->getManager();
            $session = $request->getSession();
            $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/setCalificacion', true);
            if (!$status) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado')), 200);
            }
            $GdT = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get('id_usuario'));
            $EVALUADO = $doctrine->getRepository('AppBundle:EjercicioEstado')->findOneByEstado('evaluado');
            $idCalificacion = $request->request->get('idCalificacion');
            $CALIFICACION = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findOneByIdEjercicioCalificacion($idCalificacion);
            $idNota = $request->request->get('idNota');
            $NOTA = $doctrine->getRepository('AppBundle:Calificaciones')->findOneByIdCalificaciones($idNota);
            if (null === $EVALUADO || null === $CALIFICACION || null === $NOTA) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Error inesperado')), 200);
            }
            $EJERCICIO = $CALIFICACION->getIdEjercicio();
            $SECCION = $EJERCICIO->getIdEjercicioSeccion();
            $BONIFICACION_ANTERIOR = $doctrine->getRepository('AppBundle:EjercicioBonificacion')->findOneBy([
                'idEjercicio' => $CALIFICACION->getIdEjercicio(), 'idCalificacion' => $CALIFICACION->getIdCalificaciones()
            ]);
            $BONIFICACION = $doctrine->getRepository('AppBundle:EjercicioBonificacion')->findOneBy([
                'idEjercicio' => $CALIFICACION->getIdEjercicio(), 'idCalificacion' => $NOTA
            ]);
            if (null === $BONIFICACION) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Error inesperado')), 200);
            }
            if (null !== $BONIFICACION_ANTERIOR) {
                Usuario::operacionSobreTdV($doctrine, $CALIFICACION->getIdUsuario(), (-1) * $BONIFICACION_ANTERIOR->getBonificacion(), 'Cobro (Ajuste) - Sustitución de beneficios al calificar de nuevo el mismo ejercicio');
            }
            if (Ejercicio::esEjercicioDistrito($doctrine, $EJERCICIO)) {
                $DISTRITO = $CALIFICACION->getIdUsuario()->getIdDistrito();
                $CIUDADANOS = Distrito::getCiudadanosVivosDistrito($doctrine, $DISTRITO);
                foreach ($CIUDADANOS as $CIUDADANO) {
                    $CALIFICACION = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findOneBy(['idUsuario' => $CIUDADANO, 'idEjercicio' => $EJERCICIO]);
                    if (null !== $CALIFICACION) {
                        $CALIFICACION->setIdEvaluador($GdT);
                        $CALIFICACION->setIdEjercicioEstado($EVALUADO);
                        $CALIFICACION->setIdCalificaciones($NOTA);
                        $CALIFICACION->setFecha(new \DateTime('now'));
                        $em->persist($CALIFICACION);

                        if ($SECCION === 'comida') {
                            $TARJETA = $doctrine->getRepository('AppBundle:BonificacionExtra')->findOneByIdBonificacionExtra(2);
                            if (null !== $TARJETA) {
                                $MI_TARJETA = $doctrine->getRepository('AppBundle:BonificacionXUsuario')->findOneBy([
                                    'idBonificacionExtra' => $TARJETA, 'idUsuario' => $CIUDADANO, 'usado' => 0
                                ]);
                                if (null !== $MI_TARJETA) {
                                    Usuario::operacionSobreTdV($doctrine, $CALIFICACION->getIdUsuario(), 2 * $BONIFICACION->getBonificacion(), 'Ingreso - Corrección de ejercicio en ' . $SECCION->getSeccion() . ' por el GdT (Bonificación doble)');
                                    $MI_TARJETA->setUsado(1);
                                    $em->persist($MI_TARJETA);
                                }
                            }
                        }
                        $em->flush();
                        Usuario::operacionSobreTdV($doctrine, $CALIFICACION->getIdUsuario(), $BONIFICACION->getBonificacion(), 'Ingreso - Corrección de ejercicio en ' . $SECCION->getSeccion() . ' por el GdT');
                        Usuario::comprobarNivel($doctrine, $CIUDADANO);
                    }
                }
            } else {
                $CALIFICACION->setIdEvaluador($GdT);
                $CALIFICACION->setIdEjercicioEstado($EVALUADO);
                $CALIFICACION->setIdCalificaciones($NOTA);
                $CALIFICACION->setFecha(new \DateTime('now'));
                $em->persist($CALIFICACION);
                if ($SECCION === 'comida') {
                    $TARJETA = $doctrine->getRepository('AppBundle:BonificacionExtra')->findOneByIdBonificacionExtra(2);
                    if (null !== $TARJETA) {
                        $MI_TARJETA = $doctrine->getRepository('AppBundle:BonificacionXUsuario')->findOneBy([
                            'idBonificacionExtra' => $TARJETA, 'idUsuario' => $doctrine, $CALIFICACION->getIdUsuario(), 'usado' => 0
                        ]);
                        if (null !== $MI_TARJETA) {
                            Usuario::operacionSobreTdV($doctrine, $CALIFICACION->getIdUsuario(), 2 * $BONIFICACION->getBonificacion(), 'Ingreso - Corrección de ejercicio en ' . $SECCION->getSeccion() . ' por el GdT (Bonificación doble)');
                            $MI_TARJETA->setUsado(1);
                            $em->persist($MI_TARJETA);
                        }
                    }
                }
                $em->flush();
                Usuario::operacionSobreTdV($doctrine, $CALIFICACION->getIdUsuario(), $BONIFICACION->getBonificacion(), 'Ingreso - Corrección de ejercicio en ' . $SECCION->getSeccion() . ' por el GdT');
                Usuario::comprobarNivel($doctrine, $CALIFICACION->getIdUsuario());
            }

            return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => 'Ciudadano evaluado correctamente')), 200);
        }
        return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No se han enviado datos')), 200);
    }

    /**
     * 
     * @Route("/guardian/ajustes/setFelicidad", name="setFelicidadDias")
     */
    public function setFelicidadDiasAction(Request $request) {
        if ($request->getMethod() == 'POST') {
            $doctrine = $this->getDoctrine();
            $em = $doctrine->getManager();
            $session = $request->getSession();
            $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/ajustes/setFelicidad', true);
            if (!$status) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado')), 200);
            }
            $diasDifEntregasFelicidad = $request->request->get('dias_felicidad');
            if ($diasDifEntregasFelicidad <= 0) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'La constante debe ser mayor que 0')), 200);
            }
            $CONSTANTE = $doctrine->getRepository('AppBundle:Constante')->findOneByClave('diasDifEntregasFelicidad');
            if ($CONSTANTE === null) {
                Utils::setError($doctrine, 1, 'setFelicidadDiasAction no existe constante diasDifEntregasFelicidad');
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Error inesperado')), 200);
            }
            $CONSTANTE->setValor($diasDifEntregasFelicidad);
            $em->persist($CONSTANTE);
            
            $felicidadBonificacion5 = $request->request->get('b5');
            if ($felicidadBonificacion5 <= 0) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'La constante debe ser mayor que 0')), 200);
            }
            $CONSTANTE = $doctrine->getRepository('AppBundle:Constante')->findOneByClave('felicidadBonificacion5');
            if ($CONSTANTE === null) {
                Utils::setError($doctrine, 1, 'setFelicidadDiasAction no existe constante felicidadBonificacion5');
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Error inesperado')), 200);
            }
            $CONSTANTE->setValor($felicidadBonificacion5);
            $em->persist($CONSTANTE);
            
            $felicidadBonificacion15 = $request->request->get('b15');
            if ($felicidadBonificacion15 <= 0) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'La constante debe ser mayor que 0')), 200);
            }
            $CONSTANTE = $doctrine->getRepository('AppBundle:Constante')->findOneByClave('felicidadBonificacion15');
            if ($CONSTANTE === null) {
                Utils::setError($doctrine, 1, 'setFelicidadDiasAction no existe constante felicidadBonificacion15');
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Error inesperado')), 200);
            }
            $CONSTANTE->setValor($felicidadBonificacion15);
            $em->persist($CONSTANTE);
            
            $felicidadBonificacion25 = $request->request->get('b25');
            if ($felicidadBonificacion25 <= 0) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'La constante debe ser mayor que 0')), 200);
            }
            $CONSTANTE = $doctrine->getRepository('AppBundle:Constante')->findOneByClave('felicidadBonificacion25');
            if ($CONSTANTE === null) {
                Utils::setError($doctrine, 1, 'setFelicidadDiasAction no existe constante felicidadBonificacion25');
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Error inesperado')), 200);
            }
            $CONSTANTE->setValor($felicidadBonificacion25);
            $em->persist($CONSTANTE);
            
            $em->flush();
            return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => 'Cambios realizados correctamente')), 200);
        }
        return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No se han enviado datos')), 200);
    }

}
