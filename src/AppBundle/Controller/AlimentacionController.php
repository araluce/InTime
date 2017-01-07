<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Controller;

/**
 * Description of AlimentacionController
 *
 * @author araluce
 */
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use AppBundle\Utils\DataManager;
use AppBundle\Utils\Alimentacion;
use AppBundle\Utils\Usuario;
use AppBundle\Utils\Utils;

class AlimentacionController extends Controller {

    /**
     * @Route("/ciudadano/alimentacion", name="alimentacion")
     */
    public function alimentacion_ciudadanoAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/alimentacion');
        if (!$status) {
            return new RedirectResponse('/');
        }
        $DATOS = DataManager::setDefaultData($doctrine, 'Alimentación', $session);
        return $this->render('ciudadano/extensiones/alimentacion.html.twig', $DATOS);
    }

    /**
     * @Route("/ciudadano/alimentacion/comida", name="comida")
     */
    public function comida(Request $request, $mensaje = null) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/alimentacion/comida');
        if (!$status) {
            return new RedirectResponse('/');
        }
        $DATOS = DataManager::setDefaultData($doctrine, 'Comida', $session);
        if ($mensaje !== null) {
            $DATOS['info'] = $mensaje['info'];
        }

        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get('id_usuario'));
        Alimentacion::getDatosComida($doctrine, $USUARIO, $DATOS);
        return $this->render('ciudadano/alimentacion/alimentacion.twig', $DATOS);
    }

    /**
     * 
     * @Route("/getTiempoSinComer", name="tiempoSinComer")
     */
    public function getTiempoSinComerAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $id_usuario = $session->get('id_usuario');
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($id_usuario);
        if ($USUARIO === null) {
            return new JsonResponse(array('error' => 'El usuario no está logueado'), 200);
        }

        $tsc = $USUARIO->getTiempoSinComer();
        if ($tsc === null) {
            return new JsonResponse(array('porcentaje' => 'null'), 200);
        }
        $HOY = new \DateTime('now');
        $TSC_DEFECTO = $doctrine->getRepository('AppBundle:Constante')->findOneByClave('tiempo_acabar_de_comer');
        $respuesta = [];
        $respuesta['suelo'] = $tsc->getTimestamp();
        $respuesta['techo'] = $respuesta['suelo'] + $TSC_DEFECTO->getValor();
        $respuesta['current'] = $HOY->getTimestamp();
        $respuesta['recorrido'] = $respuesta['current'] - $respuesta['suelo'];
        $respuesta['porcentaje'] = 100 - (($respuesta['recorrido'] * 100) / $TSC_DEFECTO->getValor());

        return new JsonResponse($respuesta, 200);
    }

    /**
     * @Route("/ciudadano/alimentacion/comida/tienda", name="tiendaComida")
     */
    public function tiendaComidaAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/alimentacion/comida/tienda');
        if (!$status) {
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado'));
        }
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get('id_usuario'));

        // Obtenemos los ejercicios de comida del usuario
        $SECCION_COMIDA = $doctrine->getRepository('AppBundle:EjercicioSeccion')->findOneBySeccion('comida');
        $EJERCICIOS_COMIDA_DEL_USUARIO = $doctrine->getRepository('AppBundle:EjercicioXUsuario')->findBy([
            'idUsu' => $USUARIO, 'idSeccion' => $SECCION_COMIDA
        ]);
        $DATOS = [];
        $DATOS['NUMERO_EJERCICIOS'] = count($EJERCICIOS_COMIDA_DEL_USUARIO);

        $DATOS['EJERCICIOS'] = [];
        $EJERCICIOS_CONSUMIDOS = [];
        // Si el usuario tiene ejercicios...
        if ($DATOS['NUMERO_EJERCICIOS']) {
            $DATOS['YA_SOLICITADO'] = Alimentacion::getSolicitadosComida($doctrine, $USUARIO);
            foreach ($EJERCICIOS_COMIDA_DEL_USUARIO as $EJERCICIO_USUARIO) {
                $EJERCICIO = $EJERCICIO_USUARIO->getIdEjercicio();
                $EJERCICIOS_CONSUMIDOS[] = $EJERCICIO;
                $aux = [];
                $aux['VISTO'] = $EJERCICIO_USUARIO->getVisto();
                $aux['ID'] = $EJERCICIO->getIdEjercicio();
                $aux['ICONO'] = null;
                if ($EJERCICIO->getIcono() !== null) {
                    $aux['ICONO'] = $EJERCICIO->getIcono()->getNombreImg();
                }
                $aux['ESTADO'] = 'no_solicitado';

                $CALIFICACION = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findOneBy([
                    'idUsuario' => $USUARIO, 'idEjercicio' => $EJERCICIO
                ]);
                if ($CALIFICACION !== null) {
                    $aux['ESTADO'] = $CALIFICACION->getIdEjercicioEstado()->getEstado();
                }
                
                $DATOS['EJERCICIOS'][] = $aux;
            }
        }
        // Ejercicios no solicitados aún
        $EJERCICIOS = $doctrine->getRepository('AppBundle:Ejercicio')->findByIdEjercicioSeccion($SECCION_COMIDA);
        if (count($EJERCICIOS)) {
            foreach ($EJERCICIOS AS $E) {
                if (!in_array($E, $EJERCICIOS_CONSUMIDOS)) {
                    $aux = [];
                    $aux['VISTO'] = $EJERCICIO_USUARIO->getVisto();
                    $aux['ID'] = $EJERCICIO->getIdEjercicio();
                    $aux['ICONO'] = null;
                    if ($EJERCICIO->getIcono() !== null) {
                        $aux['ICONO'] = $EJERCICIO->getIcono()->getNombreImg();
                    }
                    $aux['ESTADO'] = 'no_solicitado';
                    $aux['ELEGIBLE'] = 1;
                    $DATOS['EJERCICIOS'][] = $aux;
                }
            }
        }
        return new JsonResponse(array('estado' => 'OK', 'message' => $DATOS));
    }

    /**
     * @Route("/ciudadano/alimentacion/comida/obtenerDetalle/{id}", name="obtenerDetalle")
     */
    public function obtenerDetalleAction(Request $request, $id) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/alimentacion/comida/tienda');
        if (!$status) {
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado'));
        }
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get('id_usuario'));
        $EJERCICIO = $doctrine->getRepository('AppBundle:Ejercicio')->findOneByIdEjercicio($id);
        if ($EJERCICIO === null) {
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'El ejercicio no existe'));
        }
        $resp = [];
        $resp['ID'] = $EJERCICIO->getIdEjercicio();
        $resp['FECHA'] = $EJERCICIO->getFecha();
        $resp['ENUNCIADO'] = $EJERCICIO->getEnunciado();
        $resp['COSTE'] = Utils::segundosToDias($EJERCICIO->getCoste());
        $resp['ICONO'] = null;
        if ($EJERCICIO->getIcono() !== null) {
            $resp['ICONO'] = $EJERCICIO->getIcono()->getNombreImg();
        }
        $CALIFICACION = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findOneBy([
            'idUsuario' => $USUARIO, 'idEjercicio' => $EJERCICIO
        ]);
        if ($CALIFICACION !== null) {
            $resp['ESTADO'] = $CALIFICACION->getIdEjercicioEstado()->getEstado();
        }
        return new JsonResponse(array('estado' => 'OK', 'message' => $resp));
    }

}
