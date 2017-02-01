<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Utils;

/**
 * Description of Distrito
 *
 * @author araluce
 */
class Distrito {
    
    /**
     * Devuelve los ciudadanos de un distrito
     * @param type $doctrine
     * @param type $DISTRITO
     * @return type
     */
    static function getCiudadanosDistrito($doctrine, $DISTRITO){
        $USUARIOS = Distrito::getCiudadanosVivosDistrito($doctrine, $DISTRITO);
        return $USUARIOS;
    }
    
    /**
     * Obtiene los ciudadanos vivos de un distrito
     * @param type $doctrine
     * @param type $DISTRITO
     * @return null|array
     */
    static function getCiudadanosVivosDistrito($doctrine, $DISTRITO){
        $ESTADO_ACTIVO = $doctrine->getRepository('AppBundle:UsuarioEstado')->findOneByNombre('Activo');
        $ROL_CIUDADANO = $doctrine->getRepository('AppBundle:Rol')->findOneByNombre('Jugador');
        $CIUDADANOS = $doctrine->getRepository('AppBundle:Usuario')->findBy([
            'idRol' => $ROL_CIUDADANO, 'idEstado' => $ESTADO_ACTIVO, 'idDistrito' => $DISTRITO
        ]);
        
        return $CIUDADANOS;
    }
}
