<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Utils;

use AppBundle\Runtastic\Runtastic;

/**
 * Clase de utilidades para manejar el servicio Runtastic
 *
 * @author araluce
 */
class RuntasticUtils {

    /**
     * Actualiza las sesiones de Runtastic de un ciudadano en particular
     * @param doctrine $doctrine
     * @param array(\AppBundle\Entity\UsuarioRuntastic) $CUENTAS_RUNTASTIC Un array de las cuentas que tiene el usuario array<\AppBundle\Entity\UsuarioRuntastic>
     * @return array Actividades de la semana
     */
    static function actualizarSesionesRuntastic($doctrine, $CUENTAS_RUNTASTIC) {
        $em = $doctrine->getManager();
        $actividades_semana = [];
        if (count($CUENTAS_RUNTASTIC)) {
            foreach ($CUENTAS_RUNTASTIC as $U) {
                $SESIONES = $doctrine->getRepository('AppBundle:SesionRuntastic')->findByIdUsuarioRuntastic($U);
                $array_sesiones = [];
                if (count($SESIONES)) {
                    foreach ($SESIONES as $S) {
                        $array_sesiones[] = $S->getIdRuntastic();
                    }
                }
                $r = new Runtastic();
                $timeout = false;
                $tiempo_inicio = microtime(true);
                $hoy = new \DateTime('now');
                do {
                    $r->setUsername($U->getUsername())->setPassword($U->getPassword());
                    $week_activities = $r->getActivities($hoy->format('W'));
                    $tiempo_fin = microtime(true);
                    $tiempo = $tiempo_fin - $tiempo_inicio;
                    if ($tiempo >= 10.0) {
                        $timeout = true;
                    }
                } while ($r->getResponseStatusCode() !== 200 && !$timeout);
                $response['usuario'] = $r->getUsername();
                $response['Uid'] = $r->getUid();
                foreach ($week_activities as $activity) {
                    $actividades_semana[] = $activity;
                    if (!in_array($activity->id, $array_sesiones)) {
                        $SESION = new \AppBundle\Entity\SesionRuntastic();
                        $SESION->setIdRuntastic($activity->id);
                        $SESION->setIdUsuarioRuntastic($U);
                        $SESION->setTipo('cycling');
                        if ($activity->type === 'running') {
                            $SESION->setTipo('running');
                        }
                        $SESION->setDuracion(Utils::milisegundosToSegundos($activity->duration));
                        $SESION->setDistancia($activity->distance);
                        $SESION->setRitmo($activity->pace - 0.5);
                        $SESION->setVelocidad($activity->speed);
                        $SESION->setEvaluado(0);
                        $FECHA = new \Datetime();
                        $FECHA->setDate($activity->date->year, $activity->date->month, $activity->date->day);
                        $FECHA->setTime($activity->date->hour, $activity->date->minutes, $activity->date->seconds);
                        $SESION->setFecha($FECHA);
                        $em->persist($SESION);
                    }
                }
                $em->flush();
            }
        }
        return $actividades_semana;
    }

    /**
     * Comprueba si un ciudadano ha superado un reto deportivo
     * @param doctrine $doctrine $this->getDoctrine()
     * @param array(\AppBundle\Entity\UsuarioRuntastic) $CUENTAS_RUNTASTIC Un array de las cuentas que tiene el usuario array<\AppBundle\Entity\UsuarioRuntastic>
     * @param \AppBundle\Entity\Ejercicio $EJERCICIO El ejercicio asociado al reto deportivo
     * @return array ['OK', 'DURACION_ACUMULADA', 'DURACION_RETO', 'ID_SESIONES']
     */
    static function comprobarRetoDeportivo($doctrine, $CUENTAS_RUNTASTIC, $EJERCICIO) {
        $RETOS = $doctrine->getRepository('AppBundle:EjercicioRuntastic')->findByIdEjercicio($EJERCICIO);
        $comparar = 1;
        if (!count($RETOS)) {
            $comparar = 0;
        }
        $duracion_acumulada = 0;
        $id_sesiones = [];
        $n_sesiones = 1;
        $ok = false;
        $duracionReto = 0;
        foreach ($CUENTAS_RUNTASTIC as $CUENTA) {
            $SESIONES_RUNTASTIC = $doctrine->getRepository('AppBundle:SesionRuntastic')->findByIdUsuarioRuntastic($CUENTA);
            foreach ($SESIONES_RUNTASTIC as $SESION) {
                if (Utils::semanaPasada($SESION->getFecha())) {
                    $duracion = $SESION->getDuracion();
                    if ($comparar) {
                        if (!$SESION->getEvaluado()) {
                            foreach ($RETOS as $RETO) {
                                $duracionReto = $RETO->getDuracion();
                                if ($RETO->getTipo() === 'running') {
                                    if (($RETO->getTipo() === $SESION->getTipo() && $RETO->getRitmo() >= $SESION->getRitmo())) {
                                        $duracion_acumulada += $duracion;
                                        $id_sesiones[] = $SESION;
                                        if ($duracion_acumulada >= $RETO->getDuracion()) {
                                            $n_sesiones++;
                                            if ($n_sesiones >= 2) {
                                                $ok = true;
                                            }
                                        }
                                    }
                                }
                                if ($RETO->getTipo() === 'cycling') {
                                    if ($RETO->getTipo() === $SESION->getTipo() && $RETO->getVelocidad() <= $SESION->getVelocidad()) {
                                        $duracion_acumulada += $duracion;
                                        $id_sesiones[] = $SESION;
                                        if ($duracion_acumulada >= $RETO->getDuracion()) {
                                            $n_sesiones++;
                                            if ($n_sesiones >= 2) {
                                                $ok = true;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return ['OK' => $ok, 'DURACION_ACUMULADA' => $duracion_acumulada, 'DURACION_RETO' => $duracionReto, 'ID_SESIONES' => $id_sesiones];
    }

}
