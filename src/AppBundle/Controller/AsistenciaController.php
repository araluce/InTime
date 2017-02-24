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
use Symfony\Component\HttpFoundation\RedirectResponse;
use \Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Utils\Usuario;
use AppBundle\Utils\Utils;
use AppBundle\Utils\DataManager;

/**
 * Description of AsistenciaController
 *
 * @author araluce
 */
class AsistenciaController extends Controller {

    /**
     * 
     * @Route("/ciudadano/asistencia", name="asistencia")
     */
    public function asistenciaAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/asistencia');
        if (!$status) {
            return new RedirectResponse('/');
        }
        if(!DataManager::infoUsu($doctrine, $session)){
            return new RedirectResponse('/ciudadano/info');
        }
        $id_usuario = $session->get('id_usuario');
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($id_usuario);
        $DATOS = DataManager::setDefaultData($doctrine, 'Asistencia sanitaria', $session);
        return $this->render('ciudadano/extensiones/asistencia.twig', $DATOS);
    }

    /**
     * 
     * @Route("/ciudadano/asistencia/solicitar", name="asistenciaSolicitar")
     */
    public function asistenciaSolicitarAction(Request $request) {
        if ($request->getMethod() == 'POST') {
            $doctrine = $this->getDoctrine();
            $em = $doctrine->getManager();
            $session = $request->getSession();
            $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/asistencia/solicitar');
            if (!$status) {
                return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Permiso denegado'), 200);
            }
            $id_usuario = $session->get('id_usuario');
            $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($id_usuario);
            $dia = $request->request->get('dia');
            $horas = $request->request->get('horas');
            $motivo = $request->request->get('motivo');
            $fecha = new \DateTime('now');
            $disponible = true;
            $TUTORIAS = $doctrine->getRepository('AppBundle:UsuarioTutoria')->findAll();
            if (!count($horas)) {
                return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Debes elegir un día en el horario.'), 200);
            }
            if (count($TUTORIAS)) {
                foreach ($TUTORIAS as $TUTORIA) {
                    if ($TUTORIA->getFechaSolicitud()->format("W") === $fecha->format("W") && $TUTORIA->getDia() === $dia && in_array($TUTORIA->getHora(), $horas)) {
                        $disponible = false;
                    }
                }
            }
            if ($disponible) {
                foreach ($horas as $hora) {
                    $TUTORIA = new \AppBundle\Entity\UsuarioTutoria();
                    $TUTORIA->setIdUsuario($USUARIO);
                    $TUTORIA->setDia($dia);
                    $TUTORIA->setHora($hora);
                    $TUTORIA->setMotivo($motivo);
                    $TUTORIA->setCoste(15);
                    $TUTORIA->setFechaSolicitud($fecha);
                    $TUTORIA->setEstado(false);
                    $em->persist($TUTORIA);
                }
                $em->flush();
            } else {
                return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Error. No se ha registrado su solicitud'
                    . ' debido a que las horas solicitadas no están disponibles. Puede que otro ciudadano las haya solicitado'
                    . ' a la vez'), 200);
            }
            return new JsonResponse(array('estado' => 'OK', 'message' => 'Tu solicitud'
                . ' se ha registrado correctamente y queda pendiente de aceptación por parte del GdT.'), 200);
        }
        return new JsonResponse(array('estado' => 'ERROR', 'message' => 'No se han enviado datos'), 200);
    }

    /**
     * 
     * @Route("/ciudadano/asistencia/obtenerOcupados", name="obtenerOcupados")
     */
    public function obtenerOcupadosAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/asistencia/obtenerOcupados');
        if (!$status) {
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Permiso denegado'), 200);
        }
        $fecha = new \DateTime('now');
        $TUTORIAS = $doctrine->getRepository('AppBundle:UsuarioTutoria')->findAll();
        $DATOS = [];
        $aux = [];
        if (count($TUTORIAS)) {
            foreach ($TUTORIAS as $TUTORIA) {
                if ($TUTORIA->getFechaSolicitud()->format("W") === $fecha->format("W") &&
                        $TUTORIA->getEstado() !== 2 &&
                        $TUTORIA->getEstado() !== 3 &&
                        $TUTORIA->getEstado() !== 4) {
                    $dia = Utils::tutoriaDiaToInt($TUTORIA->getDia());
                    $hora = explode(':', $TUTORIA->getHora());
                    if ($hora[1] === '45') {
                        $hora_inf = $hora[0] . ':' . $hora[1];
//                        $aux[$dia][] = $aux2;
                        $hora[0] = intval($hora[0]) + 1;
                        $hora[1] = '00';
                        $hora_sup = $hora[0] . ':' . $hora[1];
//                        $hora_inf = $hora[0] . ':' . $hora[1];
//                        $hora_sup = $hora[0] . ':15';
                    } else {
                        $hora_inf = $hora[0] . ':' . $hora[1];
                        $hora_sup = $hora[0] . ':' . (intval($hora[1]) + 15);
                    }
                    $aux2 = [$hora_inf, $hora_sup];
                    $aux[$dia][] = $aux2;
                }
            }
            foreach ($aux as $key => $value) {
//                $value .= '],';
                $DATOS[$key] = $value;
            }
        }
        return new JsonResponse(array('estado' => 'ERROR', 'message' => json_encode($DATOS)), 200);
    }

    /**
     * 
     * @Route("/ciudadano/asistencia/obtenerMisCitas", name="obtenerMisCitas")
     */
    public function obtenerMisCitasAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/asistencia/obtenerMisCitas');
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Permiso denegado')), 200);
        }
        $id_usuario = $session->get('id_usuario');
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($id_usuario);
        $TUTORIAS = $doctrine->getRepository('AppBundle:UsuarioTutoria')->findByIdUsuario($USUARIO);
        $DATOS = [];
        $fecha = new \DateTime('now');
        if (count($TUTORIAS)) {
            foreach ($TUTORIAS as $TUTORIA) {
                $aux = [];
                $aux['ID'] = $TUTORIA->getIdUsuarioTutoria();
                $aux['HORA'] = $TUTORIA->getHora();
                $aux['DIA'] = $TUTORIA->getDia();
                $aux['MOTIVO'] = $TUTORIA->getMotivo();
                $aux['COSTE'] = $TUTORIA->getCoste();
                $aux['ESTADO'] = $TUTORIA->getEstado();
                if ($TUTORIA->getFechaSolicitud()->format("W") !== $fecha->format("W") &&
                        $aux['ESTADO'] === 0) {
                    $TUTORIA->setEstado(4);
                    $aux['ESTADO'] = $TUTORIA->getEstado();
                    $em->persist($TUTORIA);
                    $em->flush();
                }
                $aux['FECHA_SOLICITUD'] = $TUTORIA->getFechaSolicitud();
                $DATOS[] = $aux;
            }
        }
        return new JsonResponse(array('estado' => 'ERROR', 'message' => json_encode($DATOS)), 200);
    }

    /**
     * 
     * @Route("/ciudadano/asistencia/cancelarCita", name="cancelarCita")
     */
    public function cancelarCitaAction(Request $request) {
        if ($request->getMethod() == 'POST') {
            $doctrine = $this->getDoctrine();
            $em = $doctrine->getManager();
            $session = $request->getSession();
            $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/asistencia/obtenerMisCitas');
            if (!$status) {
                return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Permiso denegado'), 200);
            }
            $id_usuario = $session->get('id_usuario');
            $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($id_usuario);
            $ROL = $USUARIO->getIdRol();
            $ROL_CIUDADANO = $doctrine->getRepository('AppBundle:Rol')->findOneByNombre('Jugador');
            $ROL_GUARDIAN = $doctrine->getRepository('AppBundle:Rol')->findOneByNombre('Guardián');
            $idCita = $request->request->get('idCita');
            $motivo = $request->request->get('motivo');
            $TUTORIA = $doctrine->getRepository('AppBundle:UsuarioTutoria')->findOneByIdUsuarioTutoria($idCita);
            if($TUTORIA !== null){
                if($ROL === $ROL_CIUDADANO){
                    $TUTORIA->setEstado(2);
                }
                else if($ROL === $ROL_GUARDIAN){
                    $TUTORIA->setEstado(3);
                }
                else{
                    return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Este usuario no tiene rol')), 200);
                }
                $TUTORIA->setMotivo($motivo);
                $em->persist($TUTORIA);
                $em->flush();
                return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => 'Cita cancelada correctamente')), 200);
            }
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No se ha encontrado la cita')), 200);
        }
        return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No se han enviado datos')), 200);
    }

}
