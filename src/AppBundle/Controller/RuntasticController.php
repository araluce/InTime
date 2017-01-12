<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Controller;

/**
 * Description of RuntasticController
 *
 * @author araluce
 */
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use \Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Runtastic\Runtastic;
use AppBundle\Utils\Utils;
use AppBundle\Utils\DataManager;
use AppBundle\Utils\Usuario;

/**
 * Description of CiudadanoController
 *
 * @author araluce
 */
class RuntasticController extends Controller {

    /**
     * @Route("/ciudadano/ocio/deporte", name="loginRuntastic")
     */
    public function deporteAction(Request $request) {
        $session = $request->getSession();
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $qb = $em->createQueryBuilder();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/ocio/deporte');
        if (!$status) {
            return new RedirectResponse('/');
        }
        $DATOS = DataManager::setDefaultData($doctrine, 'Deporte', $session);

        $DATOS['LOGIN'] = 1;
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get("id_usuario"));
        $query = $qb->select('ur')
                ->from('\AppBundle\Entity\UsuarioRuntastic', 'ur')
                ->where('ur.idUsuario = :IdUsuario AND ur.activo = 1')
                ->setParameters(['IdUsuario' => $USUARIO]);
        $CUENTAS_RUNTASTIC = $query->getQuery()->getResult();
        if (!count($CUENTAS_RUNTASTIC)) {
            $DATOS['LOGIN'] = 0;
        } else {
            $DATOS['SESIONES'] = [];
            foreach ($CUENTAS_RUNTASTIC as $CUENTA) {
                $SESIONES_RUNTASTIC = $doctrine->getRepository('AppBundle:SesionRuntastic')->findByIdUsuarioRuntastic($CUENTA);
                foreach ($SESIONES_RUNTASTIC as $SESION) {
                    $aux = [];
                    $aux['tipo'] = $SESION->getTipo();
                    $duracion = floor($SESION->getDuracion() / 1000);
                    $horas = $duracion / 60 / 60;
                    if ($horas >= 1) {
                        $horas = floor($horas) . '';
                        $duracion -= $horas * 60 * 60;
                        if (strlen($horas) === 1) {
                            $horas = '0' . $horas;
                        }
                    } else {
                        $horas = 0;
                    }
                    $minutos = $duracion / 60;
                    if ($minutos >= 1) {
                        $minutos = floor($minutos);
                        $duracion -= $minutos * 60;
                        if (strlen($minutos) === 1) {
                            $minutos = '0' . $minutos;
                        }
                    } else {
                        $minutos = 0;
                    }
                    $segundos = $duracion . '';
                    if (strlen($segundos) === 1) {
                        $segundos = '0' . $segundos;
                    }
                    $aux['tipo'] = $SESION->getTipo();
                    $aux['duracion'] = $horas . ':' . $minutos . ':' . $segundos;
                    $aux['velocidad'] = $SESION->getVelocidad();
                    $aux['fecha'] = $SESION->getFecha();
                    $DATOS['SESIONES'][] = $aux;
                }
            }
//            Utils::pretty_print($DATOS);
        }
        return $this->render('ciudadano/ocio/deporte.twig', $DATOS);
    }

    /**
     * @Route("/ciudadano/ocio/deporte/comprobarLogin", name="comprobarLogin")
     */
    public function comprobarLoginAction(Request $request) {
        $session = $request->getSession();
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $qb = $em->createQueryBuilder();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/ocio/deporte/comprobarLogin');
        if (!$status) {
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Permiso denegado'), 200);
        }
        if ($request->getMethod() == 'POST') {
            $usuario_runtastic = $request->request->get('usuario');
            $password_runtastic = $request->request->get('password');
            $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get("id_usuario"));

            $query = $qb->select('ur')
                    ->from('\AppBundle\Entity\UsuarioRuntastic', 'ur')
                    ->where('ur.username = :username AND ur.idUsuario != :Usuario')
                    ->setParameters(['username' => $usuario_runtastic, 'Usuario' => $USUARIO]);
            $UR = $query->getQuery()->getResult();
            if (count($UR)) {
                return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Un ciudadano de Intime está usando actualmente esta cuenta'), 200);
            }
            $r = new Runtastic();
            $r->setUsername($usuario_runtastic)->setPassword($password_runtastic);
            if (!$r->login()) {
                return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Error al iniciar sesión. El email o la contraseña son incorrectos'), 200);
            }
            $this->iniciarSesionRuntastic($USUARIO, $usuario_runtastic, $password_runtastic);
            return new JsonResponse(array('estado' => 'OK', 'message' => 'Se ha iniciado sesión correctamente'), 200);
        }
    }

    /**
     * @Route("/ciudadano/ocio/deporte/actualizarInfo", name="actualizarInfo")
     */
    public function actualizarInfoAction(Request $request) {
        $session = $request->getSession();
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $qb = $em->createQueryBuilder();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/ocio/deporte/actualizarInfo');
        if (!$status) {
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Permiso denegado'), 200);
        }
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get("id_usuario"));
        $query = $qb->select('ur')
                ->from('\AppBundle\Entity\UsuarioRuntastic', 'ur')
                ->where('ur.idUsuario = :Usuario AND ur.activo = 1')
                ->setParameters(['Usuario' => $USUARIO]);
        $UR = $query->getQuery()->getOneOrNullResult();

        if ($UR === null) {
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Fallo al actualizar la información'), 200);
        }

        $r = new Runtastic();
        $r->setUsername($UR->getUsername())->setPassword($UR->getPassword());
        $query = $qb->select('sr.idRuntastic')
                ->from('\AppBundle\Entity\SesionRuntastic', 'sr')
                ->where('sr.idUsuarioRuntastic = :idUsuarioRuntastic')
                ->setParameters(['idUsuarioRuntastic' => $UR]);
        $SESIONES = $query->getQuery()->getResult();
//        if ($r->login()) {
            $response['usuario'] = $r->getUsername();
            $response['Uid'] = $r->getUid();

            $semana = new \DateTime('now');
            $week_activities = $r->getActivities($semana->format("W"), $semana->format("m"), $semana->format("Y"));
            foreach ($week_activities as $activity) {
                if (!in_array(array('idRuntastic' => $activity->id), $SESIONES)) {
                    $SESION = new \AppBundle\Entity\SesionRuntastic();
                    $SESION->setIdRuntastic($activity->id);
                    $SESION->setIdUsuarioRuntastic($UR);
                    $SESION->setTipo($activity->type);
                    $SESION->setDuracion($activity->duration);
                    $SESION->setDistancia($activity->distance);
                    $SESION->setPaso($activity->pace);
                    $SESION->setVelocidad($activity->speed);
                    $SESION->setKcal($activity->kcal);
                    $SESION->setRitmoCardiacoMedio($activity->heartrate_avg);
                    $SESION->setRitmoCardiacoMax($activity->heartrate_max);
                    $SESION->setDesnivel($activity->elevation_gain);
                    $SESION->setPerdidaNivel($activity->elevation_loss);

                    if ($activity->surface !== '') {
                        $SESION->setSuperficie($activity->surface);
                    }
                    if ($activity->weather !== '') {
                        $SESION->setTiempo($activity->weather);
                    }
                    if ($activity->feeling !== '') {
                        $SESION->setSensacion($activity->feeling);
                    }
                    $FECHA = new \Datetime();
                    $FECHA->setDate($activity->date->year, $activity->date->month, $activity->date->day);
                    $FECHA->setTime($activity->date->hour, $activity->date->minutes, $activity->date->seconds);
                    $SESION->setFecha($FECHA);
                    $em->persist($SESION);
                }
            }
            $em->flush();
            return new JsonResponse(['estatus' => 'OK', 'message' => 'Datos descargados correctamente'], 200);
//        }
//        return new JsonResponse(['estatus' => 'ERROR', 'message' => 'Login failed'], 200);
    }

    function iniciarSesionRuntastic($USUARIO, $rt_usuario, $rt_contrasena) {
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();

        $USUARIO_RUNTASTIC = $doctrine->getRepository('AppBundle:UsuarioRuntastic')->findOneBy([
            'idUsuario' => $USUARIO, 'username' => $rt_usuario
        ]);

        if ($USUARIO_RUNTASTIC === null) {
            $USUARIO_RUNTASTIC = new \AppBundle\Entity\UsuarioRuntastic();
            $USUARIO_RUNTASTIC->setIdUsuario($USUARIO);
            $USUARIO_RUNTASTIC->setUsername($rt_usuario);
            $USUARIO_RUNTASTIC->setPassword($rt_contrasena);
        }
        $USUARIO_RUNTASTIC->setActivo(1);
        $em->persist($USUARIO_RUNTASTIC);
        $em->flush();
    }
    
    /**
     * @Route("/ciudadano/ocio/deporte/test", name="test")
     */
    public function testAction(Request $request) {
        $session = $request->getSession();
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $qb = $em->createQueryBuilder();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/ocio/deporte/test');
        if (!$status) {
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Permiso denegado'), 200);
        }
        $r = new Runtastic();
        $r->setUsername('araluce@correo.ugr.es')->setPassword('102938');
        
        if ($r->login()) {
            return new JsonResponse(['estatus' => 'OK', 'message' => 'Datos descargados correctamente'], 200);
        }
        return new JsonResponse(['estatus' => 'ERROR', 'message' => $r->getRedirectUrl()], 200);
    }

}
