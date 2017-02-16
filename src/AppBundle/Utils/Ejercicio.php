<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Utils;

/**
 * Description of Ejercicio
 *
 * @author araluce
 */
class Ejercicio {

    /**
     * Actualiza la lista de ejercicios del usuario
     * 
     * @param type $doctrine
     * @param Entity:USUARIO $USUARIO
     */
    static function actualizarEjercicioXUsuario($doctrine, $USUARIO) {
        $em = $doctrine->getManager();
        $EJERCICIOS_USUARIO = $doctrine->getRepository('AppBundle:EjercicioXUsuario')->findByIdUsu($USUARIO);
        $EJERCICIOS_ACTUALES = [];
        if (count($EJERCICIOS_USUARIO)) {
            foreach($EJERCICIOS_USUARIO as $EJERCICIO) {
                $EJERCICIOS_ACTUALES[] = $EJERCICIO->getIdEjercicio();
            }
        }
        $EJERCICIOS = $doctrine->getRepository('AppBundle:Ejercicio')->findAll();
        if (count($EJERCICIOS)) {
            foreach ($EJERCICIOS as $EJERCICIO) {
                if (!in_array($EJERCICIO, $EJERCICIOS_ACTUALES)) {
                    $EJERCICIO_X_USUARIO = new \AppBundle\Entity\EjercicioXUsuario();
                    $EJERCICIO_X_USUARIO->setIdSeccion($EJERCICIO->getIdEjercicioSeccion());
                    $EJERCICIO_X_USUARIO->setIdUsu($USUARIO);
                    $EJERCICIO_X_USUARIO->setIdEjercicio($EJERCICIO);
                    $EJERCICIO_X_USUARIO->setVisto(0);
                    $em->persist($EJERCICIO_X_USUARIO);
                }
            }
        }
        $em->flush();
    }
    
    /**
     * Comprueba si un ejercicio es de distrito o no
     * @param type $doctrine
     * @param type $EJERCICIO
     * @return true|false
     */
    static function esEjercicioDistrito($doctrine, $EJERCICIO){
        $val = $doctrine->getRepository('AppBundle:EjercicioDistrito')->findOneByIdEjercicio($EJERCICIO);
        if($val === null)
            return false;
        return true;
    }
    
    /**
     * Para localizar la entrega de un ejercicio de distrito realizada por un integrante del mismo
     * @param type $doctrine
     * @param type $EJERCICIO
     * @param type $DISTRITO
     * @return EJERCICIO_CALIFICACION|null
     */
    static function getCalificacionPrincipalDistrito($doctrine, $EJERCICIO, $DISTRITO){
        $CIUDADANOS = $doctrine->getRepository('AppBundle:Usuario')->findByIdDistrito($DISTRITO);
        $ENTREGAS = $doctrine->getRepository('AppBundle:EjercicioEntrega')->findByIdEjercicio($EJERCICIO);
        if(!count($CIUDADANOS || !count($ENTREGAS))){
            return null;
        }
        foreach($ENTREGAS as $ENTREGA){
            $CIUDADANO = $ENTREGA->getIdUsuario();
            if(in_array($CIUDADANO, $CIUDADANOS)){
                $CALIFICACION = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findBy([
                    'idEjercicio' => $EJERCICIO, 'idUsuario' => $CIUDADANO
                ]);
                return $CALIFICACION;
            }
        }
        return null;
    }

}
