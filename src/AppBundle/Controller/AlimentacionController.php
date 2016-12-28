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

class AlimentacionController extends Controller {
    
    /**
     * @Route("/ciudadano/alimentacion", name="alimentacion")
     */
    public function alimentacion_ciudadanoAction(Request $request) {
        $DataManager  = new \AppBundle\Utils\DataManager();
        $UsuarioClass = new \AppBundle\Utils\Usuario();
        $doctrine     = $this->getDoctrine();
        $session      = $request->getSession();
        $status       = $UsuarioClass->compruebaUsuario($doctrine, $session, '/ciudadano/alimentacion');
        if (!$status) {
            return new RedirectResponse('/');
        }
        $DATOS = $DataManager->setDefaultData($doctrine,'Alimentación', $session);
        return $this->render('ciudadano/extensiones/alimentacion.html.twig', $DATOS);
    }

    /**
     * @Route("/ciudadano/alimentacion/comida", name="comida")
     */
    public function comida(Request $request, $mensaje = null) {
        $DataManager  = new \AppBundle\Utils\DataManager();
        $ALIMENTACION = new \AppBundle\Utils\Alimentacion();
        $UsuarioClass = new \AppBundle\Utils\Usuario();
        $doctrine     = $this->getDoctrine();
        $session      = $request->getSession();
        $status       = $UsuarioClass->compruebaUsuario($doctrine, $session, '/ciudadano/alimentacion/comida');
        if (!$status) {
            return new RedirectResponse('/');
        }
        $DATOS = $DataManager->setDefaultData($doctrine,'Comida', $session);
        if ($mensaje !== null) {
            $DATOS['info'] = $mensaje['info'];
        }

        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get('id_usuario'));
        $ALIMENTACION->getDatosComida($doctrine, $USUARIO, $DATOS);
        return $this->render('ciudadano/alimentacion/alimentacion.twig', $DATOS);
    }
    
    /**
     * 
     * @Route("/getTiempoSinComer", name="tiempoSinComer")
     */
    public function getTiempoSinComerAction(Request $request) {
        $ALIMENTACION = new \AppBundle\Utils\Alimentacion();
        $doctrine     = $this->getDoctrine();
        $session      = $request->getSession();
        $id_usuario   = $session->get('id_usuario');
        $USUARIO      = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($id_usuario);
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
//        $respuesta['tiempo_sin_comer'] = $tsc->getTimestamp();
//        $respuesta['tiempo_solicitud'] = $HOY->getTimestamp() - $ALIMENTACION->getCalificacionSolicitado($doctrine, $USUARIO, 'comida')->getFecha()->getTimestamp();
//        $respuesta['tiempo_defecto']   = $TIEMPO_DEFECTO->getValor();
//        $respuesta['tiempo_restante']  = $respuesta['tiempo_sin_comer'] - $HOY->getTimestamp();
//        $respuesta['tiempo_consumido'] = $respuesta['tiempo_defecto'] - $respuesta['tiempo_restante'];
//        $respuesta['porcentaje']       = ($respuesta['tiempo_restante'] / $respuesta['tiempo_defecto']) * 100;
        $respuesta['suelo'] = $tsc->getTimestamp();
        $respuesta['techo'] = $respuesta['suelo'] + $TSC_DEFECTO->getValor();
        $respuesta['current'] = $HOY->getTimestamp();
        $respuesta['recorrido'] = $respuesta['current'] - $respuesta['suelo'];
        $respuesta['porcentaje'] = 100 - (($respuesta['recorrido']*100)/$TSC_DEFECTO->getValor());
        
        return new JsonResponse($respuesta, 200);
    }
}
