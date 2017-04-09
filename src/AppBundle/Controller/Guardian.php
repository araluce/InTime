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
     * @Route("/guardian/ajustes/getFelicidadBonificacion10", name="getFelicidadBonificacion10")
     */
    public function getFelicidadBonificacion10Action(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        // Comprobamos que el usuario es admin, si no, redireccionamos a /
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/ajustes/getFelicidadBonificacion10', true);
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso denegado')), 200);
        }
        $diasDifEntregasFelicidad = Utils::segundosToDias(Utils::getConstante($doctrine, 'felicidadBonificacion10'));

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
     * @Route("/guardian/ajustes/getFelicidadBonificacion20", name="getFelicidadBonificacion20")
     */
    public function getFelicidadBonificacion20Action(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        // Comprobamos que el usuario es admin, si no, redireccionamos a /
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/ajustes/getFelicidadBonificacion20', true);
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso denegado')), 200);
        }
        $diasDifEntregasFelicidad = Utils::segundosToDias(Utils::getConstante($doctrine, 'felicidadBonificacion20'));

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
        $DATOS['DISTRITO'] = 0;
        if (Ejercicio::esEjercicioDistrito($doctrine, $EJERCICIO)) {
            $DATOS['DISTRITO'] = 1;
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
//                    $ENTREGA = $doctrine->getRepository('AppBundle:EjercicioEntrega')->findOneBy([
//                        'idUsuario' => $CALIFICACION->getIdUsuario(), 'idEjercicio' => $EJERCICIO
//                    ]);
                    $query = $doctrine->getManager()->createQueryBuilder('s')
                            ->addSelect('s')
                            ->from("\AppBundle\Entity\EjercicioEntrega", 's')
                            ->where("s.idUsuario = :USUARIO AND s.idEjercicio = :EJERCICIO")
                            ->setParameters(['USUARIO' => $CIUDADANO, 'EJERCICIO' => $EJERCICIO])
                            ->orderBy('s.fecha', 'DESC');
                    ;
                    $ENTREGA = $query->getQuery()->getResult();
                    $aux['ENTREGA']['NOMBRE'] = $ENTREGA[0]->getNombre();
                    $aux['ENTREGA']['FECHA'] = $ENTREGA[0]->getFecha();
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
//                $ENTREGAS = $doctrine->getRepository('AppBundle:EjercicioEntrega')->findByIdEjercicio($EJERCICIO);
                $query = $doctrine->getManager()->createQueryBuilder('s')
                        ->addSelect('s')
                        ->from("\AppBundle\Entity\EjercicioEntrega", 's')
                        ->where("s.idUsuario IN (:USUARIOS) AND s.idEjercicio = :EJERCICIO")
                        ->setParameters(['USUARIOS' => array_values($CIUDADANOS), 'EJERCICIO' => $EJERCICIO])
                        ->orderBy('s.fecha', 'DESC');
                ;
                $ENTREGAS = $query->getQuery()->getResult();
                if (count($CIUDADANOS) && count($ENTREGAS)) {
//                    $CALIFICACION2 = null;
//                    foreach ($ENTREGAS as $ENTREGA) {
//                        $CIUDADANO = $ENTREGA->getIdUsuario();
//                        if (in_array($CIUDADANO, $CIUDADANOS)) {
//                            $CALIFICACION2 = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findOneBy([
//                                'idEjercicio' => $EJERCICIO, 'idUsuario' => $CIUDADANO
//                            ]);
//                        }
//                    }
                    $CALIFICACION2 = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findOneBy([
                        'idEjercicio' => $EJERCICIO, 'idUsuario' => $ENTREGAS[0]->getIdUsuario()
                    ]);
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
//                        $ENTREGA = $doctrine->getRepository('AppBundle:EjercicioEntrega')->findOneBy([
//                            'idUsuario' => $CALIFICACION2->getIdUsuario(), 'idEjercicio' => $EJERCICIO
//                        ]);
                        $query = $doctrine->getManager()->createQueryBuilder('s')
                                ->addSelect('s')
                                ->from("\AppBundle\Entity\EjercicioEntrega", 's')
                                ->where("s.idUsuario = :USUARIO AND s.idEjercicio = :EJERCICIO")
                                ->setParameters(['USUARIO' => $CALIFICACION2->getIdUsuario(), 'EJERCICIO' => $EJERCICIO])
                                ->orderBy('s.fecha', 'DESC');
                        ;
                        $ENTREGA = $query->getQuery()->getResult();
                        $aux['ENTREGA']['NOMBRE'] = $ENTREGA[0]->getNombre();
                        $aux['ENTREGA']['FECHA'] = $ENTREGA[0]->getFecha();
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
     * @Route("/guardian/setCalificacion", name="setCalificacionGeneral")
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
            $CALIFICACION_MEDIA = $doctrine->getRepository('AppBundle:Calificaciones')->findOneByIdCalificaciones(5);
            $BONIFICACION_MEDIA = $doctrine->getRepository('AppBundle:EjercicioBonificacion')->findOneBy([
                'idEjercicio' => $EJERCICIO, 'idCalificacion' => $CALIFICACION_MEDIA
            ]);
            $BONIFICACION_ANTERIOR = $doctrine->getRepository('AppBundle:EjercicioBonificacion')->findOneBy([
                'idEjercicio' => $CALIFICACION->getIdEjercicio(), 'idCalificacion' => $CALIFICACION->getIdCalificaciones()
            ]);
            $BONIFICACION = $doctrine->getRepository('AppBundle:EjercicioBonificacion')->findOneBy([
                'idEjercicio' => $CALIFICACION->getIdEjercicio(), 'idCalificacion' => $NOTA
            ]);
            if (null === $BONIFICACION) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Error inesperado')), 200);
            }

            if (Ejercicio::esEjercicioDistrito($doctrine, $EJERCICIO)) {
                $DISTRITO = $CALIFICACION->getIdUsuario()->getIdDistrito();
                $CIUDADANOS = Distrito::getCiudadanosVivosDistrito($doctrine, $DISTRITO);
                foreach ($CIUDADANOS as $CIUDADANO) {
//                    if (null !== $EJERCICIO_CALIFICACION) {
//                        // Si este ejercicio ya había sido calificacdo, se resta el TdV anterior
//                        // y se le asigna el TdV por defecto hasta que el GdT lo califique
//                        if ($EJERCICIO_CALIFICACION->getIdCalificaciones() !== null) {
//                            $BONIFICACION = $doctrine->getRepository('AppBundle:EjercicioBonificacion')->findOneBy([
//                                'idEjercicio' => $id_ejercicio, 'idCalificacion' => $EJERCICIO_CALIFICACION->getIdCalificaciones()
//                            ]);
//                            if (null !== $BONIFICACION) {
//                                $tdv = (-1) * $BONIFICACION->getBonificacion();
//                                Usuario::operacionSobreTdV($doctrine, $id_usuario, $tdv, 'Cobro - Se descuenta la bonificación por nota para ingresar la nueva (id: ' . $id_ejercicio->getIdEjercicio() . ')');
//                            }
//                        }
//                    }
                    Usuario::operacionSobreTdV($doctrine, $CIUDADANO, (-1) * $BONIFICACION_MEDIA->getBonificacion(), 'Cobro (Ajuste) - Se descuenta el pago temporal (id: ' . $EJERCICIO->getIdEjercicio() . ')');
                    if (null !== $BONIFICACION_ANTERIOR) {
                        Usuario::operacionSobreTdV($doctrine, $CIUDADANO, (-1) * $BONIFICACION_ANTERIOR->getBonificacion(), 'Cobro (Ajuste) - Sustitución de beneficios al calificar de nuevo el mismo reto (id: ' . $EJERCICIO->getIdEjercicio() . ')');
                    }
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
                                    Usuario::operacionSobreTdV($doctrine, $CALIFICACION->getIdUsuario(), (2) * $BONIFICACION->getBonificacion(), 'Ingreso - Corrección de reto en ' . $SECCION->getSeccion() . ' por el GdT (Bonificación doble)(id: ' . $EJERCICIO->getIdEjercicio() . ')');
                                    $MI_TARJETA->setUsado(1);
                                    $em->persist($MI_TARJETA);
                                }
                            }
                        }
                        $em->flush();
                        Usuario::operacionSobreTdV($doctrine, $CALIFICACION->getIdUsuario(), $BONIFICACION->getBonificacion(), 'Ingreso - Corrección de reto en ' . $SECCION->getSeccion() . ' por el GdT (id: ' . $EJERCICIO->getIdEjercicio() . ')');
                    }
                }
            } else {
                Usuario::operacionSobreTdV($doctrine, $CALIFICACION->getIdUsuario(), (-1) * $BONIFICACION_MEDIA->getBonificacion(), 'Cobro (Ajuste) - Se descuenta el pago temporal (id: ' . $EJERCICIO->getIdEjercicio() . ')');
                if (null !== $BONIFICACION_ANTERIOR) {
                    Usuario::operacionSobreTdV($doctrine, $CALIFICACION->getIdUsuario(), (-1) * $BONIFICACION_ANTERIOR->getBonificacion(), 'Cobro (Ajuste) - Sustitución de beneficios al calificar de nuevo el mismo reto (id: ' . $EJERCICIO->getIdEjercicio() . ')');
                }
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
                            Usuario::operacionSobreTdV($doctrine, $CALIFICACION->getIdUsuario(), 2 * $BONIFICACION->getBonificacion(), 'Ingreso - Corrección de ejercicio en ' . $SECCION->getSeccion() . ' por el GdT (Bonificación doble)(id: ' . $EJERCICIO->getIdEjercicio() . ')');
                            $MI_TARJETA->setUsado(1);
                            $em->persist($MI_TARJETA);
                        }
                    }
                }
                $em->flush();
                Usuario::operacionSobreTdV($doctrine, $CALIFICACION->getIdUsuario(), $BONIFICACION->getBonificacion(), 'Ingreso - Corrección de reto en ' . $SECCION->getSeccion() . ' por el GdT (id: ' . $EJERCICIO->getIdEjercicio() . ')');
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

            $felicidadBonificacion10 = $request->request->get('b10');
            if ($felicidadBonificacion10 <= 0) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'La constante debe ser mayor que 0')), 200);
            }
            $CONSTANTE = $doctrine->getRepository('AppBundle:Constante')->findOneByClave('felicidadBonificacion10');
            if ($CONSTANTE === null) {
                Utils::setError($doctrine, 1, 'setFelicidadDiasAction no existe constante felicidadBonificacion10');
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Error inesperado')), 200);
            }
            $CONSTANTE->setValor($felicidadBonificacion10);
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

            $felicidadBonificacion20 = $request->request->get('b20');
            if ($felicidadBonificacion20 <= 0) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'La constante debe ser mayor que 0')), 200);
            }
            $CONSTANTE = $doctrine->getRepository('AppBundle:Constante')->findOneByClave('felicidadBonificacion20');
            if ($CONSTANTE === null) {
                Utils::setError($doctrine, 1, 'setFelicidadDiasAction no existe constante felicidadBonificacion20');
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Error inesperado')), 200);
            }
            $CONSTANTE->setValor($felicidadBonificacion20);
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

    /**
     * @Route("/guardian/directorio/getInfoBasica/{dni}", name="getInfoBasicaGuardian")
     */
    public function getInfoBasicaGuardianAction(Request $request, $dni) {
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $qb = $em->createQueryBuilder();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/directorio/getInfoBasica/' . $dni, true);
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado')), 200);
        }
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByDni($dni);
        if (null === $USUARIO) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No existe el ciudadano')), 200);
        }

        $DATOS = [];
        $ROL = $doctrine->getRepository('AppBundle:Rol')->findOneByNombre('Jugador');
        $query = $qb->select('u')
                ->from('\AppBundle\Entity\Usuario', 'u')
                ->where('u.idUsuario != :ID_USUARIO AND u.idRol = :ROL')
                ->setParameters(['ID_USUARIO' => $USUARIO->getIdUsuario(), 'ROL' => $ROL]);
        $USUARIOS = $query->getQuery()->getResult();
        $infoPuesto = Usuario::getClasificacion($doctrine, $USUARIO, $USUARIOS);

        $DATOS['DONACIONES'] = [];
        $DATOS['CANTIDADES'] = [];
        if (count($USUARIOS)) {
            foreach ($USUARIOS as $U) {
                $cantidad = Usuario::heDonadoYa($doctrine, $USUARIO, $U);
                if ($cantidad < 0) {
                    $DATOS['HE_DONADO'] = 1;
                    $aux = [];
                    $aux['CIUDADANO'] = $U->getSeudonimo();
                    $aux['CANTIDAD'] = Utils::segundosToDias((-1) * $cantidad);
                    $DATOS['DONACIONES'][] = $aux;
                }
            }
        }

        $DATOS['PUESTO'] = $infoPuesto['PUESTO'];
        $DATOS['NIVEL'] = 0;
        $DATOS['PUNTOS'] = 0;
        $USUARIO_NIVEL = $doctrine->getRepository('AppBundle:UsuarioNivel')->findOneByIdUsuario($USUARIO);
        if (null !== $USUARIO_NIVEL) {
            $DATOS['NIVEL'] = $USUARIO_NIVEL->getNivel();
            $DATOS['PUNTOS'] = $USUARIO_NIVEL->getPuntos();
        }
        if (null !== $USUARIO->getIdDistrito()) {
            $DATOS['DISTRITO'] = $USUARIO->getIdDistrito()->getNombre();
        } else {
            $DATOS['DISTRITO'] = 'Aún no tiene un distrito asignado';
        }
        $DATOS['ALIAS'] = 'Sin alias';
        if ($USUARIO->getSeudonimo()) {
            $DATOS['ALIAS'] = $USUARIO->getSeudonimo();
        }
        $DATOS['NOMBRE'] = 'Sin nombre';
        if ($USUARIO->getNombre()) {
            $DATOS['NOMBRE'] = $USUARIO->getNombre();
        }
        $DATOS['APELLIDOS'] = 'Sin apellidos';
        if ($USUARIO->getApellidos()) {
            $DATOS['APELLIDOS'] = $USUARIO->getApellidos();
        }
        $DATOS['EMAIL'] = 0;
        if ($USUARIO->getEmail()) {
            $DATOS['EMAIL'] = $USUARIO->getEmail();
        }
        $DATOS['IMAGEN'] = 0;
        if ($USUARIO->getImagen()) {
            $DATOS['IMAGEN'] = $USUARIO->getDni() . '/' . $USUARIO->getImagen();
        }
        $DATOS['FECHA_NACIMIENTO'] = 0;
        if ($USUARIO->getFechaNacimiento()) {
            $fecha = $USUARIO->getFechaNacimiento();
            $DATOS['FECHA_NACIMIENTO'] = $fecha->format('Y-m-d');
        }
        $AHORA = new \DateTime('now');
        $TDV = $USUARIO->getIdCuenta()->getTdv();
        $RESTANTE = $TDV->getTimestamp() - $AHORA->getTimestamp();
        $DATOS['TDV'] = Utils::segundosToDias($RESTANTE);

        $MC_LIBRE_MINUTEROS = $doctrine->getRepository('AppBundle:BonificacionExtra')->findOneByIdBonificacionExtra(3);
        $MC_VACACIONES = $doctrine->getRepository('AppBundle:BonificacionExtra')->findOneByIdBonificacionExtra(9);
        $MIS_MC = $doctrine->getRepository('AppBundle:BonificacionXUsuario')->findBy([
            'idUsuario' => $USUARIO
        ]);
        $DATOS['LIBRE_MINUTEROS'] = 0;
        $DATOS['TENGO_CARTAS'] = 0;
        if (count($MIS_MC)) {
            $DATOS['TENGO_CARTAS'] = 1;
            $DATOS['CARTAS'] = [];
            foreach ($MIS_MC as $MI_MC) {
                $aux = [];
                $aux['CARTA'] = $MI_MC->getIdBonificacionExtra()->getBonificacion();
                $aux['VECES'] = ($MI_MC->getContador()) + 1;
                $aux['DISPONIBLE'] = 0;
                if ($MI_MC->getIdBonificacionExtra() === $MC_LIBRE_MINUTEROS) {
                    if ($AHORA->format('W') === $MI_MC->getFecha()->format('W') && !$MI_MC->getUsado()) {
                        $DATOS['LIBRE_MINUTEROS'] = 1;
                        $aux['DISPONIBLE'] = 1;
                    }
                } elseif ($MI_MC->getIdBonificacionExtra() === $MC_VACACIONES) {
                    if ($AHORA->format('W') === $MI_MC->getFecha()->format('W') && !$MI_MC->getUsado()) {
                        $aux['DISPONIBLE'] = 1;
                    }
                } elseif (!$MI_MC->getUsado()) {
                    $aux['DISPONIBLE'] = 1;
                }
                $DATOS['CARTAS'][] = $aux;
            }
        }
        $query = $em->createQueryBuilder()->select('d')
                ->from('\AppBundle\Entity\UsuarioPrestamo', 'd')
                ->where('d.idUsuario = :Usuario AND d.motivo = :motivo AND d.restante !=0')
                ->setParameters(['Usuario' => $USUARIO, 'motivo' => 'prestamo']);
        $MIS_DEUDAS = $query->getQuery()->getResult();
        $DATOS['TENGO_DEUDAS'] = 0;
        $DATOS['DEUDAS'] = [];
        if (count($MIS_DEUDAS)) {
            $DATOS['TENGO_DEUDAS'] = 1;
            foreach ($MIS_DEUDAS as $DEUDA) {
                $aux = [];
                $aux['INTERES'] = $DEUDA->getInteres();
                $aux['SOLICITADO'] = Utils::segundosToDias($DEUDA->getCantidad());
                $aux['RESTANTE'] = Utils::segundosToDias($DEUDA->getRestante());
                $DATOS['DEUDAS'][] = $aux;
            }
        }
        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $DATOS)), 200);
    }

    /**
     * @Route("/guardian/directorio/getDirectorio/{dni}", name="directorioGuardian")
     */
    public function directorioGuardianAction(Request $request, $dni) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/directorio/' . $dni);
        if (!$status) {
            return new RedirectResponse('/');
        }
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByDni($dni);
        if (null === $USUARIO) {
            return new RedirectResponse('/');
        }

        $DATOS = [];
        $DATOS['TITULO'] = 'Informacion';
        $DATOS['DNI'] = $USUARIO->getDni();
        return $this->render('guardian/directorio.twig', $DATOS);
    }

    /**
     * @Route("/guardian/directorio/modificarTdV", name="modificarTdVGuardian")
     */
    public function modificarTdVAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/directorio/modificarTdV', true);
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado')), 200);
        }
        if ($request->getMethod() == 'POST') {
            $tiempo = $request->request->get('tdv');
            $dni = $request->request->get('dni');
            $causa = $request->request->get('motivo');
            $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByDni($dni);
            if (null === $USUARIO) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No existe el ciudadano')), 200);
            }
            if ($tiempo < 0) {
                $causa = 'Cobro - ' . $causa;
            } else {
                $causa = 'Ingreso - ' . $causa;
            }
            Usuario::operacionSobreTdV($doctrine, $USUARIO, $tiempo, $causa);
            return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => 'TdV modificado')), 200);
        }
        return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No se ha enviado ningún dato')), 200);
    }

    /**
     * 
     * @Route("/guardian/info/actualizarMovimientos/{dni}", name="actualizarMovimientoGuardian")
     */
    public function actualizarMovimientoAction(Request $request, $dni) {
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $qb = $em->createQueryBuilder();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/info/actualizarMovimientos/' . $dni, true);
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado')));
        }
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByDni($dni);
        $query = $qb->select('f')
                ->from('\AppBundle\Entity\UsuarioMovimiento', 'f')
                ->where('f.idUsuario = :ID_USUARIO')
                ->orderBy('f.fecha', 'DESC')
                ->setParameters(['ID_USUARIO' => $USUARIO->getIdUsuario()]);
        $MOVIMIENTOS = $query->getQuery()->getResult();
        if (!count($MOVIMIENTOS)) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Aún no se han producido movimientos')));
        }
        $DATOS['MOVIMIENTOS'] = [];
        foreach ($MOVIMIENTOS as $MOVIMIENTO) {
            $aux = [];
            $aux['ID'] = $MOVIMIENTO->getIdUsuarioMovimiento();
            $aux['CANTIDAD'] = $MOVIMIENTO->getCantidad();
            if ($aux['CANTIDAD'] < 0) {
                $aux['VALANCE'] = 'NEG';
            } else {
                $aux['VALANCE'] = 'POS';
            }
            $aux['CANTIDAD'] = Utils::segundosToDias($aux['CANTIDAD']);
            $aux['CONCEPTO'] = $MOVIMIENTO->getCausa();
            $aux['FECHA'] = $MOVIMIENTO->getFecha();
            $DATOS['MOVIMIENTOS'][] = $aux;
        }
        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $DATOS['MOVIMIENTOS'])));
    }

    /**
     * @Route("/guardian/info/actualizarEntregasAlimentacion/{dni}", name="actualizarEntregasAlimentacion")
     */
    public function actualizarEntregasAlimentacionAction(Request $request, $dni) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        // Comprobamos que el usuario es admin, si no, redireccionamos a /
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/ajustes/actualizarEntregasAlimentacion', true);
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso denegado')), 200);
        }
        $DATOS = [];
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByDni($dni);
        if (null === $USUARIO) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No existe un usuario con este dni')), 200);
        }
        $ENTREGAS = $doctrine->getRepository('AppBundle:EjercicioEntrega')->findByIdUsuario($USUARIO);
        if (!count($ENTREGAS)) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Este usuario no ha realizado ninguna entrega')), 200);
        }
        foreach ($ENTREGAS as $ENTREGA) {
            $aux = [];
            $EJERCICIO = $ENTREGA->getIdEjercicio();
            $aux['SECCION'] = $EJERCICIO->getIdEjercicioSeccion()->getSeccion();
            if ($aux['SECCION'] === 'comida' || $aux['SECCION'] === 'bebida') {
                $CALIFICACION = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findOneBy([
                    'idEjercicio' => $EJERCICIO, 'idUsuario' => $USUARIO
                ]);
                $ejercicioDistrito = $doctrine->getRepository('AppBundle:EjercicioDistrito')->findOneByIdEjercicio($EJERCICIO);
                $aux['DISTRITO'] = 1;
                if (null === $ejercicioDistrito) {
                    $aux['DISTRITO'] = 0;
                }
                $aux['ENUNCIADO'] = Utils::recortar_texto($EJERCICIO->getEnunciado());
                $aux['FECHA'] = $ENTREGA->getFecha()->format('h:m:s d/m/Y');
                $aux['NOMBRE'] = $ENTREGA->getNombre();
                $aux['RUTA'] = $USUARIO->getDni() . '/' . $aux['SECCION'] . '/' . $CALIFICACION->getIdEjercicioCalificacion() . '/' . $aux['NOMBRE'];
                $aux['CALIFICADO'] = 0;
                if (null !== $CALIFICACION->getIdCalificaciones()) {
                    $aux['CALIFICADO'] = 1;
                    $aux['CALIFICACION'] = $CALIFICACION->getIdCalificaciones()->getCorrespondenciaIcono();
                }
                $DATOS[] = $aux;
            }
        }

        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $DATOS)), 200);
    }

    /**
     * @Route("/guardian/info/actualizarEntregasPaga/{dni}", name="actualizarEntregasPaga")
     */
    public function actualizarEntregasPagaAction(Request $request, $dni) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        // Comprobamos que el usuario es admin, si no, redireccionamos a /
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/ajustes/actualizarEntregasPaga', true);
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso denegado')), 200);
        }
        $DATOS = [];
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByDni($dni);
        if (null === $USUARIO) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No existe un usuario con este dni')), 200);
        }
        $ENTREGAS = $doctrine->getRepository('AppBundle:EjercicioEntrega')->findByIdUsuario($USUARIO);
        if (!count($ENTREGAS)) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Este usuario no ha realizado ninguna entrega')), 200);
        }
        foreach ($ENTREGAS as $ENTREGA) {
            $aux = [];
            $EJERCICIO = $ENTREGA->getIdEjercicio();
            $aux['SECCION'] = $EJERCICIO->getIdEjercicioSeccion()->getSeccion();
            if ($aux['SECCION'] === 'paga_extra') {
                $CALIFICACION = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findOneBy([
                    'idEjercicio' => $EJERCICIO, 'idUsuario' => $USUARIO
                ]);
                $aux['ENUNCIADO'] = Utils::recortar_texto($EJERCICIO->getEnunciado());
                $aux['FECHA'] = $ENTREGA->getFecha()->format('h:m:s d/m/Y');
                $aux['NOMBRE'] = $ENTREGA->getNombre();
                $aux['RUTA'] = $USUARIO->getDni() . '/' . $aux['SECCION'] . '/' . $CALIFICACION->getIdEjercicioCalificacion() . '/' . $aux['NOMBRE'];
                $DATOS[] = $aux;
            }
        }

        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $DATOS)), 200);
    }

    /**
     * @Route("/guardian/info/actualizarEntregasFelicidad/{dni}", name="actualizarEntregasFelicidad")
     */
    public function actualizarEntregasFelicidadAction(Request $request, $dni) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        // Comprobamos que el usuario es admin, si no, redireccionamos a /
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/ajustes/actualizarEntregasFelicidad', true);
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso denegado')), 200);
        }
        $DATOS = [];
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByDni($dni);
        if (null === $USUARIO) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No existe un usuario con este dni')), 200);
        }
        $ENTREGAS = $doctrine->getRepository('AppBundle:EjercicioEntrega')->findByIdUsuario($USUARIO);
        if (!count($ENTREGAS)) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Este usuario no ha realizado ninguna entrega')), 200);
        }
        foreach ($ENTREGAS as $ENTREGA) {
            $aux = [];
            $EJERCICIO = $ENTREGA->getIdEjercicio();
            $aux['SECCION'] = $EJERCICIO->getIdEjercicioSeccion()->getSeccion();
            if ($aux['SECCION'] === 'felicidad') {
                $CALIFICACION = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findOneBy([
                    'idEjercicio' => $EJERCICIO, 'idUsuario' => $USUARIO
                ]);
                $FELICIDAD_PROPUESTA = $doctrine->getRepository('AppBundle:EjercicioFelicidad')->findOneByIdEjercicioPropuesta($EJERCICIO);
                if (null !== $FELICIDAD_PROPUESTA) {
                    $aux['TIPO'] = "Propuesta";
                    $aux['ENUNCIADO'] = Utils::recortar_texto($FELICIDAD_PROPUESTA->getEnunciado());
                    $aux['FASE'] = $FELICIDAD_PROPUESTA->getFase();
                    $aux['PORCENTAJE'] = $FELICIDAD_PROPUESTA->getPorcentaje();
                }
                $FELICIDAD_ENTREGA = $doctrine->getRepository('AppBundle:EjercicioFelicidad')->findOneByIdEjercicioEntrega($EJERCICIO);
                if (null !== $FELICIDAD_ENTREGA) {
                    $aux['TIPO'] = "Evidencia";
                    $aux['ENUNCIADO'] = Utils::recortar_texto($FELICIDAD_ENTREGA->getEnunciado());
                    $aux['FASE'] = $FELICIDAD_ENTREGA->getFase();
                    $aux['PORCENTAJE'] = $FELICIDAD_ENTREGA->getPorcentaje();
                }
                $aux['FECHA'] = $ENTREGA->getFecha()->format('h:m:s d/m/Y');
                $aux['NOMBRE'] = $ENTREGA->getNombre();
                $aux['RUTA'] = $USUARIO->getDni() . '/' . $aux['SECCION'] . '/' . $CALIFICACION->getIdEjercicioCalificacion() . '/' . $aux['NOMBRE'];
                $DATOS[] = $aux;
            }
        }

        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $DATOS)), 200);
    }

    /**
     * @Route("/guardian/vacacionesParaTodos", name="vacacionesParaTodos")
     */
    public function vacacionesParaTodosAction(Request $request) {
        if ($request->getMethod() == 'POST') {
            $doctrine = $this->getDoctrine();
            $em = $doctrine->getManager();
            $session = $request->getSession();
            // Comprobamos que el usuario es admin, si no, redireccionamos a /
            $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/ajustes/vacacionesParaTodos', true);
            if (!$status) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso denegado')), 200);
            }
            $tiempo = $request->request->get('tiempo');
            if($tiempo <= 0){
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Debes asignar un tiempo de vacaciones')), 200);
            }
            $CIUDADANOS = Usuario::getCiudadanosVivos($doctrine);
            if (!count($CIUDADANOS)) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No hay ciudadanos vivos')), 200);
            }
            $ESTADO_VACACIONES = $doctrine->getRepository('AppBundle:UsuarioEstado')->findOneByNombre('Vacaciones');
            $hoy = new \DateTime('now');
            $finBloqueoTimestamp = new \DateTime('now');
            $finBloqueoTimestamp = $finBloqueoTimestamp->getTimestamp() + $tiempo;
            $finBloqueo = new \DateTime();
            foreach ($CIUDADANOS as $CIUDADANO) {
                // Ponemos la fecha del fin de vacaciones y el tiempo que se mostrará
                $CUENTA = $CIUDADANO->getIdCuenta();
                $CUENTA->setFinbloqueo($finBloqueo->setTimestamp($finBloqueoTimestamp));
                $tdvVacaciones = intval($hoy->getTimestamp()) - intval($CUENTA->getTdv()->getTimestamp());
                $CUENTA->setTdvVacaciones($tdvVacaciones);

                // Sumamos el tdv correspondiente a las vacaciones a su cuenta
                $TDV_USUARIO = $CIUDADANO->getIdCuenta()->getTdv()->getTimestamp();
                $TDV_RESTANTE = $TDV_USUARIO + $tiempo;
                $TDV_RESTANTE_DATE = date('Y-m-d H:i:s', intval($TDV_RESTANTE));
                $TDV_RESTANTE_DATETIME = \DateTime::createFromFormat('Y-m-d H:i:s', $TDV_RESTANTE_DATE);
                $CUENTA->setTdv($TDV_RESTANTE_DATETIME);
                $em->persist($CUENTA);

                // Le damos de comer y beber
                $CIUDADANO->setIdEstado($ESTADO_VACACIONES);
                $CIUDADANO->setTiempoSinComer($hoy);
                $CIUDADANO->setTiempoSinBeber($hoy);
                $em->persist($CIUDADANO);
                
                $em->flush();
                
            }

            return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => 'Todos los ciudadanos están de vacaciones')), 200);
        }
        return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No se han enviado datos')), 200);
    }
    
    /**
     * @Route("/guardian/tdvParaTodos", name="tdvParaTodos")
     */
    public function tdvParaTodosAction(Request $request) {
        if ($request->getMethod() == 'POST') {
            $doctrine = $this->getDoctrine();
            $em = $doctrine->getManager();
            $session = $request->getSession();
            // Comprobamos que el usuario es admin, si no, redireccionamos a /
            $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/ajustes/tdvParaTodos', true);
            if (!$status) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso denegado')), 200);
            }
            $tiempo = $request->request->get('tiempo');
            if($tiempo === 0){
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Debes asignar un tdv')), 200);
            }
            $concepto = $request->request->get('motivo');
            $CIUDADANOS = Usuario::getCiudadanosVivos($doctrine);
            if (!count($CIUDADANOS)) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No hay ciudadanos vivos')), 200);
            }
            foreach($CIUDADANOS as $CIUDADANO){
                Usuario::operacionSobreTdV($doctrine, $CIUDADANO, $tiempo, $concepto);
            }
            return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => 'Operación realizada con éxito')), 200);
        }
        return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No se han enviado datos')), 200);
    }

}
