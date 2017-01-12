<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Utils;

use AppBundle\Utils\Utils;
use AppBundle\Utils\Alimentacion;
use AppBundle\Utils\Ejercicio;

/**
 * Description of Alimentacion
 *
 * @author araluce
 */
class Alimentacion {

    static function getDatosAlimentacion($doctrine, $USUARIO, &$DATOS, $SECCION) {
        // Obtenemos los ejercicios de comida del usuario
        Ejercicio::actualizarEjercicioXUsuario($doctrine, $USUARIO);
        $EJERCICIOS_COMIDA_DEL_USUARIO = $doctrine->getRepository('AppBundle:EjercicioXUsuario')->findBy([
            'idUsu' => $USUARIO, 'idSeccion' => $SECCION
        ]);
        $DATOS['NUMERO_EJERCICIOS'] = count($EJERCICIOS_COMIDA_DEL_USUARIO);
        $DATOS['EJERCICIOS'] = [];
        // Si el usuario tiene ejercicios...
        if ($DATOS['NUMERO_EJERCICIOS']) {
            foreach ($EJERCICIOS_COMIDA_DEL_USUARIO as $EJERCICIO_USUARIO) {
                $DATOS['EJERCICIOS'][] = Alimentacion::makeInfoEjercicio($doctrine, $USUARIO, $EJERCICIO_USUARIO);
            }
        }
    }

    static function makeInfoEjercicio($doctrine, $USUARIO, $EJERCICIO_USUARIO) {
        // Obtenemos el ejercicio en cuestión
        $EJERCICIO = $EJERCICIO_USUARIO->getIdEjercicio();

        $DATOS = [];
        $DATOS['VISTO'] = $EJERCICIO_USUARIO->getVisto();
        $DATOS['ENUNCIADO'] = $EJERCICIO->getEnunciado();
        $DATOS['TIPO'] = $EJERCICIO->getIdTipoEjercicio()->getTipo();
        $DATOS['FECHA'] = $EJERCICIO->getFecha();
        $DATOS['ID'] = $EJERCICIO->getIdEjercicio();
        $DATOS['SECCION'] = $EJERCICIO->getIdEjercicioSeccion()->getSeccion();

        if ($USUARIO->getTiempoSinComer() !== null) {
            $DATOS['TIEMPO_SIN_COMER'] = $USUARIO->getTiempoSinComer()->getTimestamp();
        }
        $DATOS['ESTADO'] = 'no_solicitado';

        $CALIFICACION = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findOneBy([
            'idUsuario' => $USUARIO, 'idEjercicio' => $EJERCICIO
        ]);
        if ($CALIFICACION !== null) {
            $DATOS['ESTADO'] = $CALIFICACION->getIdEjercicioEstado()->getEstado();
            if ($CALIFICACION->getIdCalificaciones() !== null) {
                $DATOS['EVALUADO'] = $CALIFICACION->getIdCalificaciones();
            }
            if ($DATOS['ESTADO'] === 'solicitado') {
                if ($DATOS['SECCION'] === 'comida') {
                    $DATOS['TSC'] = $USUARIO->getTiempoSinComer();
                } else if ($DATOS['SECCION'] === 'bebida'){
                    $DATOS['TSB'] = $USUARIO->getTiempoSinBeber();
                }
            }
        }

        $DATOS['ELEGIBLE'] = 1;

        return $DATOS;
    }

    static function getCalificacionSolicitado($doctrine, $USUARIO, $seccion) {
        $SECCION = $doctrine->getRepository('AppBundle:EjercicioSeccion')->findOneBySeccion($seccion);
        $SOLICITADO = $doctrine->getRepository('AppBundle:EjercicioEstado')->findOneByEstado('solicitado');
        if ($SECCION === 'null') {
            return 0;
        }

        $EJERCICIOS_SECCION = $doctrine->getRepository('AppBundle:Ejercicio')->findByIdEjercicioSeccion($SECCION);
        if (!count($EJERCICIOS_SECCION)) {
            return 0;
        }

        $EJERCICIOS = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findBy([
            'idUsuario' => $USUARIO, 'idEjercicioEstado' => $SOLICITADO
        ]);
        if (!count($EJERCICIOS)) {
            return 0;
        }

        foreach ($EJERCICIOS as $EJERCICIO) {
            if (in_array($EJERCICIO->getIdEjercicio(), $EJERCICIOS_SECCION)) {
                return $EJERCICIO;
            }
        }
        return 0;
    }

    /**
     * Comprueba si se han solicitado ejercicios de la sección comida
     * 
     * @param type $doctrine
     * @param type $USUARIO
     * @return int 0 si no se han encontrado ejercicios en estado "solicitado"
     * de la sección "comida", 1 en cualquier otro caso
     */
    static function getSolicitadosComida($doctrine, $USUARIO) {
        $COMIDA = $doctrine->getRepository('AppBundle:EjercicioSeccion')->findOneBySeccion('comida');
        if ($COMIDA === null) {
            Utils::setError($doctrine, 1, 'No se encuentra la sección comida en EJERCICIO_SECCION');
            return -1;
        }
        $SOLICITADO = $doctrine->getRepository('AppBundle:EjercicioEstado')->findOneByEstado('solicitado');
        if ($SOLICITADO === null) {
            Utils::setError($doctrine, 1, 'No se encuentra el estado solicitado en EJERCICIO_ESTADO');
            return -1;
        }
        $EJERCICIOS = $doctrine->getRepository('AppBundle:Ejercicio')->findByIdEjercicioSeccion($COMIDA);
        if (count($EJERCICIOS)) {
            foreach ($EJERCICIOS as $EJERCICIO) {
                $CALIFICACION = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findOneBy([
                    "idEjercicio" => $EJERCICIO, "idEjercicioEstado" => $SOLICITADO, "idUsuario" => $USUARIO
                ]);
                if ($CALIFICACION !== null) {
                    return 1;
                }
            }
        }
        return 0;
    }
    static function porcetajeEnergia($ts_usuario, $ts_defecto){
        $HOY = new \DateTime('now');
        $respuesta = [];
        $respuesta['suelo'] = $ts_usuario->getTimestamp();
        $respuesta['techo'] = $respuesta['suelo'] + $ts_defecto->getValor();
        $respuesta['current'] = $HOY->getTimestamp();
        $respuesta['recorrido'] = $respuesta['current'] - $respuesta['suelo'];
        $respuesta['porcentaje'] = 100 - (($respuesta['recorrido'] * 100) / $ts_defecto->getValor());
        return $respuesta;
    }
}
