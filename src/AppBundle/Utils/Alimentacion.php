<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Utils;

/**
 * Description of Alimentacion
 *
 * @author araluce
 */
class Alimentacion {

    static function getDatosComida($doctrine, $USUARIO, &$DATOS) {
        $UTILS = new \AppBundle\Utils\Utils();
        $ALIMENTACION = new \AppBundle\Utils\Alimentacion();

        // Obtenemos los ejercicios de comida del usuario
        $SECCION_COMIDA = $doctrine->getRepository('AppBundle:EjercicioSeccion')->findOneBySeccion('comida');
        $EJERCICIOS_COMIDA_DEL_USUARIO = $doctrine->getRepository('AppBundle:EjercicioXUsuario')->findBy([
            'idUsu' => $USUARIO, 'idSeccion' => $SECCION_COMIDA
        ]);
        $DATOS['NUMERO_EJERCICIOS'] = count($EJERCICIOS_COMIDA_DEL_USUARIO);

        $DATOS['SECCION'] = 'comida';
        
        $DATOS['EJERCICIOS'] = [];
        // Si el usuario tiene ejercicios...
        if ($DATOS['NUMERO_EJERCICIOS']) {
            foreach ($EJERCICIOS_COMIDA_DEL_USUARIO as $EJERCICIO_USUARIO) {
                $DATOS['EJERCICIOS'][] = $ALIMENTACION->makeInfoEjercicio($doctrine, $USUARIO, $EJERCICIO_USUARIO);
            }
        }
    }

    static function makeInfoEjercicio($doctrine, $USUARIO, $EJERCICIO_USUARIO) {
        $UTILS = new \AppBundle\Utils\Utils();

        // Creamos el entityManager
        $em = $doctrine->getManager();

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
        if (count($CALIFICACION)) {
            $DATOS['ESTADO'] = $CALIFICACION->getIdEjercicioEstado()->getEstado();
            if ($CALIFICACION->getIdCalificaciones() !== null) {
                $DATOS['EVALUADO'] = $CALIFICACION->getIdCalificaciones();
            }
            if ($DATOS['ESTADO'] === 'solicitado'){
                if ($DATOS['SECCION'] === 'comida') {
                    $DATOS['TSC'] = $USUARIO->getTiempoSinComer();
                }
                else{
                    $DATOS['TSC'] = $USUARIO->getTiempoSinBeber();
                }
            }
        }
        
        $DATOS['ELEGIBLE'] = 1;
        
        return $DATOS;
        
//        $aux['SOLICITANTES'] = count($SOLICITANTES);
//        $aux['NUM_MAX_SOLICITANTES'] = $NUM_MAX_SOLICITANTES->getValor();
//        
//        
//
//        $SOLICITANTES = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findByIdEjercicio($EJERCICIO);
//        $NUM_MAX_SOLICITANTES = $doctrine->getRepository('AppBundle:Constante')->findOneByClave('num_max_solicitantes_paga');
//
//        //$NUM_EJERCICIOS_SOLICITADOS = count($UTILS->ejercicios_solicitados_por_en($doctrine, $USUARIO, $ejercicio, 'paga_extra'));
//        $aux = [];
//        $EJERCICIOS_SOLICITADOS = $UTILS->ejercicios_solicitados_en($doctrine, $USUARIO, 'paga_extra');
//        if ($EJERCICIOS_SOLICITADOS === 0) {
//            $num_ejercicios_solicitados = 0;
//        } else {
//            $num_ejercicios_solicitados = count($EJERCICIOS_SOLICITADOS);
//        }
//        if ($num_ejercicios_solicitados) {
//            if (in_array($EJERCICIO, $EJERCICIOS_SOLICITADOS)) {
//                $aux['SOLICITADO'] = 1;
//            }
//        }
//        $EJERCICIOS_ENTREGADOS = $UTILS->ejercicios_entregados_en($doctrine, $USUARIO, 'paga_extra');
//        if (count($EJERCICIOS_ENTREGADOS)) {
//            if (in_array($EJERCICIO, $EJERCICIOS_ENTREGADOS)) {
//                $aux['ENTREGADO'] = 1;
//            }
//        }
//        if ($EJERCICIO_CALIFICACIONES !== null) {
//            $ESTADO = $EJERCICIO_CALIFICACIONES->getIdEjercicioEstado()->getEstado();
//            if ($ESTADO === 'evaluado') {
//                $aux['EVALUADO'] = $EJERCICIO_CALIFICACIONES->getIdCalificaciones();
//            }
//            $aux['ESTADO'] = $ESTADO;
//        }
//        $aux['ELEGIBLE'] = 1;
//        if (($num_ejercicios_solicitados > 0 &&
//                (!isset($aux['SOLICITADO']) && !isset($aux['ENTREGADO']))) || isset($aux['EVALUADO'])) {
//            $aux['ELEGIBLE'] = 0;
//        }
    }
    
    static function getCalificacionSolicitado($doctrine, $USUARIO, $seccion){
        $SECCION = $doctrine->getRepository('AppBundle:EjercicioSeccion')->findOneBySeccion($seccion);
        $SOLICITADO = $doctrine->getRepository('AppBundle:EjercicioEstado')->findOneByEstado('solicitado');
        if ($SECCION === 'null'){
            return 0;
        }
        
        $EJERCICIOS_SECCION = $doctrine->getRepository('AppBundle:Ejercicio')->findByIdEjercicioSeccion($SECCION);
        if(!count($EJERCICIOS_SECCION)){
            return 0;
        }
        
        $EJERCICIOS = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findBy([
            'idUsuario' => $USUARIO, 'idEjercicioEstado' => $SOLICITADO
        ]);
        if(!count($EJERCICIOS)){
            return 0;
        }
        
        foreach($EJERCICIOS as $EJERCICIO){
            if(in_array($EJERCICIO->getIdEjercicio(), $EJERCICIOS_SECCION)){
                return $EJERCICIO;
            }
        }
        return 0;
    }

}
