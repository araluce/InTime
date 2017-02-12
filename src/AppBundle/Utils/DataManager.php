<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Utils;

use AppBundle\Utils\Usuario;

/**
 * Description of DataManager
 *
 * @author araluce
 */
class DataManager {

    static function setDefaultData($doctrine, $TITULO, $session) {
        $id_usuario = $session->get('id_usuario');
        $usuario = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($id_usuario);
        $DATOS['TITULO'] = $TITULO;
        $DATOS['TDV'] = $usuario->getIdCuenta()->getTdv();
        $DATOS['BLOQUEO'] = $usuario->getIdCuenta()->getFinbloqueo();
        $FECHA = new \DateTime('now');
        if ($DATOS['TDV'] < $FECHA) {
            Usuario::setDefuncion($doctrine, $usuario);
        }
        $DATOS['ESTADO_USUARIO'] = $usuario->getIdEstado()->getNombre();
        $DATOS['CHAT'] = DataManager::chatsPendientes($doctrine, $usuario);

        $mensajes_sin_ver = $doctrine->getRepository('AppBundle:MensajeXUsuario')->findBy(
                ['idUsuario' => $usuario, 'visto' => 0]);
        if (count($mensajes_sin_ver)) {
            $DATOS['MENSAJES'] = [];
            foreach ($mensajes_sin_ver as $mensaje) {
                $MENSAJE = [];
                $MENSAJE['TITULO'] = $mensaje->getIdMensaje()->getTitulo();
                $MENSAJE['TEXTO'] = $mensaje->getIdMensaje()->getMensaje();
                $MENSAJE['FECHA'] = $mensaje->getIdMensaje()->getFecha();
//                $tipo_mensaje = $mensaje->getIdMensaje()->getIdTipoMensaje()->getIdTipoMensaje();
                $DATOS['MENSAJES'][] = $MENSAJE;
//                switch ($tipo_mensaje) {
//                    case 1:
//                        $DATOS['MENSAJES'][] = $MENSAJE;
//                        break;
//                    case 4:
//                        $DATOS['MENSAJES_INSPECCION'][] = $MENSAJE;
//                        break;
//                    case 5:
//                        $DATOS['MENSAJES_PAGA'][] = $MENSAJE;
//                        break;
//                }
            }
            $em = $doctrine->getManager();
            $mensajes_sin_ver[0]->setVisto(1);
            $em->persist($mensajes_sin_ver[0]);
            $em->flush();
        }

        $EJERCICIOS_X_USUARIO = $doctrine->getRepository('AppBundle:EjercicioXUsuario')->findBy([
            'idUsu' => $usuario,
            'visto' => 0
        ]);
        if (count($EJERCICIOS_X_USUARIO)) {
            $DATOS['MENSAJES_INSPECCION'] = [];
            $DATOS['MENSAJES_PAGA'] = [];
            $DATOS['MENSAJES_COMIDA'] = [];
            $DATOS['MENSAJES_BEBIDA'] = [];
            $ids_grupos = [];
            foreach ($EJERCICIOS_X_USUARIO as $ejercicio) {
                $aux = [];
                $esta = false;
                if (!$ejercicio->getVisto()) {
                    $posible_ejercicio = $ejercicio->getIdEjercicio();
                    $posible_grupo = $ejercicio->getIdGrupo();
                    $aux['ID'] = [];
                    $aux2 = [];

                    if ($posible_ejercicio !== null) {
                        $aux2['TIPO'] = 'ejercicio';
                        $aux2['ID'] = $posible_ejercicio->getIdEjercicio();
                    } else {
                        $grupo = $posible_grupo->getIdGrupoEjercicios();
                        if (in_array($grupo, $ids_grupos)) {
                            $esta = true;
                        } else {
                            $ids_grupos[] = $grupo;
                        }
                        $aux2['TIPO'] = 'grupo';
                        $aux2['ID'] = $grupo;
                    }
                    $aux['ID'][] = $aux2;
                    if (!$esta) {
                        switch ($ejercicio->getIdSeccion()->getIdEjercicioSeccion()) {
                            case 1:
                                $DATOS['MENSAJES_INSPECCION'][] = $aux;
                                break;
                            case 2:
                                $DATOS['MENSAJES_PAGA'][] = $aux;
                                break;
                            case 3:
                                $DATOS['MENSAJES_COMIDA'][] = $aux;
                                break;
                            case 4:
                                $DATOS['MENSAJES_BEBIDA'][] = $aux;
                                break;
                        }
                    }
                }
            }
        }
//        \AppBundle\Utils\Utils::pretty_print($DATOS);
        return $DATOS;
    }

    static function chatsPendientes($doctrine, $USUARIO) {
        $CIUDADANOS = Usuario::getUsuariosMenosSistema($doctrine);
        $contador = 0;
        if ($CIUDADANOS !== null) {
            foreach ($CIUDADANOS as $CIUDADANO) {
                $contador += Usuario::numeroMensajesChat($doctrine, $USUARIO, $CIUDADANO);
            }
        }
        return $contador;
    }

    static function getDatosEjercicioInspeccion($doctrine, $USUARIO, $EJERCICIO) {
        $aux = [];
        $EJERCICIO_USUARIO = $doctrine->getRepository('AppBundle:EjercicioXUsuario')->findOneBy([
            'idUsu' => $USUARIO, 'idEjercicio' => $EJERCICIO
        ]);
        $EJERCICIO_CALIFICACIONES = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findOneBy([
            'idUsuario' => $USUARIO, 'idEjercicio' => $EJERCICIO
        ]);
        $CALIFICACION = $doctrine->getRepository('AppBundle:Calificaciones')->findOneByIdCalificaciones(4);
        $EJERCICIO_BENEFICIO = $doctrine->getRepository('AppBundle:EjercicioBonificacion')->findOneBy([
            'idEjercicio' => $EJERCICIO, 'idCalificacion' => $CALIFICACION
        ]);
        $aux['ENUNCIADO'] = $EJERCICIO->getEnunciado();
        $aux['BENEFICIO'] = Utils::segundosToDias($EJERCICIO_BENEFICIO->getBonificacion());
        $aux['ID'] = $EJERCICIO->getIdEjercicio();
        $aux['VISTO'] = $EJERCICIO_USUARIO->getVisto();
        $aux['COSTE'] = Utils::segundosToDias($EJERCICIO->getCoste());
        $aux['ELEGIBLE'] = true;
        if ($EJERCICIO_CALIFICACIONES !== null) {
            $aux['ELEGIBLE'] = false;
            if($EJERCICIO_CALIFICACIONES->getIdCalificaciones() !== null){
                $aux['CORRECTO'] = true;
            } else {
                $aux['CORRECTO'] = false;
            }
        }
        return $aux;
    }

    static function getDatosEjercicioPaga($doctrine, $USUARIO, $ejercicio) {
        $EJERCICIO_USUARIO = $doctrine->getRepository('AppBundle:EjercicioXUsuario')->findOneBy([
            'idEjercicio' => $ejercicio, 'idUsu' => $USUARIO
        ]);
        $EJERCICIO_CALIFICACIONES = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findOneBy([
            'idUsuario' => $USUARIO, 'idEjercicio' => $ejercicio
        ]);
        $SOLICITANTES = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findByIdEjercicio($ejercicio);
        $NUM_MAX_SOLICITANTES = $doctrine->getRepository('AppBundle:Constante')->findOneByClave('num_max_solicitantes_paga');

        //$NUM_EJERCICIOS_SOLICITADOS = count($UTILS->ejercicios_solicitados_por_en($doctrine, $USUARIO, $ejercicio, 'paga_extra'));
        $aux = [];
        $aux['ELEGIBLE'] = true;
        $aux['ENUNCIADO'] = $ejercicio->getEnunciado();
        $aux['FECHA'] = $ejercicio->getFecha();
        $aux['ID'] = $ejercicio->getIdEjercicio();
        $aux['COSTE'] = Utils::segundosToDias($ejercicio->getCoste());
        $aux['VISTO'] = $EJERCICIO_USUARIO->getVisto();
        $aux['SOLICITANTES'] = count($SOLICITANTES);
        $aux['NUM_MAX_SOLICITANTES'] = $NUM_MAX_SOLICITANTES->getValor();
        $aux['ESTADO'] = 'no_solicitado';
        if ($EJERCICIO_CALIFICACIONES !== null) {
            $ESTADO = $EJERCICIO_CALIFICACIONES->getIdEjercicioEstado()->getEstado();
            if ($ESTADO === 'evaluado') {
                $aux['EVALUACION'] = [];
                $aux['EVALUACION']['ICONO'] = $EJERCICIO_CALIFICACIONES->getIdCalificaciones()->getCorrespondenciaIcono();
                $aux['EVALUACION']['TEXTO'] = $EJERCICIO_CALIFICACIONES->getIdCalificaciones()->getCorrespondenciaTexto();
                $aux['EVALUACION']['NUMERICA'] = $EJERCICIO_CALIFICACIONES->getIdCalificaciones()->getCorrespondenciaNumerica();
            }
            $aux['ESTADO'] = $ESTADO;
        }

        return $aux;
    }

}
