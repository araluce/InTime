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
        $VACACIONES = $doctrine->getRepository('AppBundle:UsuarioEstado')->findOneByNombre("Vacaciones");
        if (count($CIUDADANOS)) {
            foreach ($CIUDADANOS as $CIUDADANO) {
                // Ciudadano en vacaciones
                if ($CIUDADANO->getIdEstado() === $VACACIONES) {
                    $CIUDADANO->setTiempoSinComer(new \DateTime('now'));
                    $CIUDADANO->setTiempoSinBeber(new \DateTime('now'));
                    $em->persist($CIUDADANO);
                    $em->flush();
                }
                if ($CIUDADANO->getTiempoSinComer()->getTimestamp() + $intervaloComida < $topeComida ||
                        $CIUDADANO->getTiempoSinBeber()->getTimestamp() + $intervaloBebida < $topeBebida) {
                    Usuario::setDefuncion($doctrine, $CIUDADANO);
                    $contador++;
                }
            }
        }
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
//                if ($diaSemana !== '6' && $diaSemana !== '0') {
                    if ($CUENTA->getFinbloqueo() < $fecha) {
                        $ESTADO = $doctrine->getRepository('AppBundle:UsuarioEstado')->findOneByNombre('Activo');
                        $CIUDADANO->setIdEstado($ESTADO);
                        $em->persist($CIUDADANO);
                        $contador++;
                    }
//                } else {
//                    $CUENTA = $USUARIO->getIdCuenta();
//                    $finBloqueo = new \DateTime('now');
//                    $finBloqueo->add(new \DateInterval('P1D'));
//                    $CUENTA->setFinbloqueo($finBloqueo);
//                    $em->persist($CUENTA);
//                }
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
        $MC_DEPORTE = $doctrine->getRepository('AppBundle:BonificacionExtra')->findOneByIdBonificacionExtra(10);
        $contador = 0;
        if (count($CIUDADANOS)) {
            foreach ($CIUDADANOS as $CIUDADANO) {
                $MC_COMPRADA = $doctrine->getRepository('AppBundle:BonificacionXUsuario')->findOneBy([
                    'idUsuario' => $CIUDADANO, 'idBonificacionExtra' => $MC_DEPORTE, 'usado' => 0
                ]);
                // Actualizar sus sesiones
                $CUENTAS_RUNTASTIC = $doctrine->getRepository('AppBundle:UsuarioRuntastic')->findByIdUsuario($CIUDADANO);
                if (count($CUENTAS_RUNTASTIC)) {
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
                        if ($ok) {
                            $contador++;
                            if (null !== $MC_COMPRADA && $duracion_acumulada >= ($duracionReto * 2)) {
                                Ejercicio::evaluaFasePartes($doctrine, $EJERCICIO, $CIUDADANO, $id_sesiones, true);
                                $MC_COMPRADA->setUsado(1);
                                $em->persist($MC_COMPRADA);
                                $em->flush();
                            } else {
                                Ejercicio::evaluaFasePartes($doctrine, $EJERCICIO, $CIUDADANO, $id_sesiones);
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
     * 
     * @Route("/cron/bonificarClasificaciones", name="bonificarClasificaciones")
     */
    public function bonificarClasificacionesAction() {
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();

        // Obtenemos los tres primeros distritos
        $clasificacionDistritos = Usuario::getClasificacionDistritosMes($doctrine);
        $DISTRITO_PRIMERO = null;
        $DISTRITO_SEGUNDO = null;
        $DISTRITO_TERCERO = null;
        // Necesitamos sólo los 3 primeros distritos
        if (count($clasificacionDistritos) >= 3) {
            $DISTRITO_PRIMERO = $doctrine->getRepository('AppBundle:UsuarioDistrito')->findOneByNombre($clasificacionDistritos[0]['DISTRITO']);
            $DISTRITO_SEGUNDO = $doctrine->getRepository('AppBundle:UsuarioDistrito')->findOneByNombre($clasificacionDistritos[1]['DISTRITO']);
            $DISTRITO_TERCERO = $doctrine->getRepository('AppBundle:UsuarioDistrito')->findOneByNombre($clasificacionDistritos[2]['DISTRITO']);
        }

        $ROL_JUGADOR = $doctrine->getRepository('AppBundle:Rol')->findOneByNombre('Jugador');
        $CIUDADANOS = $doctrine->getRepository('AppBundle:Usuario')->findByIdRol($ROL_JUGADOR);
        $listaCiudadanosPremiados = [];
        if (count($CIUDADANOS)) {
            foreach ($CIUDADANOS as $CIUDADANO) {
                $globalBonificado = false;
                $miListaDePremios = [
                    '1erGlobal' => 0,
                    '2doGlobal' => 0,
                    '3eroGlobal' => 0,
                    '1eroMiDistrito' => 0,
                    '1erDistrito' => 0,
                    '2doDistrito' => 0,
                    '3eroDistrito' => 0
                ];
                // Obtenemos todos los ciudadanos menos yo
                // y obtenemos nuestra clasificación entre ese conjunto de 
                // usuarios (clasificación global)
                $qb = $em->createQueryBuilder();
                $query = $qb->select('u')
                        ->from('\AppBundle\Entity\Usuario', 'u')
                        ->where('u.idUsuario != :ID_USUARIO AND u.idRol = :ROL')
                        ->setParameters(['ID_USUARIO' => $CIUDADANO->getIdUsuario(), 'ROL' => $ROL_JUGADOR]);
                $RESTO_CIUDADANOS = $query->getQuery()->getResult();
                $clasificacionGlobal = Usuario::getClasificacionMes($doctrine, $CIUDADANO, $RESTO_CIUDADANOS);

                switch ($clasificacionGlobal['PUESTO']) {
                    case '1':
//                        $aux = [];
//                        $aux['causa'] = 'Ingreso - 1er puesto en la clasificación global';
//                        $aux['bonificacion'] = Utils::getConstante($doctrine, 'primeroGlobalMes');
//                        $miListaDePremios[] = $aux;
                        $miListaDePremios['1erGlobal'] = Utils::getConstante($doctrine, 'primeroGlobalMes');
                        break;
                    case '2':
//                        $aux = [];
//                        $aux['causa'] = 'Ingreso - 2do puesto en la clasificación global';
//                        $aux['bonificacion'] = Utils::getConstante($doctrine, 'segundoGlobalMes');
//                        $miListaDePremios[] = $aux;
                        $miListaDePremios['2doGlobal'] = Utils::getConstante($doctrine, 'segundoGlobalMes');
                        break;
                    case '3':
//                        $aux = [];
//                        $aux['causa'] = 'Ingreso - 3er puesto en la clasificación global';
//                        $aux['bonificacion'] = Utils::getConstante($doctrine, 'terceroGlobalMes');
//                        $miListaDePremios[] = $aux;
                        $miListaDePremios['3eroGlobal'] = Utils::getConstante($doctrine, 'terceroGlobalMes');
                        break;
                }

                // Obtenemos todos los ciudadanos de mi distrito menos yo
                // y obtenemos nuestra clasificación entre ese conjunto de 
                // usuarios (clasificación en el distrito)
                $qb = $em->createQueryBuilder();
                $query = $qb->select('u')
                        ->from('\AppBundle\Entity\Usuario', 'u')
                        ->where('u.idUsuario != :ID_USUARIO AND u.idRol = :ROL AND u.idDistrito = :DISTRITO')
                        ->setParameters([
                    'ID_USUARIO' => $CIUDADANO->getIdUsuario(),
                    'ROL' => $ROL_JUGADOR,
                    'DISTRITO' => $CIUDADANO->getIdDistrito()
                ]);
                $CIUDADANOS_DE_MI_DISTRITO = $query->getQuery()->getResult();
                $clasificacionEnDistrito = Usuario::getClasificacionMes($doctrine, $CIUDADANO, $CIUDADANOS_DE_MI_DISTRITO);
                if ($clasificacionEnDistrito['PUESTO'] === 1) {
//                    $aux = [];
//                    $aux['causa'] = 'Ingreso - 1er puesto en tu distrito';
//                    $aux['bonificacion'] = Utils::getConstante($doctrine, 'primeroEnDistritoMes');
//                    $miListaDePremios[] = $aux;
                    $miListaDePremios['1eroMiDistrito'] = Utils::getConstante($doctrine, 'primeroEnDistritoMes');
                }
//                $aux = [];
//                $aux['ciudadano'] = $CIUDADANO->getSeudonimo();
//                $aux['clasificacion global'] = $clasificacionGlobal['PUESTO'];
//                $aux['clasificacion distrito'] = $clasificacionEnDistrito['PUESTO'];
//                Utils::pretty_print($aux);
                // Obtenemos posibles bonificaciones por la clasificación
                // de nuestro distrito
                if ($CIUDADANO->getIdDistrito() === $DISTRITO_PRIMERO) {
//                    $aux = [];
//                    $aux['causa'] = 'Ingreso - Pertenencia al 1er distrito del mes';
//                    $aux['bonificacion'] = Utils::getConstante($doctrine, 'primerDistritoMes');
//                    $miListaDePremios[] = $aux;
                    $miListaDePremios['1erDistrito'] = Utils::getConstante($doctrine, 'primerDistritoMes');
                }
                if ($CIUDADANO->getIdDistrito() === $DISTRITO_SEGUNDO) {
//                    $aux = [];
//                    $aux['causa'] = 'Ingreso - Pertenencia al 2do distrito del mes';
//                    $aux['bonificacion'] = Utils::getConstante($doctrine, 'segundoDistritoMes');
//                    $miListaDePremios[] = $aux;
                    $miListaDePremios['2doDistrito'] = Utils::getConstante($doctrine, 'segundoDistritoMes');
                }
                if ($CIUDADANO->getIdDistrito() === $DISTRITO_TERCERO) {
//                    $aux = [];
//                    $aux['causa'] = 'Ingreso - Pertenencia al 3er distrito del mes';
//                    $aux['bonificacion'] = Utils::getConstante($doctrine, 'tercerDistritoMes');
//                    $miListaDePremios[] = $aux;
                    $miListaDePremios['3eroDistrito'] = Utils::getConstante($doctrine, 'tercerDistritoMes');
                }

//                $aux = [];
//                $aux['ciudadano'] = $CIUDADANO->getSeudonimo();
//                $aux['premios'] = $miListaDePremios;
//                Utils::pretty_print($aux);
                // En este punto ya tenemos todas las posibles bonificaciones
                // ahora bonificamos la más alta
//                if (count($miListaDePremios)) {
//                    $miBonificacionMasAlta = 0;
//                    $aux = [];
//                    foreach ($miListaDePremios as $bonificacion) {
//                        if ($bonificacion['bonificacion'] > $miBonificacionMasAlta) {
//                            $miBonificacionMasAlta = $bonificacion['bonificacion'];
//                            $aux['ciudadano'] = $CIUDADANO->getSeudonimo();
//                            $aux['causa'] = $bonificacion['causa'];
//                            $aux['bonificacion'] = $bonificacion['bonificacion'];
//                        }
//                    }
//                    $listaCiudadanosPremiados[] = $aux;
//                    Usuario::operacionSobreTdV($doctrine, $CIUDADANO, $aux['bonificacion'], $aux['causa']);
//                }
                // Se bonifican los premios globales
                if ($miListaDePremios['1erGlobal']) {
//                    $globalBonificado = true;
                    $listaCiudadanosPremiados[] = ['ciudadano' => $CIUDADANO->getSeudonimo(), 'causa' => '1er puesto en la clasificación global'];
                    Usuario::operacionSobreTdV($doctrine, $CIUDADANO, $miListaDePremios['1erGlobal'], 'Ingreso - 1er puesto en la clasificación global');
                } else if ($miListaDePremios['2doGlobal']) {
//                    $globalBonificado = true;
                    $listaCiudadanosPremiados[] = ['ciudadano' => $CIUDADANO->getSeudonimo(), 'causa' => '2do puesto en la clasificación global'];
                    Usuario::operacionSobreTdV($doctrine, $CIUDADANO, $miListaDePremios['2doGlobal'], 'Ingreso - 2do puesto en la clasificación global');
                } else if ($miListaDePremios['3eroGlobal']) {
                    $listaCiudadanosPremiados[] = ['ciudadano' => $CIUDADANO->getSeudonimo(), 'causa' => '3er puesto en la clasificación global'];
                    Usuario::operacionSobreTdV($doctrine, $CIUDADANO, $miListaDePremios['3eroGlobal'], 'Ingreso - 3er puesto en la clasificación global');
                }

                if (!$globalBonificado && $miListaDePremios['1eroMiDistrito']) {
                    $listaCiudadanosPremiados[] = ['ciudadano' => $CIUDADANO->getSeudonimo(), 'causa' => '1er puesto en tu distrito'];
                    Usuario::operacionSobreTdV($doctrine, $CIUDADANO, $miListaDePremios['1eroMiDistrito'], 'Ingreso - 1er puesto en tu distrito');
                }

                if ($miListaDePremios['1erDistrito']) {
                    $listaCiudadanosPremiados[] = ['ciudadano' => $CIUDADANO->getSeudonimo(), 'causa' => 'Pertenencia al 1er distrito del mes'];
                    Usuario::operacionSobreTdV($doctrine, $CIUDADANO, $miListaDePremios['1erDistrito'], 'Ingreso - Pertenencia al 1er distrito del mes');
                } else if ($miListaDePremios['2doDistrito']) {
                    $listaCiudadanosPremiados[] = ['ciudadano' => $CIUDADANO->getSeudonimo(), 'causa' => 'Pertenencia al 2do distrito del mes'];
                    Usuario::operacionSobreTdV($doctrine, $CIUDADANO, $miListaDePremios['2doDistrito'], 'Ingreso - Pertenencia al 2do distrito del mes');
                } else if ($miListaDePremios['3eroDistrito']) {
                    $listaCiudadanosPremiados[] = ['ciudadano' => $CIUDADANO->getSeudonimo(), 'causa' => 'Pertenencia al 3er distrito del mes'];
                    Usuario::operacionSobreTdV($doctrine, $CIUDADANO, $miListaDePremios['3eroDistrito'], 'Ingreso - Pertenencia al 3er distrito del mes');
                }
            }
        }

        $reporte = "";
        if (count($listaCiudadanosPremiados)) {
            foreach ($listaCiudadanosPremiados as $premio) {
                $reporte .= $premio['ciudadano'] . "=>" . $premio['causa'] . " ";
            }
        }
        Utils::setError($doctrine, 3, "Clasificaciones - " . $reporte);
//        Utils::pretty_print($listaCiudadanosPremiados);
        return new JsonResponse(json_encode(array('estado' => 'OK')), 200);
    }

    /**
     * @Route("/cron/koVacaciones", name="koVacaciones")
     */
//    public function koVacacionesAction(Request $request) {
//        $doctrine = $this->getDoctrine();
//        $RESPUESTA = [];
//        $CIUDADANOS = Usuario::getCiudadanosVacaciones($doctrine);
//        $FECHA = new \DateTime('now');
//        $cincoDias = 432000;
//        $contador = 0;
//        foreach($CIUDADANOS as $CIUDADANO){
//            Usuario::operacionSobreTdV($doctrine, $CIUDADANO, (-1) * $cincoDias, 'Cobro - Deuda con el metronomista');
//            $tdv = $CIUDADANO->getIdCuenta()->getTdv();
//            if ($tdv < $FECHA) {
//                $contador++;
//                Usuario::setDefuncion($doctrine, $CIUDADANO);
//            }
//        }
//        return new JsonResponse(json_encode($contador), 200);
//    }

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

    /**
     * @Route("/cron/controlMinicartas", name="controlMinicartas")
     */
    public function controlMinicartasAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $HOY = new \DateTime('now');
        $contador = 0;

        $libreMinuteros = $doctrine->getRepository('AppBundle:BonificacionExtra')->findOneByIdBonificacionExtra(3);
        $libreMinuterosCompradas = $doctrine->getRepository('AppBundle:BonificacionXUsuario')->findBy([
            'idBonificacionExtra' => $libreMinuteros, 'usado' => 0
        ]);
        if (count($libreMinuterosCompradas)) {
            foreach ($libreMinuterosCompradas as $MC) {
                if ($HOY->format('W') !== $MC->getFecha()->format('W')) {
                    $contador++;
                    $MC->setUsado(1);
                    $em->persist($MC);
                    $em->flush();
                }
            }
        }

        $paronNocturno = $doctrine->getRepository('AppBundle:BonificacionExtra')->findOneByIdBonificacionExtra(5);
        $paronNocturnoCompradas = $doctrine->getRepository('AppBundle:BonificacionXUsuario')->findBy([
            'idBonificacionExtra' => $paronNocturno, 'usado' => 0
        ]);
        if (count($paronNocturnoCompradas)) {
            foreach ($paronNocturnoCompradas as $MC) {
                if ($HOY->format('W') !== $MC->getFecha()->format('W')) {
                    $contador++;
                    $MC->setUsado(1);
                    $em->persist($MC);
                    $em->flush();
                }
            }
        }

        Utils::setError($doctrine, 3, "Control de minicartas - " . $contador);
        return new JsonResponse(json_encode($contador . " cartas fuera de circulación"), 200);
    }

    /**
     * @Route("/cron/paronNocturno", name="paronNocturno")
     */
    public function paronNocturnoAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $HOY = new \DateTime('now');
        $contador = 0;
        $sieteDias = 604800;
        $paronNocturno = $doctrine->getRepository('AppBundle:BonificacionExtra')->findOneByIdBonificacionExtra(5);

        $paronNocturnoComprado = $doctrine->getRepository('AppBundle:BonificacionXUsuario')->findBy([
            'idBonificacionExtra' => $paronNocturno, 'usado' => 0
        ]);
        if (count($paronNocturnoComprado)) {
            foreach ($paronNocturnoComprado as $MC) {
                if ($HOY->getTimestamp() - $MC->getFecha()->getTimestamp() <= $sieteDias) {
                    $contador++;
                    Usuario::operacionSobreTdV($doctrine, $MC->getIdUsuario(), 28800, 'Ingreso - Parón nocturno');
                } else {
                    $MC->setUsado(1);
                    $em->persist($MC);
                    $em->flush();
                }
            }
        }

        Utils::setError($doctrine, 3, "Parón nocturno - " . $contador);
        return new JsonResponse("OK", 200);
    }

}
