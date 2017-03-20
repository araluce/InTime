<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Utils\Usuario;
use AppBundle\Utils\Utils;
use AppBundle\Utils\Trabajo;
use AppBundle\Utils\Distrito;
use AppBundle\Utils\Pago;
use AppBundle\Utils\Ejercicio;
use AppBundle\Runtastic\Runtastic;

/**
 * Description of CronController
 *
 * @author araluce
 */
class CronController extends Controller {

    /**
     * @Route("/cron/jornadaLaboral", name="cronJornadaLaboral")
     */
    public function cronJornadaLaboralAction(Request $request) {
        $doctrine = $this->getDoctrine();
        Utils::setError($doctrine, 3, 'CRON - Jornada Laboral');
        $CIUDADANOS = Usuario::getCiudadanosVivos($doctrine);
        $n = 0;
        if (count($CIUDADANOS)) {
            foreach ($CIUDADANOS as $CIUDADANO) {
                $RES = Trabajo::comprobarJornadaLaboral($doctrine, $CIUDADANO);
                if ($RES) {
                    $TDV_JORNADA_LABORAL = Utils::getConstante($doctrine, 'jornada_laboral');
                    Usuario::operacionSobreTdV($doctrine, $CIUDADANO, $TDV_JORNADA_LABORAL, 'Ingreso - Jornada Laboral');
                    $n++;
                }
            }
        }
        return new JsonResponse(json_encode(array(
                    'estado' => 'OK',
                    'message' => $n . ' ciudadano/s han cobrado su Jornada Laboral'
                )), 200);
    }

    /**
     * @Route("/cron/checkTdV", name="cronCheckTdV")
     */
    public function checkTdVAction(Request $request) {
        $doctrine = $this->getDoctrine();
        Utils::setError($doctrine, 3, 'CRON - Tiempo de vida');
        $CIUDADANOS = Usuario::getCiudadanosVivos($doctrine);
        $Fecha = new \DateTime('now');
        $n = 0;
        if (count($CIUDADANOS)) {
            foreach ($CIUDADANOS as $CIUDADANO) {
                if ($CIUDADANO->getIdCuenta()->getTdv() < $Fecha) {
                    Usuario::setDefuncion($doctrine, $CIUDADANO);
                    $n++;
                }
            }
        }
        return new JsonResponse(json_encode(array(
                    'estado' => 'OK',
                    'message' => $n . ' ciudadano/s declarados fallecidos'
                )), 200);
    }

    /**
     * @Route("/cron/pagarFinDeSemana", name="pagarFinDeSemana")
     */
    public function pagarFinDeSemanaAction(Request $request) {
        $doctrine = $this->getDoctrine();
//        Utils::setError($doctrine, 3, 'CRON - Pago fines de semana');
//        $CIUDADANOS = Usuario::getCiudadanosVivos($doctrine);
//        if (count($CIUDADANOS)) {
//            foreach ($CIUDADANOS as $CIUDADANO) {
//                Usuario::operacionSobreTdV($doctrine, $CIUDADANO, 172800, 'Ajuste - Fin de semana');
//            }
//        }
        return new JsonResponse(json_encode(array('estado' => 'OK')), 200);
    }

    /**
     * @Route("/cron/cobrarCuotaPrestamo", name="cronCobrarCuotaPrestamo")
     */
    public function cobrarCuotaPrestamoAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $prestamo = "prestamo";
        $em = $doctrine->getManager();
        $query = $doctrine->getRepository('AppBundle:UsuarioPrestamo')->createQueryBuilder('a');
        $query->select('a');
        $query->where('a.restante > 0 AND a.motivo = :PRESTAMO');
        $query->setParameters(["PRESTAMO" => $prestamo]);
        $PRESTAMOS = $query->getQuery()->getResult();
        $recaudado = 0;
        foreach ($PRESTAMOS as $PRESTAMO) {
            $interes = $PRESTAMO->getInteres();
            $cuota = ($PRESTAMO->getCantidad() + ($PRESTAMO->getCantidad() * $interes)) / 4;
            $recaudado += $cuota;
            $restante = $PRESTAMO->getRestante() - $cuota;
            $CIUDADANO = $PRESTAMO->getIdUsuario();
            Usuario::operacionSobreTdV($doctrine, $CIUDADANO, (-1) * $cuota, 'Cobro - Cuota semanal por préstamo pendiente');
            $PRESTAMO->setRestante($restante);
            $em->persist($PRESTAMO);
        }
        $em->flush();
        Utils::setError($doctrine, 3, 'CRON - Cobrar la cuota de préstamo (' . $recaudado . ')');

        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => 'Se ha recaudado un total de ' . $recaudado . ' segundos')), 200);
    }

    /**
     * @Route("/cron/comprobarAlimentacion", name="cronComprobarAlimentacion")
     */
    public function comprobarAlimentacionAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $CIUDADANOS = Usuario::getCiudadanosVivos($doctrine);
        $contador = 0;
        $fecha = new \DateTime('now');
        $intervaloComida = Utils::getConstante($doctrine, "tiempo_acabar_de_comer");
        $intervaloBebida = Utils::getConstante($doctrine, "tiempo_acabar_de_beber");
        $topeComida = $fecha->getTimestamp();
        $topeBebida = $fecha->getTimestamp();
        if (count($CIUDADANOS)) {
            foreach ($CIUDADANOS as $CIUDADANO) {
                // Ya está contemplado el usuario con vacaciones
                if ($CIUDADANO->getTiempoSinComer()->getTimestamp() + $intervaloComida < $topeComida ||
                        $CIUDADANO->getTiempoSinBeber()->getTimestamp() + $intervaloBebida < $topeBebida) {
                    Usuario::setDefuncion($doctrine, $CIUDADANO);
                    $contador++;
                }
            }
        }
        $em->flush();
        Utils::setError($doctrine, 3, 'CRON - Comprobar alimentación');
        if ($contador) {
            return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $contador . ' ciudadanos han fallecido de inanición')), 200);
        } else {
            return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => 'Todos los ciudadanos están bien alimentados')), 200);
        }
    }

    /**
     * @Route("/cron/comprobarVacaciones", name="cronComprobarVacaciones")
     */
    public function comprobarVacacionesAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $CIUDADANOS = Usuario::getCiudadanosVacaciones($doctrine);
        $fecha = new \DateTime('now');
        $diaSemana = date("w", $fecha->getTimestamp());
        $contador = 0;
        if (count($CIUDADANOS)) {
            foreach ($CIUDADANOS as $CIUDADANO) {
                $CUENTA = $CIUDADANO->getIdCuenta();
                if ($diaSemana !== '6' && $diaSemana !== '0') {
                    if ($CUENTA->getFinbloqueo() < $fecha) {
                        $ESTADO = $doctrine->getRepository('AppBundle:UsuarioEstado')->findOneByNombre('Activo');
                        $CIUDADANO->setIdEstado($ESTADO);
                        $em->persist($CIUDADANO);
                        $contador++;
                    }
                } else {
                    $finBloqueo = $CUENTA->getFinbloqueo();
                    $finBloqueo->add(new \DateInterval('P1D'));
                    $CUENTA->setFinbloqueo($finBloqueo);
                    $em->persist($CUENTA);
                }
            }
        }
        $em->flush();
        Utils::setError($doctrine, 3, 'CRON - Comprobar vacaciones');
        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $contador . ' ciudadanos reincorporados' . $diaSemana)), 200);
    }

    /**
     * @Route("/cron/comprobarRetosDeportivos", name="comprobarRetosDeportivos")
     */
    public function comprobarRetosDeportivosAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $CIUDADANOS = Usuario::getCiudadanosVivos($doctrine);
        $contador = 0;
        if (count($CIUDADANOS)) {
            foreach ($CIUDADANOS as $CIUDADANO) {
                // Actualizar sus sesiones
                $CUENTAS_RUNTASTIC = $doctrine->getRepository('AppBundle:UsuarioRuntastic')->findByIdUsuario($CIUDADANO);
                if (!count($CUENTAS_RUNTASTIC)) {
                    return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Fallo al actualizar la información'), 200);
                }
                $actividades_semana = [];
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
                        echo $r->getResponseStatusCode();
                        $week_activities = $r->getActivities($hoy->format('W') - 1);
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
                            $SESION->setRitmo($activity->pace);
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

                // Comprobación de las sesiones
                $ok = false;
                if (count($CUENTAS_RUNTASTIC)) {
                    $DEPORTE = $doctrine->getRepository('AppBundle:EjercicioSeccion')->findOneBySeccion('deporte');
                    $EJERCICIO = $doctrine->getRepository('AppBundle:Ejercicio')->findOneByIdEjercicioSeccion($DEPORTE);
                    if (null !== $EJERCICIO) {
                        $comparar = 1;
                        $RETOS = $doctrine->getRepository('AppBundle:EjercicioRuntastic')->findByIdEjercicio($EJERCICIO);
                        if (!count($RETOS)) {
                            $comparar = 0;
                        }
                        $duracion_acumulada = 0;
                        $id_sesiones = [];
                        $n_sesiones = 1;
                        foreach ($CUENTAS_RUNTASTIC as $CUENTA) {
                            $SESIONES_RUNTASTIC = $doctrine->getRepository('AppBundle:SesionRuntastic')->findByIdUsuarioRuntastic($CUENTA);
                            foreach ($SESIONES_RUNTASTIC as $SESION) {
                                if (!$ok) {
                                    if (Utils::semanaPasada($SESION->getFecha())) {
                                        $duracion = $SESION->getDuracion();
                                        if ($comparar) {
                                            if (!$SESION->getEvaluado()) {
                                                foreach ($RETOS as $RETO) {
                                                    if ($RETO->getTipo() === 'running') {
                                                        if (($RETO->getTipo() === $SESION->getTipo() && $RETO->getRitmo() >= $SESION->getRitmo())) {
                                                            $duracion_acumulada += $duracion;
                                                            $id_sesiones[] = $SESION;
                                                            if ($duracion_acumulada >= $RETO->getDuracion()) {
                                                                $n_sesiones++;
                                                                if ($n_sesiones >= 2) {
                                                                    $ok = true;
                                                                    $contador++;
                                                                    Ejercicio::evaluaFasePartes($doctrine, $EJERCICIO, $CIUDADANO, $id_sesiones);
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
                                                                    $contador++;
                                                                    Ejercicio::evaluaFasePartes($doctrine, $EJERCICIO, $CIUDADANO, $id_sesiones);
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
                        }
                    }
                }
            }
        }
        $em->flush();
        Utils::setError($doctrine, 3, 'CRON - Comprobar retos deportivos - ' . $contador);
        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $contador . ' ciudadanos han superado el reto deportivo')), 200);
    }

    /**
     * @Route("/cron/pagoMina", name="cronMina")
     */
    public function cronMinaAction(Request $request) {
        // Cada noche después de las 00:00
        $doctrine = $this->getDoctrine();
        $CIUDADANOS = Usuario::getCiudadanosVivos($doctrine);
        $ULTIMA_MINA = Utils::ultimaMinaDesactivada($doctrine);
        $MINA_ACTIVA = Utils::minaActiva($doctrine);
        $HOY = new \DateTime('now');
        $acertantes = 0;
        $return = 'CRON - Hoy no ha habido mina o está activa en este momento';
        if ($ULTIMA_MINA) {
            if ($MINA_ACTIVA && $MINA_ACTIVA === $ULTIMA_MINA) {
                $return = 'CRON - La mina sigue activa';
            } else {
                if (intval($ULTIMA_MINA->getFechaFinal()->format('d')) === intval($HOY->format('d') - 1)) {
                    $return = 'CRON - No ha habido acertantes';
                    $query = $doctrine->getRepository('AppBundle:UsuarioMina')->createQueryBuilder('a');
                    $query->select('a');
                    $query->where('a.idMina = :MINA');
                    $query->setParameters(['MINA' => $ULTIMA_MINA]);
                    $GANADORES = $query->getQuery()->getResult();
                    if (count($GANADORES)) {
                        $GANADORES_USU = [];
                        foreach ($GANADORES as $G) {
                            $GANADORES_USU[] = $G->getIdUsuario();
                        }
                        if (count($CIUDADANOS)) {
                            foreach ($CIUDADANOS as $CIUDADANO) {
                                if (in_array($CIUDADANO, $GANADORES_USU)) {
                                    Pago::pagarMina($doctrine, $ULTIMA_MINA, $CIUDADANO, count($GANADORES), true);
                                    $acertantes++;
                                } else {
                                    Pago::pagarMina($doctrine, $ULTIMA_MINA, $CIUDADANO, count($GANADORES));
                                }
                            }
                            $return = 'CRON - ' . $acertantes . ' acertantes en la mina';
                        }
                    }
                }
            }
        }
        Utils::setError($doctrine, 3, $return);
        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $return)), 200);
    }

    /**
     * @Route("/cron/comprobarNivelCiudadanos", name="comprobarNivelCiudadanos")
     */
    public function comprobarNivelCiudadanosAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $CIUDADANOS = Usuario::getCiudadanosVivos($doctrine);
        $contador = 0;
        if (count($CIUDADANOS)) {
            foreach ($CIUDADANOS as $CIUDADANO) {
                if (Usuario::comprobarNivel($doctrine, $CIUDADANO)) {
                    $contador++;
                }
            }
        }
        Utils::setError($doctrine, 3, 'CRON - ' . $contador . ' ciudadanos han subido de nivel');
        return new JsonResponse(json_encode($contador . ' ciudadanos han subido de nivel'), 200);
    }

    /**
     * @Route("/cron/ciudadanosDistrito", name="testCiudadanos")
     */
    public function testCiudadanos(Request $request) {
        $doctrine = $this->getDoctrine();
        $DISTRITOS = $doctrine->getRepository('AppBundle:UsuarioDistrito')->findAll();
        $RESPUESTA = [];
        foreach ($DISTRITOS as $DISTRITO) {
            $CIUDADANOS = Distrito::getCiudadanosVivosDistrito($doctrine, $DISTRITO);
            $aux = [];
            $aux['DISTRITO'] = $DISTRITO->getNombre();
            $aux['CIUDADANOS'] = [];
            if (count($CIUDADANOS)) {
                foreach ($CIUDADANOS as $CIUDADANO) {
                    $aux2 = [];
                    $aux2['NOMBRE'] = $CIUDADANO->getNombre();
                    $aux['CIUDADANOS'][] = $aux2;
                }
            }
            $RESPUESTA[] = $aux;
        }
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario(9);
        $resp = Usuario::dejarHerencia($doctrine, $USUARIO);
        echo 'Número de compañeros:   [' . $resp . ']';
//        Utils::pretty_print($RESPUESTA);
//        return new JsonResponse(json_encode($RESPUESTA), 200);
    }

}
