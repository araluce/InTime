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
        if (!DataManager::infoUsu($doctrine, $session)) {
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
                    if ($TUTORIA->getFechaSolicitud()->format("W") === $fecha->format("W") &&
                            $TUTORIA->getDia() === $dia &&
                            in_array($TUTORIA->getHora(), $horas)) {
                        $disponible = false;
                    }
                }
            }
            if ($disponible) {
                foreach ($horas as $hora) {
                    if ($hora !== '') {
                        $TUTORIA = new \AppBundle\Entity\UsuarioTutoria();
                        $TUTORIA->setIdUsuario($USUARIO);
                        $TUTORIA->setDia($dia);
                        $TUTORIA->setHora($hora);
                        $TUTORIA->setMotivo($motivo);
                        $TUTORIA->setCoste(900);
                        $TUTORIA->setFechaSolicitud($fecha);
                        $TUTORIA->setEstado(false);
                        $em->persist($TUTORIA);
                    }
                }
                $em->flush();
            } else {
                return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Error. No se ha registrado su solicitud'
                    . ' debido a que las horas solicitadas no están disponibles.'), 200);
            }
            return new JsonResponse(array('estado' => 'OK', 'message' => 'Tu solicitud'
                . ' se ha registrado correctamente y queda pendiente de aceptación por parte del GdT.'), 200);
        }
        return new JsonResponse(array('estado' => 'ERROR', 'message' => 'No se han enviado datos'), 200);
    }

    /**
     * 
     * @Route("/ciudadano/asistencia/obtenerCitasPendientes", name="obtenerCitasPendientes")
     */
    public function obtenerCitasPendientesAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/asistencia/obtenerCitasPendientes');
        if (!$status) {
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Permiso denegado'), 200);
        }
        $TUTORIAS = $doctrine->getRepository('AppBundle:UsuarioTutoria')->findAll();
        $ROL_GDT = $doctrine->getRepository('AppBundle:Rol')->findOneByNombre('Guardián');
        $fecha = new \DateTime('now');
        if (count($TUTORIAS)) {
            $aux = [];
            foreach ($TUTORIAS as $TUTORIA) {
                if ($TUTORIA->getFechaSolicitud()->format("W") === $fecha->format("W") &&
                        $TUTORIA->getEstado() === 0 &&
                        $TUTORIA->getIdUsuario()->getIdRol() !== $ROL_GDT) {
                    $dia = Utils::tutoriaDiaToInt($TUTORIA->getDia());
                    $hora = explode(':', $TUTORIA->getHora());
                    if ($hora[1] === '45') {
                        $hora_inf = $hora[0] . ':' . $hora[1];
                        $hora[0] = intval($hora[0]) + 1;
                        $hora[1] = '00';
                        $hora_sup = $hora[0] . ':' . $hora[1];
                    } else {
                        $hora_inf = $hora[0] . ':' . $hora[1];
                        $hora_sup = $hora[0] . ':' . (intval($hora[1]) + 15);
                    }
                    $aux2 = [$hora_inf, $hora_sup];
                    $aux[$dia][] = $aux2;
                }
            }
            foreach ($aux as $key => $value) {
                $DATOS[$key] = $value;
            }
        }
        return new JsonResponse(array('estado' => 'ERROR', 'message' => json_encode($DATOS)), 200);
    }

    /**
     * 
     * @Route("/ciudadano/asistencia/obtenerCitasAceptadas", name="obtenerCitasAceptadas")
     */
    public function obtenerCitasAceptadasAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/asistencia/obtenerCitasAceptadas');
        if (!$status) {
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Permiso denegado'), 200);
        }
        $ROL_GDT = $doctrine->getRepository('AppBundle:Rol')->findOneByNombre('Guardián');
        $fecha = new \DateTime('now');
        $DATOS = [];
        $TUTORIAS = $doctrine->getRepository('AppBundle:UsuarioTutoria')->findAll();
        if (count($TUTORIAS)) {
            $aux = [];
            foreach ($TUTORIAS as $TUTORIA) {
                if ($TUTORIA->getFechaSolicitud()->format("W") === $fecha->format("W") &&
                        !Utils::esCitaAntigua($TUTORIA) &&
                        $TUTORIA->getEstado() === 1 &&
                        $TUTORIA->getIdUsuario()->getIdRol() !== $ROL_GDT) {
                    $dia = Utils::tutoriaDiaToInt($TUTORIA->getDia());
                    $hora = explode(':', $TUTORIA->getHora());
                    if ($hora[1] === '45') {
                        $hora_inf = $hora[0] . ':' . $hora[1];
                        $hora[0] = intval($hora[0]) + 1;
                        $hora[1] = '00';
                        $hora_sup = $hora[0] . ':' . $hora[1];
                    } else {
                        $hora_inf = $hora[0] . ':' . $hora[1];
                        $hora_sup = $hora[0] . ':' . (intval($hora[1]) + 15);
                    }
                    $aux2 = [$hora_inf, $hora_sup];
                    $aux[$dia][] = $aux2;
                }
            }
            foreach ($aux as $key => $value) {
                $DATOS[$key] = $value;
            }
        }
        return new JsonResponse(array('estado' => 'ERROR', 'message' => json_encode($DATOS)), 200);
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
        $ROL_GDT = $doctrine->getRepository('AppBundle:Rol')->findOneByNombre('Guardián');
        $fecha = new \DateTime('now');
        $DATOS = [];

        $TUTORIAS = $doctrine->getRepository('AppBundle:UsuarioTutoria')->findAll();
        if (count($TUTORIAS)) {
            $aux = [];
            foreach ($TUTORIAS as $TUTORIA) {
                if ($TUTORIA->getIdUsuario()->getIdRol() === $ROL_GDT) {
                    $dia = Utils::tutoriaDiaToInt($TUTORIA->getDia());
                    $hora = explode(':', $TUTORIA->getHora());
                    if ($hora[1] === '45') {
                        $hora_inf = $hora[0] . ':' . $hora[1];
                        $hora[0] = intval($hora[0]) + 1;
                        $hora[1] = '00';
                        $hora_sup = $hora[0] . ':' . $hora[1];
                    } else {
                        $hora_inf = $hora[0] . ':' . $hora[1];
                        $hora_sup = $hora[0] . ':' . (intval($hora[1]) + 15);
                    }
                    $aux2 = [$hora_inf, $hora_sup];
                    $aux[$dia][] = $aux2;
                }
            }
            foreach ($aux as $key => $value) {
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
                if (($TUTORIA->getFechaSolicitud()->format("W") !== $fecha->format("W") ||
                        Utils::esCitaAntigua($TUTORIA) ) &&
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
            if ($TUTORIA !== null) {
                if ($ROL === $ROL_CIUDADANO) {
                    $TUTORIA->setEstado(2);
                } else if ($ROL === $ROL_GUARDIAN) {
                    $TUTORIA->setEstado(3);
                } else {
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

    /**
     * 
     * @Route("/guardian/asistencia", name="asistenciaGuardian")
     */
    public function asistenciaGuardianAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/asistencia', true);
        if (!$status) {
            return new RedirectResponse('/');
        }
        return $this->render('guardian/asistencia.twig');
    }

    /**
     * 
     * @Route("/guardian/asistencia/getCitaInfo", name="getCitaInfo")
     */
    public function asistenciaGetCitaInfoAction(Request $request) {
        if ($request->getMethod() == 'POST') {
            $doctrine = $this->getDoctrine();
            $em = $doctrine->getManager();
            $session = $request->getSession();
            $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/asistencia/getCitaInfo', true);
            if (!$status) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Permiso denegado')), 200);
            }
            $dia = $request->request->get('dia');
            $hora = $request->request->get('hora');
            $CITAS = $doctrine->getRepository('AppBundle:UsuarioTutoria')->findBy([
                'hora' => $hora, 'dia' => $dia
            ]);
            if (!count($CITAS)) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No hay ninguna cita')), 200);
            }

            $HOY = new \DateTime('now');
            $ESTA_SEMANA = $HOY->format('W');
            $CITA = null;
            foreach ($CITAS as $C) {
                if ($C->getFechaSolicitud()->format('W') === $ESTA_SEMANA &&
                        ($C->getEstado() === 0 || $C->getEstado() === 1)) {
                    $CITA = $C;
                }
            }
            if (null === $CITA) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No hay ninguna cita')), 200);
            }

            $GDT = $doctrine->getRepository('AppBundle:Rol')->findOneByNombre('Guardián');

            $DATOS = [];

            $CIUDADANO = $CITA->getIdUsuario();
            $DATOS['ADMIN'] = 0;
            if ($CIUDADANO->getIdRol() === $GDT) {
                $DATOS['ADMIN'] = 1;
            }
            $DATOS['NOMBRE'] = $CIUDADANO->getNombre();
            $DATOS['APELLIDOS'] = $CIUDADANO->getApellidos();
            $DATOS['ALIAS'] = $CIUDADANO->getSeudonimo();
            $DATOS['DNI'] = $CIUDADANO->getDni();

            $DATOS['HORA'] = $C->getHora();
            $DATOS['DIA'] = $C->getDia();
            $DATOS['MOTIVO'] = $C->getMotivo();
            $DATOS['ESTADO'] = $C->getEstado();

            return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $DATOS)), 200);
        }
        return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No se han enviado datos')), 200);
    }

    /**
     * 
     * @Route("/guardian/asistencia/bloquear", name="asistenciaBloquearGuardian")
     */
    public function asistenciaBloquearGuardianAction(Request $request) {
        if ($request->getMethod() == 'POST') {
            $doctrine = $this->getDoctrine();
            $em = $doctrine->getManager();
            $session = $request->getSession();
            $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/asistencia/bloquear', true);
            if (!$status) {
                return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Permiso denegado'), 200);
            }
            $id_usuario = $session->get('id_usuario');
            $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($id_usuario);
            $dia = $request->request->get('dia');
            $horas = $request->request->get('horas');
            $motivo = '';
            $fecha = new \DateTime('now');
            $disponible = true;
            $TUTORIAS = $doctrine->getRepository('AppBundle:UsuarioTutoria')->findAll();
            if (!count($horas)) {
                return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Debes elegir un día en el horario.'), 200);
            }
            if (count($TUTORIAS)) {
                foreach ($TUTORIAS as $TUTORIA) {
                    if ($TUTORIA->getFechaSolicitud()->format("W") === $fecha->format("W") &&
                            $TUTORIA->getDia() === $dia &&
                            in_array($TUTORIA->getHora(), $horas) &&
                            $TUTORIA->getEstado() === 0) {
                        // Anulamos una cita solicitada
                        $TURORIA->setEstado(3);
                        $TURORIA->setMotivo('A esta hora no me viene bien. Puedes solicitar otra hora.');
                        $em->persist($TURORIA);
                        $em->flush();
                    }
                }
            }
            foreach ($horas as $hora) {
                $TUTORIA = new \AppBundle\Entity\UsuarioTutoria();
                $TUTORIA->setIdUsuario($USUARIO);
                $TUTORIA->setDia($dia);
                $TUTORIA->setHora($hora);
                $TUTORIA->setMotivo($motivo);
                $TUTORIA->setCoste(0);
                $TUTORIA->setFechaSolicitud($fecha);
                $TUTORIA->setEstado(false);
                $em->persist($TUTORIA);
            }
            $em->flush();

            return new JsonResponse(array('estado' => 'OK', 'message' => 'Bloqueado con éxito'), 200);
        }
        return new JsonResponse(array('estado' => 'ERROR', 'message' => 'No se han enviado datos'), 200);
    }

    /**
     * 
     * @Route("/guardian/asistencia/desbloquear", name="asistenciaDesbloquearGuardian")
     */
    public function asistenciaDesbloquearGuardianAction(Request $request) {
        if ($request->getMethod() == 'POST') {
            $doctrine = $this->getDoctrine();
            $em = $doctrine->getManager();
            $session = $request->getSession();
            $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/asistencia/desbloquear', true);
            if (!$status) {
                return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Permiso denegado'), 200);
            }
            $id_usuario = $session->get('id_usuario');
            $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($id_usuario);
            $dia = $request->request->get('dia');
            $hora = $request->request->get('hora');
            $motivo = '';
            $fecha = new \DateTime('now');
            $TUTORIA = $doctrine->getRepository('AppBundle:UsuarioTutoria')->findOneBy([
                'dia' => $dia, 'hora' => $hora
            ]);
            $em->remove($TUTORIA);
            $em->flush();
            return new JsonResponse(array('estado' => 'OK', 'message' => 'Desbloqueado con éxito'), 200);
        }
        return new JsonResponse(array('estado' => 'ERROR', 'message' => 'No se han enviado datos'), 200);
    }

    /**
     * 
     * @Route("/guardian/asistencia/aceptarCita", name="asistenciaAceptarCitaGuardian")
     */
    public function asistenciaAceptarCitaGuardianAction(Request $request) {
        if ($request->getMethod() == 'POST') {
            $doctrine = $this->getDoctrine();
            $em = $doctrine->getManager();
            $session = $request->getSession();
            $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/asistencia/aceptarCita', true);
            if (!$status) {
                return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Permiso denegado'), 200);
            }

            $dia = $request->request->get('dia');
            $hora = $request->request->get('hora');
            $TUTORIA = $doctrine->getRepository('AppBundle:UsuarioTutoria')->findOneBy([
                'dia' => $dia, 'hora' => $hora
            ]);
            $USUARIO = $TUTORIA->getIdUsuario();
            Usuario::operacionSobreTdV($doctrine, $USUARIO, (-1) * $TUTORIA->getCoste(), 'Cobro - Asistencia');
            $TUTORIA->setEstado(1);
            $em->persist($TUTORIA);
            $em->flush();
            return new JsonResponse(array('estado' => 'OK', 'message' => 'Cita aceptada'), 200);
        }
        return new JsonResponse(array('estado' => 'ERROR', 'message' => 'No se han enviado datos'), 200);
    }

    /**
     * 
     * @Route("/guardian/asistencia/anularCita", name="asistenciaAnularCitaGuardian")
     */
    public function asistenciaAnularCitaGuardianAction(Request $request) {
        if ($request->getMethod() == 'POST') {
            $doctrine = $this->getDoctrine();
            $em = $doctrine->getManager();
            $session = $request->getSession();
            $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/asistencia/anularCita', true);
            if (!$status) {
                return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Permiso denegado'), 200);
            }
            $semana = new \DateTime('now');
            $dia = $request->request->get('dia');
            $hora = $request->request->get('hora');
            $motivo = $request->request->get('motivo');
            $TUTORIAS = $doctrine->getRepository('AppBundle:UsuarioTutoria')->findBy([
                'dia' => $dia, 'hora' => $hora
            ]);
            if (!count($TUTORIAS)) {
                return new JsonResponse(array('estado' => 'ERROR', 'message' => 'No encuentro la cita'), 200);
            }
            foreach ($TUTORIAS as $TUTORIA) {
                if ($TUTORIA->getFechaSolicitud()->format('W') === $semana->format('W')) {
                    $USUARIO = $TUTORIA->getIdUsuario();
                    Usuario::operacionSobreTdV($doctrine, $USUARIO, $TUTORIA->getCoste(), 'Ingreso - Cancelación de sistencia');
                    $TUTORIA->setEstado(3);
                    $TUTORIA->setMotivo($motivo);
                    $em->persist($TUTORIA);
                    $em->flush();
                }
            }
            return new JsonResponse(array('estado' => 'OK', 'message' => 'Cita anulada'), 200);
        }
        return new JsonResponse(array('estado' => 'ERROR', 'message' => 'No se han enviado datos'), 200);
    }

    /**
     * 
     * @Route("/guardian/asistencia/remunerarCita", name="asistenciaRemunerarCitaGuardian")
     */
    public function asistenciaRemunerarCitaGuardianAction(Request $request) {
        if ($request->getMethod() == 'POST') {
            $doctrine = $this->getDoctrine();
            $em = $doctrine->getManager();
            $session = $request->getSession();
            $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/asistencia/remunerarCita', true);
            if (!$status) {
                return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Permiso denegado'), 200);
            }
            $semana = new \DateTime('now');
            $dia = $request->request->get('dia');
            $hora = $request->request->get('hora');
            $TUTORIAS = $doctrine->getRepository('AppBundle:UsuarioTutoria')->findBy([
                'dia' => $dia, 'hora' => $hora
            ]);
            if (!count($TUTORIAS)) {
                return new JsonResponse(array('estado' => 'ERROR', 'message' => 'No encuentro la cita'), 200);
            }
            foreach ($TUTORIAS as $TUTORIA) {
                if ($TUTORIA->getFechaSolicitud()->format('W') === $semana->format('W')) {
                    $USUARIO = $TUTORIA->getIdUsuario();
                    Usuario::operacionSobreTdV($doctrine, $USUARIO, $TUTORIA->getCoste(), 'Ingreso - Cita satisfactoria');
                }
            }
            return new JsonResponse(array('estado' => 'OK', 'message' => 'Ciudadano remunerado correctamente'), 200);
        }
        return new JsonResponse(array('estado' => 'ERROR', 'message' => 'No se han enviado datos'), 200);
    }

}
