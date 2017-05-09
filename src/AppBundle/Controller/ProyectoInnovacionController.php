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
use AppBundle\Utils\Distrito;
use AppBundle\Utils\Utils;
use AppBundle\Utils\DataManager;
use AppBundle\Utils\Ejercicio;
use AppBundle\Utils\Trabajo;

/**
 * Controlador para retos de proyecto de innovación
 *
 * @author araluce
 */
class ProyectoInnovacionController extends Controller {

    /**
     * @Route("/ciudadano/trabajo/proyecto_innovacion", name="proyecto_innovacion")
     */
    public function proyectoInnovacionAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/trabajo/proyecto_innovacion');
        if (!$status) {
            return new RedirectResponse('/');
        }
        $DATOS = DataManager::setDefaultData($doctrine, 'Proyecto de innovación', $session);

        return $this->render('ciudadano/trabajo/proyecto_innovacion.twig', $DATOS);
    }

    /**
     * @Route("/ciudadano/trabajo/proyecto_innovacion/entregarPI", name="entregarPI")
     */
    public function entregarPIAction(Request $request) {
        $session = $request->getSession();
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/trabajo/proyecto_innovacion/entregarPI');
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado')), 200);
        }
        if ($request->getMethod() == 'POST') {
            $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get("id_usuario"));
            $ENTREGA = $request->files->get('entrega');
            if (null === $ENTREGA) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No se ha seleccionado ningún archivo')), 200);
            }
            $EJERCICIO_SECCION = $doctrine->getRepository('AppBundle:EjercicioSeccion')->findOneBySeccion('proyecto_innovacion');
            $EJERCICIO = $doctrine->getRepository('AppBundle:Ejercicio')->findOneByIdEjercicioSeccion($EJERCICIO_SECCION);
            if (null === $EJERCICIO) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No se encuentra el reto')), 200);
            }
            $EJERCICIO_CALIFICACION = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findOneBy([
                'idUsuario' => $USUARIO, 'idEjercicio' => $EJERCICIO
            ]);
            if (null !== $EJERCICIO_CALIFICACION) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Ya tenías una entrega en este reto')), 200);
            }
            $ENTREGADO = $doctrine->getRepository('AppBundle:EjercicioEstado')->findOneByEstado('entregado');
            $SOLICITADO = $doctrine->getRepository('AppBundle:EjercicioEstado')->findOneByEstado('solicitado');
            $CIUDADANOS = Distrito::getCiudadanosDistrito($doctrine, $USUARIO->getIdDistrito());
            if (count($CIUDADANOS)) {
                foreach ($CIUDADANOS as $CIUDADANO) {
                    $EJERCICIO_CALIFICACION = new \AppBundle\Entity\EjercicioCalificacion();
                    $EJERCICIO_CALIFICACION->setFecha(new \DateTime('now'));
                    $EJERCICIO_CALIFICACION->setIdCalificaciones(null);
                    $EJERCICIO_CALIFICACION->setIdEjercicio($EJERCICIO);
                    $EJERCICIO_CALIFICACION->setIdEjercicioEstado($SOLICITADO);
                    $EJERCICIO_CALIFICACION->setIdEvaluador(null);
                    $EJERCICIO_CALIFICACION->setIdGrupo(null);
                    $EJERCICIO_CALIFICACION->setIdUsuario($CIUDADANO);
                    $em->persist($EJERCICIO_CALIFICACION);
                }
                $em->flush();
            }
            $EJERCICIO_CALIFICACION = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findOneBy([
                'idUsuario' => $USUARIO, 'idEjercicio' => $EJERCICIO
            ]);
            $EJERCICIO_CALIFICACION->setIdEjercicioEstado($ENTREGADO);

            if ($ENTREGA->getClientSize() < $ENTREGA->getMaxFilesize()) {
                $ruta = 'USUARIOS/' . $USUARIO->getDni() . '/proyecto_innovacion/' . $EJERCICIO_CALIFICACION->getIdEjercicioCalificacion();
                if (!file_exists($ruta)) {
                    mkdir($ruta, 0777, true);
                }
                $nombre_entrega = Utils::replaceAccented($ENTREGA->getClientOriginalName());
                $UPLOAD = $ENTREGA->move($ruta, $nombre_entrega);
                if ($ENTREGA->getError()) {
                    return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => $ENTREGA->getErrorMessage())), 200);
                }

                $EJERCICIO_ENTREGA = $doctrine->getRepository('AppBundle:EjercicioEntrega')->findOneBy([
                    'idUsuario' => $USUARIO, 'idEjercicio' => $EJERCICIO
                ]);
                if ($EJERCICIO_ENTREGA === null) {
                    $EJERCICIO_ENTREGA = new \AppBundle\Entity\EjercicioEntrega();
                }
                $EJERCICIO_ENTREGA->setIdUsuario($USUARIO);
                $EJERCICIO_ENTREGA->setIdEjercicio($EJERCICIO);
                $EJERCICIO_ENTREGA->setNombre($nombre_entrega);
                $EJERCICIO_ENTREGA->setMime($ENTREGA->getClientMimeType());
                $EJERCICIO_ENTREGA->setFecha($EJERCICIO_CALIFICACION->getFecha());
                $em->persist($EJERCICIO_ENTREGA);

                $em->flush();
                return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => 'Reto entregado correctamente')), 200);
            }
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'El archivo supera el límite máximo permitido')), 200);
        }
        return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No se han recibido datos')), 200);
    }

    /**
     * @Route("/ciudadano/trabajo/proyecto_innovacion/datosPI", name="datosPI")
     */
    public function datosPIAction(Request $request) {
        $session = $request->getSession();
        $doctrine = $this->getDoctrine();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/trabajo/proyecto_innovacion/datosPI');
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado')), 200);
        }
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get("id_usuario"));
        $EJERCICIO_SECCION = $doctrine->getRepository('AppBundle:EjercicioSeccion')->findOneBySeccion('proyecto_innovacion');
        $EJERCICIO = $doctrine->getRepository('AppBundle:Ejercicio')->findOneByIdEjercicioSeccion($EJERCICIO_SECCION);

        $CIUDADANOS = Distrito::getCiudadanosDistrito($doctrine, $USUARIO->getIdDistrito());
        if (count($CIUDADANOS)) {
            foreach ($CIUDADANOS as $CIUDADANO) {
                $EJERCICIO_ENTREGA = $doctrine->getRepository('AppBundle:EjercicioEntrega')->findOneBy([
                    'idUsuario' => $CIUDADANO, 'idEjercicio' => $EJERCICIO
                ]);
                if (null !== $EJERCICIO_ENTREGA) {
                    $EJERCICIO_CALIFICACION = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findOneBy([
                        'idUsuario' => $CIUDADANO, 'idEjercicio' => $EJERCICIO
                    ]);
                    $DATOS = [];
                    $DATOS['ENTREGADO'] = 0;
                    $DATOS['CALIFICADO'] = 0;
                    if (null !== $EJERCICIO_CALIFICACION) {
                        $DATOS['ENTREGADO'] = 1;
                        if (null !== $EJERCICIO_CALIFICACION->getIdCalificaciones()) {
                            $DATOS['CALIFICADO'] = 1;
                            $DATOS['TITULO'] = $EJERCICIO->getEnunciado();
                            $DATOS['ICONO'] = $EJERCICIO_CALIFICACION->getIdCalificaciones()->getCorrespondenciaIcono();
                            $DATOS['TEXTO'] = $EJERCICIO_CALIFICACION->getIdCalificaciones()->getCorrespondenciaTexto();
                        }
                    }
                }
            }
        }

        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $DATOS)), 200);
    }

    /**
     * 
     * @Route("/guardian/ejercicios/proyectoInnovacion", name="felicidadGuardian")
     */
    public function felicidadGuardianAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/ejercicios/proyectoInnovacion', true);
        if (!$status) {
            return new RedirectResponse('/');
        }
        $DATOS['TITULO'] = 'Proyecto de innovación';
        $DATOS['ULT_CAL'] = Utils::getUltimasCalificacionesSeccion($doctrine, 'proyecto_innovacion');
        return $this->render('guardian/ejercicios/proyectoInnovacion.twig', $DATOS);
    }

}
