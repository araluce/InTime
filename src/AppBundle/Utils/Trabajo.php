<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Utils;

use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Utils\Usuario;

/**
 * Description of Trabajo
 *
 * @author araluce
 */
class Trabajo {

    static function solicitar_paga($doctrine, $USUARIO, $EJERCICIO) {
        $UTILS = new \AppBundle\Utils\Utils();
        $FECHA = new \DateTime('now');
        $ESTADO_SOLICITADO = $doctrine->getRepository('AppBundle:EjercicioEstado')->findOneByEstado('solicitado');
        $EJERCICIO_CALIFICACION = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findOneBy([
            'idUsuario' => $USUARIO, 'idEjercicio' => $EJERCICIO
        ]);
        $em = $doctrine->getManager();

        if ($EJERCICIO_CALIFICACION === null) {
            $num_solicitudes = $UTILS->comprueba_numero_solicitudes($doctrine, $EJERCICIO);
            $num_max_solicitudes = $doctrine->getRepository('AppBundle:Constante')->findOneByClave('num_max_solicitantes_paga')->getValor();
            $resp = $UTILS->setVisto($doctrine, $USUARIO, $EJERCICIO, null);
            if (intval($num_solicitudes) < intval($num_max_solicitudes)) {
                $EJERCICIO_CALIFICACION = new \AppBundle\Entity\EjercicioCalificacion();
                $EJERCICIO_CALIFICACION->setIdUsuario($USUARIO);
                $EJERCICIO_CALIFICACION->setIdEjercicio($EJERCICIO);
                $EJERCICIO_CALIFICACION->setIdEjercicioEstado($ESTADO_SOLICITADO);
                $EJERCICIO_CALIFICACION->setFecha($FECHA);
                $em->persist($EJERCICIO_CALIFICACION);
                $em->flush();
                return new JsonResponse(array('respuesta' => 'OK', 'datos' => $resp), 200);
            } else {
                $mensaje = 'OPS! Llegaste tarde<br><br> Este ejercicio está lleno';
            }
        } else {
            $mensaje = 'Este ejercicio ya había sido solicitado, entregado o evaluado por usted';
        }

        return new JsonResponse(array('respuesta' => 'ERROR', 'mensaje' => $mensaje), 200);
    }

    static function solicitar_alimentacion($doctrine, $USUARIO, $EJERCICIO, $SECCION) {
        $UTILS = new \AppBundle\Utils\Utils();
        $FECHA = new \DateTime('now');
        $ESTADO_SOLICITADO = $doctrine->getRepository('AppBundle:EjercicioEstado')->findOneByEstado('solicitado');
        $EJERCICIO_CALIFICACION = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findOneBy([
            'idUsuario' => $USUARIO, 'idEjercicio' => $EJERCICIO
        ]);
        $em = $doctrine->getManager();

        if ($EJERCICIO_CALIFICACION === null) {
            $resp = $UTILS->setVisto($doctrine, $USUARIO, $EJERCICIO, null);
            $EJERCICIO_CALIFICACION = new \AppBundle\Entity\EjercicioCalificacion();
            $EJERCICIO_CALIFICACION->setIdUsuario($USUARIO);
            $EJERCICIO_CALIFICACION->setIdEjercicio($EJERCICIO);
            $EJERCICIO_CALIFICACION->setIdEjercicioEstado($ESTADO_SOLICITADO);
            $EJERCICIO_CALIFICACION->setFecha($FECHA);
            $em->persist($EJERCICIO_CALIFICACION);
            $em->flush();

            // Módulo para establecer tiempo sin comer
//            switch ($SECCION){
//                case 'comida':
//                    $TIEMPO = 'tiempo_sin_comer';
//                    break;
//                case 'bebida':
//                    $TIEMPO = 'tiempo_sin_beber';
//                    break;
//            }
//            $TIEMPO_SIN = $doctrine->getRepository('AppBundle:Constante')->findOneByClave($TIEMPO);
            //$DATE_TIEMPO_SIN = $FECHA->getTimestamp() + $TIEMPO_SIN->getValor();
            $DATE_TIEMPO_SIN = $FECHA->getTimestamp();
            $DATE = date('Y-m-d H:i:s', intval($DATE_TIEMPO_SIN));
            
            if($SECCION === 'comida'){
                $USUARIO->setTiempoSinComer(\DateTime::createFromFormat('Y-m-d H:i:s', $DATE));
            }
            if($SECCION === 'bebida'){
                $USUARIO->setTiempoSinBeber(\DateTime::createFromFormat('Y-m-d H:i:s', $DATE));
            }
            $em->persist($USUARIO);
            $em->flush();
            Usuario::operacionSobreTdV($doctrine, $USUARIO, (-1)*$EJERCICIO->getCoste(), 'Cobro - Compra comida');

            return new JsonResponse(array('respuesta' => 'OK', 'datos' => $resp), 200);
        } else {
            return new JsonResponse(array('respuesta' => 'ERROR', 'mensaje' => 
                'Este ejercicio ya había sido solicitado, entregado o evaluado'), 200);
        }

        return new JsonResponse(array('respuesta' => 'ERROR', 'mensaje' => 'Ejercicio solicitado correctamente'), 200);
    }

}
