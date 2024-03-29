<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Utils;

use AppBundle\Entity\EjercicioCalificacion;
use AppBundle\Entity\EjercicioXUsuario;
use AppBundle\Utils\Distrito;
use DateTime;

/**
 * Métodos que gestionan los retos de la app
 *
 * @author araluce
 */
class Ejercicio {

    /**
     * Actualiza la lista de ejercicios del usuario
     * 
     * @param type $doctrine
     * @param <AppBundle\Entity\Usuario> $USUARIO
     */
    static function actualizarEjercicioXUsuario($doctrine, $USUARIO) {
        $em = $doctrine->getManager();
        $EJERCICIOS_USUARIO = $doctrine->getRepository('AppBundle:EjercicioXUsuario')->findByIdUsu($USUARIO);
        $EJERCICIOS_ACTUALES = [];
        if (count($EJERCICIOS_USUARIO)) {
            foreach ($EJERCICIOS_USUARIO as $EJERCICIO) {
                $EJERCICIOS_ACTUALES[] = $EJERCICIO->getIdEjercicio();
            }
        }
        $EJERCICIOS = $doctrine->getRepository('AppBundle:Ejercicio')->findAll();
        if (count($EJERCICIOS)) {
            foreach ($EJERCICIOS as $EJERCICIO) {
                if (!in_array($EJERCICIO, $EJERCICIOS_ACTUALES)) {
                    $EJERCICIO_X_USUARIO = new EjercicioXUsuario();
                    $EJERCICIO_X_USUARIO->setIdSeccion($EJERCICIO->getIdEjercicioSeccion());
                    $EJERCICIO_X_USUARIO->setIdUsu($USUARIO);
                    $EJERCICIO_X_USUARIO->setIdEjercicio($EJERCICIO);
                    $EJERCICIO_X_USUARIO->setVisto(0);
                    $em->persist($EJERCICIO_X_USUARIO);
                }
            }
        }
        $em->flush();
    }

    /**
     * Comprueba si un ejercicio es de distrito o no
     * @param type $doctrine
     * @param <AppBundle\Entity\Ejericicio> $EJERCICIO
     * @return true|false
     */
    static function esEjercicioDistrito($doctrine, $EJERCICIO) {
        $val = $doctrine->getRepository('AppBundle:EjercicioDistrito')->findOneByIdEjercicio($EJERCICIO);
        if ($val === null)
            return false;
        return true;
    }

    /**
     * Obtiene el ejercicio de tipo deportivo más bajo no superado
     * @param type $doctrine
     * @param <AppBundle\Entity\Usuario> $USUARIO
     * @return int|<AppBundle\Entity\Ejercicio>
     */
    static function getFase($doctrine, $USUARIO) {
        $DEPORTE = $doctrine->getRepository('AppBundle:EjercicioSeccion')->findOneBySeccion('deporte');
        $EJERCICIOS = $doctrine->getRepository('AppBundle:Ejercicio')->findByIdEjercicioSeccion($DEPORTE);
        if (!count($EJERCICIOS)) {
            return 0;
        }
        $fase_min = 1000;
        $RETO = null;
        foreach ($EJERCICIOS as $EJERCICIO) {
            $CALIFICACION = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findBy([
                'idEjercicio' => $EJERCICIO, 'idUsuario' => $USUARIO
            ]);
            if (!count($CALIFICACION) && $fase_min > intval($EJERCICIO->getEnunciado())) {
                $RETO = $EJERCICIO;
                $fase_min = intval($EJERCICIO->getEnunciado());
            } else {
                if (count($CALIFICACION)) {
                    foreach ($CALIFICACION as $C) {
                        if (!Utils::estaSemana($C->getFecha()) && $fase_min > intval($EJERCICIO->getEnunciado())) {
                            $RETO = $EJERCICIO;
                            $fase_min = intval($EJERCICIO->getEnunciado());
                        }
                    }
                }
            }
        }
        return $RETO;
    }

    /**
     * Evalua e ingresa el beneficio obtenido al superar una fase de un reto
     * deportivo
     * @param type $doctrine
     * @param <AppBundle\Entity\Ejercicio> $EJERCICIO Reto deportivo
     * @param <AppBundle\Entity\Usuario> $USUARIO Ciudadano
     * @param array(<AppBundle\Entity\RuntasticSesion>) $SESIONES
     * @param boolean $beneficioDoble
     */
    static function evaluaFasePartes($doctrine, $EJERCICIO, $USUARIO, $SESIONES, $beneficioDoble = false) {
        $em = $doctrine->getManager();
        $EVALUADO = $doctrine->getRepository('AppBundle:EjercicioEstado')->findOneByEstado('evaluado');
        $NOTA = $doctrine->getRepository('AppBundle:EjercicioBonificacion')->findOneByIdEjercicio($EJERCICIO);
        $CALIFICACION = new EjercicioCalificacion();
        $CALIFICACION->setFecha(new DateTime('now'));
        $CALIFICACION->setIdCalificaciones($NOTA->getIdCalificacion());
        $CALIFICACION->setIdEjercicio($EJERCICIO);
        $CALIFICACION->setIdEjercicioEstado($EVALUADO);
        $CALIFICACION->setIdUsuario($USUARIO);
        $em->persist($CALIFICACION);

        foreach ($SESIONES as $SESION) {
            $SESION->setEvaluado(1);
            $em->persist($SESION);
        }
        $em->flush();
        if ($beneficioDoble) {
            Usuario::operacionSobreTdV($doctrine, $USUARIO, 2 * $NOTA->getBonificacion(), 'Ingreso - Reto deportivo superado (beneficio doble).');
        } else {
            Usuario::operacionSobreTdV($doctrine, $USUARIO, $NOTA->getBonificacion(), 'Ingreso - Reto deportivo superado.');
        }
    }

    /**
     * Nos devuelve la primera entrega de un ejercicio, 0 si nunca ha sido
     * entregado el ejercicio
     * @param type $doctrine
     * @param <AppBundle\Entity\Usuario> $USUARIO
     * @param <AppBundle\Entity\Ejercicio> $EJERCICIO
     * @param type $DISTRITO
     * @return int
     */
    static function datosReentrega($doctrine, $USUARIO, $EJERCICIO, $DISTRITO) {
        $ENTREGA = $doctrine->getRepository('AppBundle:EjercicioEntrega')->findOneBy([
            'idUsuario' => $USUARIO, 'idEjercicio' => $EJERCICIO
        ]);
        if (null === $ENTREGA) {
            if (null === $DISTRITO) {
                // El usuario no ha realizado ninguna entrega de este ejercicio
                // y no hay entregas de otros usuarios del mismo distrito
                // => No es reentrega, es una entrega nueva
                return 0;
            }
            // Como es de distrito y no lo he entregado yo buscamos al que
            // entregó la primera vez
            $CIUDADANOS = Distrito::getCiudadanosDistrito($doctrine, $DISTRITO);
            if (!count($CIUDADANOS)) {
                return 0;
            }
            foreach ($CIUDADANOS as $CIUDADANO) {
                $ENTREGA = $doctrine->getRepository('AppBundle:EjercicioEntrega')->findOneBy([
                    'idUsuario' => $CIUDADANO, 'idEjercicio' => $EJERCICIO
                ]);
                if (null !== $ENTREGA) {
                    return $ENTREGA;
                }
            }
            // No se ha encontrado ninguna entrega por parte de ningún miembro
            // del distrito
            return 0;
        }
        return $ENTREGA;
    }

    /**
     * Marca a vistos los retos de paga extra
     * @param type $doctrine
     * @param <AppBundle\Entity\Usuario> $USUARIO
     */
    static function actualizarPagaVisto($doctrine, $USUARIO) {
        $em = $doctrine->getManager();
        $SECCION_PAGA = $doctrine->getRepository('AppBundle:EjercicioSeccion')->findOneBySeccion('paga_extra');
        $NO_VISTOS = $doctrine->getRepository('AppBundle:EjercicioXUsuario')->findBy([
            'idSeccion' => $SECCION_PAGA, 'idUsu' => $USUARIO, 'visto' => 0
        ]);
        if (count($NO_VISTOS)) {
            foreach ($NO_VISTOS as $EJERCICIO) {
                $EJERCICIO->setVisto(1);
                $em->persist($EJERCICIO);
            }
            $em->flush();
        }
    }

}
