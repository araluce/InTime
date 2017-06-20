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
     * Devuelve el conjunto de ciudadanos de un distrito
     * @param type $doctrine
     * @param int $DISTRITO
     * @return null|array(<AppBundle\Entity\Usuario>)
     */
    static function getCiudadanosDistrito($doctrine, $DISTRITO){
        $USUARIOS = $doctrine->getRepository('AppBundle:Usuario')->findByIdDistrito($DISTRITO);
        return $USUARIOS;
    }
    
    /**
     * Obtiene los ciudadanos de un distrito con estado distinto de fallecido o 
     * inactivo
     * @param type $doctrine
     * @param int $DISTRITO
     * @return null|array(<AppBundle\Entity\Usuario>)
     */
    static function getCiudadanosVivosDistrito($doctrine, $DISTRITO){
        $ESTADO_FALLECIDO = $doctrine->getRepository('AppBundle:UsuarioEstado')->findOneByNombre('Fallecido');
        $ESTADO_INACTIVO = $doctrine->getRepository('AppBundle:UsuarioEstado')->findOneByNombre('Inactivo');
        $ROL_CIUDADANO = $doctrine->getRepository('AppBundle:Rol')->findOneByNombre('Jugador');
        $query = $doctrine->getRepository('AppBundle:Usuario')->createQueryBuilder('a');
        $query->select('a');
        $query->where('a.idDistrito = :DISTRITO AND a.idEstado != :ESTADO_F AND a.idEstado != :ESTADO_I AND a.idRol = :ROL');
        $query->setParameters(['DISTRITO' => $DISTRITO, 'ESTADO_F' => $ESTADO_FALLECIDO, 'ESTADO_I' => $ESTADO_INACTIVO, 'ROL' => $ROL_CIUDADANO]);
        $CIUDADANOS = $query->getQuery()->getResult();
        return $CIUDADANOS;
    }
    
    /**
     * Obtiene los ciudadanos con estado activo de un distrito
     * @param type $doctrine
     * @param int $DISTRITO
     * @return null|array(<AppBundle\Entity\Usuario>)
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
