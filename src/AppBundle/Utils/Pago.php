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

    /**
     * Paga una mina a un usuario asignandole un premio base a ciudadanos que no han logrado 
     * desactivar la mina y el premio a los que la han desactivado
     * @param type $doctrine
     * @param type $USUARIO
     * @param type $ganador
     * @return int
     */
    static function pagarMina($doctrine, $MINA, $USUARIO, $ganador = false) {
        $em = $doctrine->getManager();
        if ($USUARIO === null) {
            Utils::setError($doctrine, 1, 'pagarMina - No existe el usuario');
            return 0;
        }
        if ($MINA === null) {
            Utils::setError($doctrine, 1, 'pagarMina - No existe la mina');
            return 0;
        }
        $PREMIO = Utils::getConstante($doctrine, "premio_mina");
        $PREMIO_BASE = Utils::getConstante($doctrine, "premio_base_mina");
        if($ganador){
            Usuario::operacionSobreTdV($doctrine, $USUARIO, $PREMIO, 'Ingreso - Premio desactivación de mina');
            $CALIFICACION_MEDIA = $doctrine->getRepository('AppBundle:Calificaciones')->findOneByIdCalificaciones(4);
            $EVALUADO = $doctrine->getRepository('AppBundle:EjercicioEstado')->findOneByEstado('evaluado');
            $CALIFICACION = new \AppBundle\Entity\EjercicioCalificacion();
            $CALIFICACION->setFecha(new \DateTime('now'));
            $CALIFICACION->setIdCalificaciones($CALIFICACION_MEDIA);
            $CALIFICACION->setIdEjercicio($MINA->getIdEjercicio());
            $CALIFICACION->setIdEjercicioEstado($EVALUADO);
            $CALIFICACION->setIdEvaluador(null);
            $CALIFICACION->setIdGrupo(null);
            $CALIFICACION->setIdUsuario($USUARIO);
            $em->persist($CALIFICACION);
            $em->flush();
        } else {
            Usuario::operacionSobreTdV($doctrine, $USUARIO, $PREMIO_BASE, 'Ingreso - Premio base mina para ciudadanos que no han conseguido desactivar la mina');
        }
        return 1;
    }

}
