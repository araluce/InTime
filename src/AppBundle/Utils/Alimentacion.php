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
use AppBundle\Utils\Distrito;

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
                } else if ($DATOS['SECCION'] === 'bebida') {
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
    static function getSolicitadosComida($doctrine, $USUARIO, $buscar_distrito = null) {
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
                    $EJERCICIO_DISTRITO = $doctrine->getRepository('AppBundle:EjercicioDistrito')->findOneByIdEjercicio($EJERCICIO);
                    if ($buscar_distrito && $EJERCICIO_DISTRITO !== null) {
                        return 1;
                    }
                    if (!$buscar_distrito && $EJERCICIO_DISTRITO === null) {
                        return 1;
                    }
                }
            }
        }
        return 0;
    }

    /**
     * Comprueba si se han solicitado ejercicios de la sección bebida
     * 
     * @param type $doctrine
     * @param type $USUARIO
     * @return int 0 si no se han encontrado ejercicios en estado "solicitado"
     * de la sección "bebida", 1 en cualquier otro caso
     */
    static function getSolicitadosBebida($doctrine, $USUARIO, $buscar_distrito = null) {
        $BEBIDA = $doctrine->getRepository('AppBundle:EjercicioSeccion')->findOneBySeccion('bebida');
        if ($BEBIDA === null) {
            Utils::setError($doctrine, 1, 'No se encuentra la sección bebida en EJERCICIO_SECCION');
            return -1;
        }
        $SOLICITADO = $doctrine->getRepository('AppBundle:EjercicioEstado')->findOneByEstado('solicitado');
        if ($SOLICITADO === null) {
            Utils::setError($doctrine, 1, 'No se encuentra el estado solicitado en EJERCICIO_ESTADO');
            return -1;
        }
        $EJERCICIOS = $doctrine->getRepository('AppBundle:Ejercicio')->findByIdEjercicioSeccion($BEBIDA);
        if (count($EJERCICIOS)) {
            foreach ($EJERCICIOS as $EJERCICIO) {
                $CALIFICACION = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findOneBy([
                    "idEjercicio" => $EJERCICIO, "idEjercicioEstado" => $SOLICITADO, "idUsuario" => $USUARIO
                ]);
                if ($CALIFICACION !== null) {
                    $EJERCICIO_DISTRITO = $doctrine->getRepository('AppBundle:EjercicioDistrito')->findOneByIdEjercicio($EJERCICIO);
                    if ($buscar_distrito && $EJERCICIO_DISTRITO !== null) {
                        return 1;
                    }
                    if (!$buscar_distrito && $EJERCICIO_DISTRITO === null) {
                        return 1;
                    }
                }
            }
        }
        return 0;
    }

    /**
     * Realiza la operación de ts_usuario, ts_defecto a porcentaje
     * @param type $ts_usuario
     * @param type $ts_defecto
     * @return type
     */
    static function porcetajeEnergia($ts_usuario, $ts_defecto) {
        $HOY = new \DateTime('now');
        $respuesta = [];
        $respuesta['suelo'] = $ts_usuario->getTimestamp();
        $respuesta['techo'] = $respuesta['suelo'] + $ts_defecto->getValor();
        $respuesta['current'] = $HOY->getTimestamp();
        $respuesta['recorrido'] = $respuesta['current'] - $respuesta['suelo'];
        $respuesta['porcentaje'] = 100 - (($respuesta['recorrido'] * 100) / $ts_defecto->getValor());
        return $respuesta;
    }

    /**
     * Actualiza la fecha TSC o TSB dependiendo de la sección
     * @param type $doctrine
     * @param type $USUARIO
     * @param type $SECCION
     */
    static function setTSC_TSB($doctrine, $USUARIO, $SECCION) {
        $em = $doctrine->getManager();
        $FECHA = new \DateTime('now');
        $DATE_TIEMPO_SIN = $FECHA->getTimestamp();
        $DATE = date('Y-m-d H:i:s', intval($DATE_TIEMPO_SIN));

        if ($SECCION === 'comida') {
            $USUARIO->setTiempoSinComer(\DateTime::createFromFormat('Y-m-d H:i:s', $DATE));
        }
        if ($SECCION === 'bebida') {
            $USUARIO->setTiempoSinBeber(\DateTime::createFromFormat('Y-m-d H:i:s', $DATE));
        }
        $em->persist($USUARIO);
        $em->flush();
    }

    /**
     * Actualiza la fecha TSC o TSB de distrito dependiendo de la sección
     * @param type $doctrine
     * @param type $USUARIO
     * @param type $SECCION
     */
    static function setTSCD_TSBD($doctrine, $USUARIO, $SECCION) {
        $em = $doctrine->getManager();
        $FECHA = new \DateTime('now');
        $DATE_TIEMPO_SIN = $FECHA->getTimestamp();
        $DATE = date('Y-m-d H:i:s', intval($DATE_TIEMPO_SIN));

        if ($SECCION === 'comida') {
            $USUARIO->setTiempoSinComerDistrito(\DateTime::createFromFormat('Y-m-d H:i:s', $DATE));
        }
        if ($SECCION === 'bebida') {
            $USUARIO->setTiempoSinBeberDistrito(\DateTime::createFromFormat('Y-m-d H:i:s', $DATE));
        }
        $em->persist($USUARIO);
        $em->flush();
    }

    /**
     * Separación entre entregas por secciones. Esta función nos dice si un 
     * ejercicio puede ser entregado si el último ejercicio de su sección 
     * @param type $doctrine
     * @param type $SECCION
     * @param type $USUARIO
     * @return int
     */
    static function tiempoEntreEntregas($doctrine, $SECCION, $USUARIO, $DISTRITO = null) {
        $FECHA = new \DateTime('now');
        if ($DISTRITO === null) {
            $ultimoEjercicio = Alimentacion::ultimaEntrega($doctrine, $SECCION, $USUARIO);
        } else {
            $ultimoEjercicio = Alimentacion::ultimaEntrega($doctrine, $SECCION, $USUARIO, $USUARIO->getIdDistrito());
        }
        if ($ultimoEjercicio) {
            $diasDif = Utils::getConstante($doctrine, 'diasDifEntregas');
            $fechaEntrega = $ultimoEjercicio->getFecha();
            if ($FECHA->format("Y") === $fechaEntrega->format("Y")) {
                if ($FECHA->format("d") - $FECHA->format("d") < $diasDif) {
                    return 0;
                }
            }
        }
        return 1;
    }

    /**
     * Obtiene la última entrega de una sección específica
     * @param type $doctrine
     * @param type $SECCION
     * @param type $USUARIO
     * @return 0|Ejercicio
     */
    static function ultimaEntrega($doctrine, $SECCION, $USUARIO, $DISTRITO = null) {
        $EJERCICIOS = $doctrine->getRepository('AppBundle:Ejercicio')->findByIdEjercicioSeccion($SECCION);
        if (!count($EJERCICIOS)) {
            return 0;
        }
        if ($DISTRITO !== null) {
            $query = $doctrine->getRepository('AppBundle:EjercicioDistrito')->createQueryBuilder('a');
            $query->select('a');
            $query->where('a.idEjercicio IN (:EJERCICIOS)');
            $query->setParameters(['EJERCICIOS' => array_values($EJERCICIOS)]);
            $EJERCICIO_DISTRITO = $query->getQuery()->getResult();
            if (!count($EJERCICIO_DISTRITO)) {
                return 0;
            }
            $ID_EJERCICIOS = [];
            foreach ($EJERCICIO_DISTRITO as $E) {
                $ID_EJERCICIOS[] = $E->getIdEjercicio();
            }
            $USUARIOS_DISTRITO = $doctrine->getRepository('AppBundle:Usuario')->findByIdDistrito($DISTRITO);
            $query = $doctrine->getRepository('AppBundle:EjercicioEntrega')->createQueryBuilder('a');
            $query->select('a');
            $query->where('a.idEjercicio IN (:EJERCICIOS) AND a.idUsuario IN (:USUARIOS)');
            $query->orderBy('a.fecha', 'DESC');
            $query->setParameters(['EJERCICIOS' => array_values($ID_EJERCICIOS), 'USUARIOS' => array_values($USUARIOS_DISTRITO)]);
            $ENTREGAS = $query->getQuery()->getResult();
        } else {
            $query = $doctrine->getRepository('AppBundle:EjercicioEntrega')->createQueryBuilder('a');
            $query->select('a');
            $query->where('a.idEjercicio IN (:EJERCICIOS) AND a.idUsuario = :USUARIO');
            $query->orderBy('a.fecha', 'DESC');
            $query->setParameters(['EJERCICIOS' => array_values($EJERCICIOS), 'USUARIO' => $USUARIO]);
            $ENTREGAS = $query->getQuery()->getResult();
            if (count($ENTREGAS)) {
                foreach ($ENTREGAS as $ENTREGA) {
                    if (!Ejercicio::esEjercicioDistrito($doctrine, $ENTREGA->getIdEjercicio())) {
                        return $ENTREGA;
                    }
                }
            }
            return 0;
        }
        if (!count($ENTREGAS)) {
            return 0;
        }
        return $ENTREGAS[0];
    }

    static function numeroSolicitantes($doctrine, $EJERCICIO, $DISTRITO) {
        $estadoSolicitado = $doctrine->getRepository('AppBundle:EjercicioEstado')->findOneByEstado('solicitado');
        $usuariosDistrito = Distrito::getCiudadanosVivosDistrito($doctrine, $DISTRITO);

        $query = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->createQueryBuilder('a');
        $query->select('COUNT(a)');
        $query->where('a.idEjercicio = :EJERCICIO AND a.idUsuario IN (:USUARIOS) AND a.idEjercicioEstado = :ESTADO');
        $query->orderBy('a.fecha', 'DESC');
        $query->setParameters(['EJERCICIO' => $EJERCICIO, 'USUARIOS' => array_values($usuariosDistrito), 'ESTADO' => $estadoSolicitado]);
        $SOLICITUDES = $query->getQuery()->getSingleScalarResult();

        return $SOLICITUDES;
    }

}
