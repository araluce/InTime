<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Utils;

use AppBundle\Utils\Usuario;
use AppBundle\Utils\Utils;

/**
 * Description of Pago
 *
 * @author araluce
 */
class Pago {

    static function pagarMina($doctrine, $usuario) {
        $em = $doctrine->getManager();
        $qb = $em->createQueryBuilder();
        if ($usuario === null) {
            \AppBundle\Utils\Utils::setError($doctrine, 1, 'pagarMina - No existe el usuario');
            return 0;
        }
        $PREMIO = Utils::getConstante($doctrine, "premio_mina");
        $PREMIO_BASE = Utils::getConstante($doctrine, "premio_base_mina");
        Usuario::operacionSobreTdV($doctrine, $usuario, $PREMIO, 'Ingreso - Premio desactivación de mina');
        $DISTRITO = $usuario->getIdDistrito();
        $query = $qb->select('u')
                ->from('\AppBundle\Entity\Usuario', 'u')
                ->where('u.idDistrito IS NOT NULL AND u.idDistrito = :IdDistrito AND u.idUsuario != :IdUsuario')
                ->setParameters(['IdDistrito' => $DISTRITO, 'IdUsuario' => $usuario->getIdUsuario()]);
        $USUARIOS_GANADORES = $query->getQuery()->getResult();
        if(count($USUARIOS_GANADORES)){
            foreach($USUARIOS_GANADORES as $U){
                Usuario::operacionSobreTdV($doctrine, $U, $PREMIO_BASE, 'Ingreso - Desactivación de mina por @' . $usuario->getSeudonimo());
            }
        }
        return 1;
    }

}
