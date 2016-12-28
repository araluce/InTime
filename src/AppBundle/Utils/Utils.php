<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Utils;

/**
 * Description of Utils
 *
 * @author araluce
 */
class Utils {

    static function pretty_print($var) {
        print '<pre style="font-weight: bold;">';
        print_r($var);
        print '</pre>';
    }

    static function setNota($doctrine, $id_usuario, $id_grupo, $id_ejercicio, $nota) {
        if ($id_grupo === null) {
            $SECCION = $id_ejercicio->getIdEjercicioSeccion()->getSeccion();
        } else {
            $SECCION = $doctrine->getRepository('AppBundle:EjercicioXGrupo')->
                            findOneByIdGrupoEjercicios($id_grupo)->getIdEjercicio()->
                            getIdEjercicioSeccion()->getSeccion();
        }
        $corresponidencia_numerica = \AppBundle\Utils\Utils::notaToCalificacion($nota);
        $CALIFICACION = $doctrine->getRepository('AppBundle:Calificaciones')->findOneByCorrespondenciaNumerica($corresponidencia_numerica);
        $ROL_SISTEMA = $doctrine->getRepository('AppBundle:Rol')->findOneByNombre('Sistema');
        $SISTEMA = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdRol($ROL_SISTEMA);
        $EJERCICIO_CALIFICACION = [];

        if ($id_grupo !== null) {
            $EJERCICIO_CALIFICACION = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findOneBy([
                'idUsuario' => $id_usuario, 'idGrupo' => $id_grupo
            ]);
        } else {
            $EJERCICIO_CALIFICACION = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findOneBy([
                'idUsuario' => $id_usuario, 'idEjercicio' => $id_ejercicio
            ]);
        }

        if (!count($EJERCICIO_CALIFICACION)) {
            $EJERCICIO_CALIFICACION = new \AppBundle\Entity\EjercicioCalificacion();
            $EJERCICIO_CALIFICACION->setIdUsuario($id_usuario);
        }
        if ($id_grupo !== null) {
            $EJERCICIO_CALIFICACION->setIdGrupo($id_grupo);
        } else {
            $EJERCICIO_CALIFICACION->setIdEjercicio($id_ejercicio);
        }
        $TdVDefecto = $doctrine->getRepository('AppBundle:Constante')->findOneByClave('pago_paga_' . $corresponidencia_numerica);
        \AppBundle\Utils\Usuario::addTdVEjercicio($doctrine, $id_usuario, $TdVDefecto->getValor(), $EJERCICIO_CALIFICACION->getIdCalificaciones());
        $EJERCICIO_CALIFICACION->setIdCalificaciones($CALIFICACION);
        $EJERCICIO_CALIFICACION->setIdEvaluador($SISTEMA);
        $EJERCICIO_CALIFICACION->setFecha(new \DateTime('now'));
        $EJERCICIO_ESTADO = null;
        if ($SECCION === 'inspeccion_trabajo') {
            $EJERCICIO_ESTADO = $doctrine->getRepository('AppBundle:EjercicioEstado')->findOneByEstado('evaluado');
        }
        if ($SECCION === 'paga_extra' || $SECCION === 'comida' || $SECCION === 'bebida') {
            $EJERCICIO_ESTADO = $doctrine->getRepository('AppBundle:EjercicioEstado')->findOneByEstado('entregado');
        }
//        \AppBundle\Utils\Utils::pretty_print($EJERCICIO_ESTADO);
        $EJERCICIO_CALIFICACION->setIdEjercicioEstado($EJERCICIO_ESTADO);
//        \AppBundle\Utils\Utils::pretty_print($EJERCICIO_CALIFICACION);
        $doctrine->getManager()->persist($EJERCICIO_CALIFICACION);
        $doctrine->getManager()->flush();

        $DATOS = [];
        $DATOS['TITULO'] = 'Resultados';
        $DATOS['NOTA_TEXTO'] = $CALIFICACION->getCorrespondenciaTexto();
        $DATOS['NOTA_ICONO'] = $CALIFICACION->getCorrespondenciaIcono();
        $DATOS['NOTA_ID'] = $CALIFICACION->getIdCalificaciones();
        return $DATOS;
    }

    static function notaToCalificacion($nota) {
        if ($nota <= 4.5) {
            $correspondencia_numerica = 3.5;
        } elseif ($nota > 4.5 && $nota <= 5.5) {
            $correspondencia_numerica = 5;
        } elseif ($nota > 5.5 && $nota <= 7) {
            $correspondencia_numerica = 6.5;
        } elseif ($nota > 7 && $nota <= 8.5) {
            $correspondencia_numerica = 8;
        } elseif ($nota > 8.5 && $nota <= 9.5) {
            $correspondencia_numerica = 9;
        } elseif ($nota > 9.5) {
            $correspondencia_numerica = 10;
        }
        return $correspondencia_numerica;
    }

    static function getDatosArenaTest($doctrine, $USUARIO, $EJERCICIO, &$DATOS) {
        $DATOS['ENUNCIADO'] = $EJERCICIO->getEnunciado();
        $DATOS['TIPO'] = $EJERCICIO->getIdTipoEjercicio()->getTipo();
        $DATOS['SECCION'] = $EJERCICIO->getIdEjercicioSeccion()->getSeccion();
        $DATOS['FECHA'] = $EJERCICIO->getFecha();
        $DATOS['ID'] = $EJERCICIO->getIdEjercicio();

        if ($DATOS['TIPO'] === 'test') {
            $EJERCICIO_RESPUESTA = $doctrine->getRepository('AppBundle:EjercicioRespuesta')->findByIdEjercicio($EJERCICIO);
            $DATOS['RESPUESTAS'] = [];
            foreach ($EJERCICIO_RESPUESTA as $respuesta) {
                $aux = [];
                $aux['RESPUESTA'] = $respuesta->getRespuesta();
                $aux['CORRECTA'] = $respuesta->getCorrecta();
                $DATOS['RESPUESTAS'][] = $aux;
            }
        }
        if ($DATOS['TIPO'] === 'entrega') {
            $ENTREGA_USUARIO = $doctrine->getRepository('AppBundle:EjercicioEntrega')->findOneBy([
                'idUsuario' => $USUARIO, 'idEjercicio' => $EJERCICIO
            ]);
            if ($ENTREGA_USUARIO !== null) {
                $DATOS['ENTREGA'] = [];
                $EJERCICIO_CALIFICACION = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findOneBy([
                    'idUsuario' => $USUARIO, 'idEjercicio' => $EJERCICIO
                ]);
                $DATOS['ENTREGA']['NOMBRE'] = $ENTREGA_USUARIO->getNombre();
                $DATOS['ENTREGA']['MIME'] = $ENTREGA_USUARIO->getMime();
                $DATOS['ENTREGA']['FECHA'] = $ENTREGA_USUARIO->getFecha();
                $DATOS['ENTREGA']['URL'] = 'USUARIOS/' . $USUARIO->getDni()
                        . '/' . $DATOS['SECCION']
                        . '/' . $EJERCICIO_CALIFICACION->getIdEjercicioCalificacion()
                        . '/' . $DATOS['ENTREGA']['NOMBRE'];
            }
        }

        \AppBundle\Utils\Utils::setVisto($doctrine, $USUARIO, $EJERCICIO, null);
        return $DATOS;
    }

    static function getDatosArenaGrupoTest($doctrine, $USUARIO, $GRUPO) {
        $DATOS = [];
        $DATOS['EJERCICIOS'] = [];
        $aux = [];
        $EJERCICIO_X_GRUPO = $doctrine->getRepository('AppBundle:EjercicioXGrupo')->findByIdGrupoEjercicios($GRUPO);

        foreach ($EJERCICIO_X_GRUPO as $e) {
            $EJERCICIO = $e->getIdEjercicio();
            $aux['ENUNCIADO'] = $EJERCICIO->getEnunciado();
            $DATOS['TIPO'] = $EJERCICIO->getIdTipoEjercicio()->getTipo();
            $DATOS['SECCION'] = $EJERCICIO->getIdEjercicioSeccion()->getSeccion();
            $aux['FECHA'] = $EJERCICIO->getFecha();
            $aux['ID'] = $EJERCICIO->getIdEjercicio();

            if ($DATOS['TIPO'] === 'grupo_test') {
                $EJERCICIO_RESPUESTA = $doctrine->getRepository('AppBundle:EjercicioRespuesta')->findByIdEjercicio($EJERCICIO);
                $aux['RESPUESTAS'] = [];
                foreach ($EJERCICIO_RESPUESTA as $respuesta) {
                    $aux2 = [];
                    $aux2['RESPUESTA'] = $respuesta->getRespuesta();
                    $aux2['CORRECTA'] = $respuesta->getCorrecta();
                    $aux['RESPUESTAS'][] = $aux2;
                }
            }
            $DATOS['EJERCICIOS'][] = $aux;

            \AppBundle\Utils\Utils::setVisto($doctrine, $USUARIO, null, $GRUPO);
        }
        return $DATOS;
    }

    static function getDatosInspeccion($doctrine, $USUARIO, $ejercicio, $grupo) {
        $UTILS = new \AppBundle\Utils\Utils();
        $aux = [];
        if ($grupo !== null) {
            $ejercicio = $doctrine->getRepository('AppBundle:EjercicioXGrupo')->findOneByIdGrupoEjercicios($grupo)->getIdEjercicio();
            $EJERCICIO_USUARIO = $doctrine->getRepository('AppBundle:EjercicioXUsuario')->findOneByIdGrupo($grupo);
            $EJERCICIO_CALIFICACIONES = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findOneBy([
                'idUsuario' => $USUARIO, 'idGrupo' => $grupo
            ]);
        } else {
            $EJERCICIO_USUARIO = $doctrine->getRepository('AppBundle:EjercicioXUsuario')->findOneByIdEjercicio($ejercicio);
            $EJERCICIO_CALIFICACIONES = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findOneBy([
                'idUsuario' => $USUARIO, 'idEjercicio' => $ejercicio
            ]);
        }
        $aux['ENUNCIADO'] = $ejercicio->getEnunciado();
        $aux['TIPO'] = $ejercicio->getIdTipoEjercicio()->getTipo();
        $aux['TIPO_ID'] = $ejercicio->getIdTipoEjercicio()->getIdTipoEjercicio();
        $aux['FECHA'] = $ejercicio->getFecha();
        $aux['ID'] = $ejercicio->getIdEjercicio();
        $aux['VISTO'] = $EJERCICIO_USUARIO->getVisto();
        $aux['ELEGIBLE'] = 1;
        if (count($EJERCICIO_CALIFICACIONES)) {
            if ($EJERCICIO_CALIFICACIONES->getIdEjercicioEstado()->getEstado() === 'evaluado') {
                $aux['EVALUADO'] = $EJERCICIO_CALIFICACIONES->getIdCalificaciones();
            }
        }
        return $aux;
    }

    static function getDatosPaga($doctrine, $USUARIO, $ejercicio) {
        $UTILS = new \AppBundle\Utils\Utils();
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
        $EJERCICIOS_SOLICITADOS = $UTILS->ejercicios_solicitados_en($doctrine, $USUARIO, 'paga_extra');
        if ($EJERCICIOS_SOLICITADOS === 0) {
            $num_ejercicios_solicitados = 0;
        } else {
            $num_ejercicios_solicitados = count($EJERCICIOS_SOLICITADOS);
        }
        if ($num_ejercicios_solicitados) {
            if (in_array($ejercicio, $EJERCICIOS_SOLICITADOS)) {
                $aux['SOLICITADO'] = 1;
            }
        }
        $EJERCICIOS_ENTREGADOS = $UTILS->ejercicios_entregados_en($doctrine, $USUARIO, 'paga_extra');
        if (count($EJERCICIOS_ENTREGADOS)) {
            if (in_array($ejercicio, $EJERCICIOS_ENTREGADOS)) {
                $aux['ENTREGADO'] = 1;
            }
        }

        $aux['ENUNCIADO'] = $ejercicio->getEnunciado();
        $aux['TIPO'] = $ejercicio->getIdTipoEjercicio()->getTipo();
        $aux['TIPO_ID'] = $ejercicio->getIdTipoEjercicio()->getIdTipoEjercicio();
        $aux['FECHA'] = $ejercicio->getFecha();
        $aux['ID'] = $ejercicio->getIdEjercicio();
        $aux['VISTO'] = $EJERCICIO_USUARIO->getVisto();
        $aux['INTENTOS'] = count($EJERCICIO_CALIFICACIONES);
        $aux['SOLICITANTES'] = count($SOLICITANTES);
        $aux['NUM_MAX_SOLICITANTES'] = $NUM_MAX_SOLICITANTES->getValor();
        $aux['ESTADO'] = 'no_solicitado';
        if ($EJERCICIO_CALIFICACIONES !== null) {
            $ESTADO = $EJERCICIO_CALIFICACIONES->getIdEjercicioEstado()->getEstado();
            if ($ESTADO === 'evaluado') {
                $aux['EVALUADO'] = $EJERCICIO_CALIFICACIONES->getIdCalificaciones();
            }
            $aux['ESTADO'] = $ESTADO;
        }
        $aux['ELEGIBLE'] = 1;
        if (($num_ejercicios_solicitados > 0 &&
                (!isset($aux['SOLICITADO']) && !isset($aux['ENTREGADO']))) || isset($aux['EVALUADO'])) {
            $aux['ELEGIBLE'] = 0;
        }

        return $aux;
    }

    /**
     * Función que marca una actividad como vista por el usuario
     * 
     * @param type $doctrine
     * @param Entity $id_usuario 
     * @param Entity $id_ejercicio null si hacemos referencia a un grupo
     * @param Entity $id_grupo null si hacemos referencia a un ejercicio
     * @return int 0 si el ejercicio no existe para el usuario, 1 para éxito
     */
    static function setVisto($doctrine, $id_usuario, $id_ejercicio, $id_grupo) {
        if ($id_grupo === null) {
            $EJERCICIO_X_USUARIO = $doctrine->getRepository('AppBundle:EjercicioXUsuario')->findOneBy([
                'idEjercicio' => $id_ejercicio, 'idUsu' => $id_usuario
            ]);
        } else {
            $EJERCICIO_X_USUARIO = $doctrine->getRepository('AppBundle:EjercicioXUsuario')->findOneBy([
                'idGrupo' => $id_grupo, 'idUsu' => $id_usuario
            ]);
        }

        if ($EJERCICIO_X_USUARIO === null) {
            return 0;
        } else {
            $em = $doctrine->getManager();
            $EJERCICIO_X_USUARIO->setVisto(1);
            $em->persist($EJERCICIO_X_USUARIO);
            $em->flush();
            return 1;
        }
    }

    /**
     * 
     * @param type $doctrine
     * @param Entity $id_ejercicio 
     * @return int número de alumnos que han solicitado realizar el ejercicio
     */
    static function comprueba_numero_solicitudes($doctrine, $id_ejercicio) {
        $SOLICITANTES = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findByIdEjercicio($id_ejercicio);

        return count($SOLICITANTES);
    }

    /**
     * Función que retorna todos los ejercicios solicitados por el usuario en
     * una sección concreta
     * 
     * @param type $doctrine
     * @param Entity\Usuario $USUARIO
     * @param Entity\Ejercicio $EJERCICIO
     * @param string $SECCION
     * @return array<Entity\EjercicioCalificacion> 
     */
    static function ejercicios_solicitados_por_en($doctrine, $USUARIO, $EJERCICIO, $SECCION) {
        $SECCION_PAGA = $doctrine->getRepository('AppBundle:EjercicioSeccion')->findOneBySeccion($SECCION);
        $ESTADO_SOLICITADO = $doctrine->getRepository('AppBundle:EjercicioEstado')->findOneByEstado('solicitado');
        $EJERCICIOS_SOLICITADOS = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findBy([
            'idUsuario' => $USUARIO, 'idEjercicio' => $EJERCICIO, 'idEjercicioEstado' => $ESTADO_SOLICITADO
        ]);
        $EJERCICIOS_SOLICITADOS_SECCION = [];
        foreach ($EJERCICIOS_SOLICITADOS as $EJERCICIO_SOLICITADO) {
            $seccion_ejercicio = $EJERCICIO_SOLICITADO->getIdEjercicio()->getIdEjercicioSeccion();
            if ($seccion_ejercicio === $SECCION_PAGA) {
                $EJERCICIOS_SOLICITADOS_SECCION[] = $EJERCICIO_SOLICITADO;
            }
        }
        return $EJERCICIOS_SOLICITADOS_SECCION;
    }

    static function ejercicios_solicitados_en($doctrine, $USUARIO, $SECCION_SOLICITADA) {
        $SECCION = $doctrine->getRepository('AppBundle:EjercicioSeccion')->findOneBySeccion($SECCION_SOLICITADA);
        $ESTADO_SOLICITADO = $doctrine->getRepository('AppBundle:EjercicioEstado')->findOneByEstado('solicitado');
        //$ESTADO_ENTREGADO  = $doctrine->getRepository('AppBundle:EjerciciEstado')->findOneBySeccion('entregado');
        $EJERCICIOS_SECCION = $doctrine->getRepository('AppBundle:Ejercicio')->findByIdEjercicioSeccion($SECCION);
        $EJERCICIOS_SOLICITADOS = [];
        $CALIFICACIONES_SOLICITADAS = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findBy([
            'idUsuario' => $USUARIO, 'idEjercicioEstado' => $ESTADO_SOLICITADO
        ]);
        if (count($CALIFICACIONES_SOLICITADAS)) {
            foreach ($CALIFICACIONES_SOLICITADAS as $EJERCICIO) {
                if (in_array($EJERCICIO->getIdEjercicio(), $EJERCICIOS_SECCION)) {
                    $EJERCICIOS_SOLICITADOS[] = $EJERCICIO->getIdEjercicio();
                }
            }
            return $EJERCICIOS_SOLICITADOS;
        } else {
            return 0;
        }
    }

    static function ejercicios_entregados_en($doctrine, $USUARIO, $SECCION_SOLICITADA) {
        $SECCION = $doctrine->getRepository('AppBundle:EjercicioSeccion')->findOneBySeccion($SECCION_SOLICITADA);
        $ESTADO_ENTREGADO = $doctrine->getRepository('AppBundle:EjercicioEstado')->findOneByEstado('entregado');
        $EJERCICIOS_SECCION = $doctrine->getRepository('AppBundle:Ejercicio')->findByIdEjercicioSeccion($SECCION);
        $EJERCICIOS_ENTREGADOS = [];
        $CALIFICACIONES_SOLICITADAS = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findBy([
            'idUsuario' => $USUARIO, 'idEjercicioEstado' => $ESTADO_ENTREGADO
        ]);
        if (count($CALIFICACIONES_SOLICITADAS)) {
            foreach ($CALIFICACIONES_SOLICITADAS as $EJERCICIO) {
                if (in_array($EJERCICIO->getIdEjercicio(), $EJERCICIOS_SECCION)) {
                    $EJERCICIOS_ENTREGADOS[] = $EJERCICIO->getIdEjercicio();
                }
            }
            return $EJERCICIOS_ENTREGADOS;
        } else {
            return 0;
        }
    }

    static function getConstante($doctrine, $constante) {
        $C = $doctrine->getRepository('AppBundle:Constante')->findOneByClave($constante);
        if ($C !== null) {
            $VALOR = $C->getValor();
            if ($VALOR === 0) {
                \AppBundle\Utils\Utils::setError($doctrine, 0, 'Constante ' . $constante . ' tiene valor 0');
                return "0";
            }
            return $VALOR;
        }
        \AppBundle\Utils\Utils::setError($doctrine, 1, 'No se encuentra la constante ' . $constante);
        return 0;
    }

    /**
     * Registra el mal funcionamiento en la base de datos
     * @param type $doctrine
     * @param int $nivel 0 para Warning, 1 para Error
     * @param string $accion La acción que ha producido el error
     * @param Entity $usuario Usuario involucrado si lo hubiera
     */
    static function setError($doctrine, $nivel, $accion, $usuario=null) {
        $em = $doctrine->getManager();
        $REPORT = new \AppBundle\Entity\LogError();
        switch ($nivel){
            case '0':
                $REPORT->setNivel('Warning');
                break;
            case '1':
                $REPORT->setNivel('Error');
        }
        if($usuario !== null){
            $REPORT->setIdUsuario($usuario);
        }
        $REPORT->setAccion($accion);
        $REPORT->setFecha(new \DateTime('now'));
        $em->persist($REPORT);
        $em->flush();
    }

}