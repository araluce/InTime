<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Utils;

use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Utils\Usuario;
use AppBundle\Utils\Utils;

/**
 * Description of Trabajo
 *
 * @author araluce
 */
class Trabajo {

    static function solicitar_paga($doctrine, $USUARIO, $EJERCICIO) {
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
            Usuario::operacionSobreTdV($doctrine, $USUARIO, (-1)*$EJERCICIO->getCoste(), 'Cobro - Compra comida');

            return new JsonResponse(array('respuesta' => 'OK', 'datos' => $resp), 200);
        } else {
            return new JsonResponse(array('respuesta' => 'ERROR', 'mensaje' => 
                'Este ejercicio ya había sido solicitado, entregado o evaluado'), 200);
        }

        return new JsonResponse(array('respuesta' => 'ERROR', 'mensaje' => 'Ejercicio solicitado correctamente'), 200);
    }
    
    /**
     * Comprueba que el usuario ha compartido el número de tweets diarios
     * 
     * @param type $doctrine
     * @param Entity:USUARIO $USUARIO
     * @return true|false
     */
    static function comprobarJornadaLaboral($doctrine, $USUARIO){
        $query = $doctrine->getRepository('AppBundle:MochilaTweets')->createQueryBuilder('a');
        $query->select('COUNT(a)');
        $query->where('DATE_DIFF(CURRENT_DATE(), a.fecha) = 0 AND a.idUsuario = :USUARIO');
        $query->setParameters(['USUARIO' => $USUARIO]);
        $N_TUITS = $query->getQuery()->getSingleScalarResult();
        $TWEETS_X_DIA = Utils::getConstante($doctrine, 'jornada_laboral_tweets');
        if($N_TUITS >= $TWEETS_X_DIA){
            return 1;
        }
        return 0;
    }

}
