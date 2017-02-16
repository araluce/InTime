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
     * Obtiene los ciudadanos con estado distinto de fallecido de un distrito
     * @param type $doctrine
     * @param type $DISTRITO
     * @return null|array
     */
    static function getCiudadanosVivosDistrito($doctrine, $DISTRITO){
        $ESTADO_FALLECIDO = $doctrine->getRepository('AppBundle:UsuarioEstado')->findOneByNombre('Fallecido');
        $ROL_CIUDADANO = $doctrine->getRepository('AppBundle:Rol')->findOneByNombre('Jugador');
        $query = $doctrine->getRepository('AppBundle:Usuario')->createQueryBuilder('a');
        $query->select('a');
        $query->where('a.idDistrito = :DISTRITO AND a.idEstado != :ESTADO AND a.idRol = :ROL');
        $query->setParameters(['DISTRITO' => $DISTRITO, 'ESTADO' => $ESTADO_FALLECIDO, 'ROL' => $ROL_CIUDADANO]);
        $CIUDADANOS = $query->getQuery()->getResult();
        return $CIUDADANOS;
    }
    
    /**
     * Obtiene los ciudadanos con estado activo de un distrito
     * @param type $doctrine
     * @param type $DISTRITO
     * @return null|array
     */
    static function getCiudadanosActivosDistrito($doctrine, $DISTRITO){
        $ESTADO_ACTIVO = $doctrine->getRepository('AppBundle:UsuarioEstado')->findOneByNombre('Activo');
        $ROL_CIUDADANO = $doctrine->getRepository('AppBundle:Rol')->findOneByNombre('Jugador');
        $CIUDADANOS = $doctrine->getRepository('AppBundle:Usuario')->findBy([
            'idRol' => $ROL_CIUDADANO, 'idEstado' => $ESTADO_ACTIVO, 'idDistrito' => $DISTRITO
        ]);
        
        return $CIUDADANOS;
    }
}
