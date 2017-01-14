<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Utils;

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
        $DATOS['ESTADO_USUARIO'] = $usuario->getIdEstado()->getNombre();

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
                        }
                    }
                }
            }
        }
//        \AppBundle\Utils\Utils::pretty_print($DATOS);
        return $DATOS;
    }

}
