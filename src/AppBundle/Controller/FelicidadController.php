<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Controller;

/**
 * Description of FelicidadController
 *
 * @author araluce
 */
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use AppBundle\Utils\DataManager;
use AppBundle\Utils\Alimentacion;
use AppBundle\Utils\Usuario;
use AppBundle\Utils\Utils;
use AppBundle\Utils\Ejercicio;
use AppBundle\Utils\Distrito;

class FelicidadController extends Controller {

    /**
     * @Route("/ciudadano/felicidad", name="comida")
     */
    public function comidaAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/felicidad');
        if (!$status) {
            return new RedirectResponse('/');
        }
        $DATOS = DataManager::setDefaultData($doctrine, 'Felicidad', $session);
        return $this->render('ciudadano/extensiones/felicidad.twig', $DATOS);
    }

    /**
     * @Route("/ciudadano/felicidad/getMisRetosFelicidad", name="getMisRetosFelicidad")
     */
    public function getMisRetosFelicidadAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/felicidad/getMisRetosFelicidad');
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Permiso denegado')), 200);
        }
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get("id_usuario"));
        $DATOS = [];
        $RETOS = $doctrine->getRepository('AppBundle:EjercicioFelicidad')->findByIdUsuario($USUARIO);
        if (null !== $RETOS) {
            foreach ($RETOS as $RETO) {
                $aux = [];
                $aux['FECHA'] = $RETO->getFecha();
                $aux['ID_RETO'] = $RETO->getIdEjercicioFelicidad();
                $aux['DESCRIPCION'] = $RETO->getEnunciado();
                $aux['PROPUESTA'] = [];
                $aux2 = [];
                $PROPUESTA = $RETO->getIdEjercicioPropuesta();
                $aux2['ID_EJERCICIO'] = $PROPUESTA->getIdEjercicio();
                $CALIFICACION_PROPUESTA = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findOneByIdEjercicio($PROPUESTA);
                $aux2['ESTADO'] = $CALIFICACION_PROPUESTA->getIdEjercicioEstado()->getEstado();
                if ($CALIFICACION_PROPUESTA->getIdEjercicioEstado() === 'evaluado') {
                    $APTO = $doctrine->getRepository('AppBundle:Calificaciones')->findOneByIdCalificaciones(1);
                    $aux2['CALIFICACION'] = 0;
                    if ($CALIFICACION_PROPUESTA->getIdCalificaciones() === $APTO) {
                        $aux2['CALIFICACION'] = 1;
                    }
                }
                $aux2['ID_CALIFICACION'] = $CALIFICACION_PROPUESTA->getIdEjercicioCalificacion();
                $EJERCICIO_ENTREGA = $doctrine->getRepository('AppBundle:EjercicioEntrega')->findOneByIdEjercicio($PROPUESTA);
                $aux2['NOMBRE_ENTREGA'] = $EJERCICIO_ENTREGA->getNombre();
                $aux2['RUTA_ENTREGA'] = $USUARIO->getDni() . '/felicidad/' . $aux2['ID_CALIFICACION'] . '/' . $aux2['NOMBRE_ENTREGA'];
                $aux['PROPUESTA'] = $aux2;
                $DATOS[] = $aux;
            }
        }
        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $DATOS)), 200);
    }

    /**
     * @Route("/ciudadano/felicidad/entregarFelicidad", name="entregarFelicidad")
     */
    public function entregarFelicidadAction(Request $request) {
        if ($request->getMethod() == 'POST') {
            $doctrine = $this->getDoctrine();
            $em = $doctrine->getManager();
            $session = $request->getSession();
            $ID_RETO = $request->request->get('id_reto');
            $ENTREGA = $request->files->get('entrega');
            $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/felicidad/entregarFelicidad');
            if (!$status) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado')), 200);
            }
            $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get('id_usuario'));
            $EJERCICIO_FELICIDAD = $doctrine->getRepository('AppBundle:EjercicioFelicidad')->findOneByIdEjercicioFelicidad($ID_RETO);
            if ($EJERCICIO_FELICIDAD === null) {
                Utils::setError($doctrine, 0, 'El EjercicioFelicidad con id: ' . $ID_RETO . ' no existe (entregarFelicidadAction)', $USUARIO);
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Error inesperado')), 200);
            }
            if ($ENTREGA !== null) {
                if ($ENTREGA->getClientSize() < $ENTREGA->getMaxFilesize()) {
                    // Cambiar a tiempo entre entregas de Felicidad
                    $tiempoEntreEntregas = Utils::getConstante($doctrine, 'diasDifEntregas');
                    $HOY = new \DateTime('now');
                    $fechaEntrega = $EJERCICIO_FELICIDAD->getIdEjercicioPropuesta()->getFecha();
                    if ($HOY->format("Y") === $fechaEntrega->format("Y")) {
                        if ($HOY->format("d") - $fechaEntrega->format("d") < $tiempoEntreEntregas) {
                            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Tiempo entre entregas de ' . $tiempoEntreEntregas . ' días')), 200);
                        }
                    }
                    $TIPO_ENTREGA = $doctrine->getRepository('AppBundle:EjercicioTipo')->findOneByTipo('entrega');
                    $SECCION_FELICIDAD = $doctrine->getRepository('AppBundle:EjercicioSeccion')->findOneBySeccion('felicidad');
                    $EJERCICIO = new \AppBundle\Entity\Ejercicio();
                    $EJERCICIO->setCoste(0);
                    $EJERCICIO->setEnunciado('Ejercicio de felicidad');
                    $EJERCICIO->setFecha($HOY);
                    $EJERCICIO->setIcono(null);
                    $EJERCICIO->setIdEjercicioSeccion($SECCION_FELICIDAD);
                    $EJERCICIO->setIdTipoEjercicio($TIPO_ENTREGA);
                    $em->persist($EJERCICIO);

                    $EJERCICIO_FELICIDAD->setIdEjercicioEntrega($EJERCICIO);
                    $em->persist($EJERCICIO_FELICIDAD);

                    $ENTREGADO = $doctrine->getRepository('AppBundle:EjercicioEstado')->findOneByEstado('entregado');
                    $EJERCICIO_CALIFICACION = new \AppBundle\Entity\EjercicioCalificacion();
                    $EJERCICIO_CALIFICACION->setFecha($HOY);
                    $EJERCICIO_CALIFICACION->setIdCalificaciones(null);
                    $EJERCICIO_CALIFICACION->setIdEjercicio($EJERCICIO);
                    $EJERCICIO_CALIFICACION->setIdEjercicioEstado($ENTREGADO);
                    $EJERCICIO_CALIFICACION->setIdGrupo(null);
                    $EJERCICIO_CALIFICACION->setIdEvaluador(null);
                    $EJERCICIO_CALIFICACION->setIdUsuario($USUARIO);
                    $em->persist($EJERCICIO_CALIFICACION);

                    $nombre_entrega = $ENTREGA->getClientOriginalName();
                    $EJERCICIO_ENTREGA = new \AppBundle\Entity\EjercicioEntrega();
                    $EJERCICIO_ENTREGA->setFecha($HOY);
                    $EJERCICIO_ENTREGA->setIdEjercicio($EJERCICIO);
                    $EJERCICIO_ENTREGA->setIdUsuario($USUARIO);
                    $EJERCICIO_ENTREGA->setNombre($nombre_entrega);
                    $EJERCICIO_ENTREGA->setMime($ENTREGA->getClientMimeType());
                    $em->persist($EJERCICIO_ENTREGA);

                    $em->flush();

                    $ruta = 'USUARIOS/' . $USUARIO->getDni() . '/felicidad/' . $EJERCICIO_CALIFICACION->getIdEjercicioCalificacion();
                    if (!file_exists($ruta)) {
                        mkdir($ruta, 0777, true);
                    }

                    $ENTREGA->move($ruta, $nombre_entrega);
                    return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => 'Entrega realizada correctamente correctamente')), 200);
                }
                $respuesta = 'El archivo que intenta subir supera el tamaño máximo permitido.'
                        . '<br>Tu archivo: ' . $ENTREGA->getClientSize() / 1024
                        . '<br>Tamaño máx: ' . $ENTREGA->getMaxFilesize() / 1024;
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => $respuesta)), 200);
            }
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No se ha seleccionado archivo')), 200);
        }
        return new JsonResponse(array('estado' => 'ERROR', 'message' => 'No se ha recibido ningún dato'));
    }

    /**
     * @Route("/ciudadano/felicidad/entregarPropuesta", name="entregarPropuesta")
     */
    public function entregarPropuestaFelicidadAction(Request $request) {
        if ($request->getMethod() == 'POST') {
            $doctrine = $this->getDoctrine();
            $em = $doctrine->getManager();
            $session = $request->getSession();
            $ENTREGA = $request->files->get('entrega');
            $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/felicidad/entregarPropuesta');
            if (!$status) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado')), 200);
            }
            $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get('id_usuario'));

            if ($ENTREGA !== null) {
                if ($ENTREGA->getClientSize() < $ENTREGA->getMaxFilesize()) {
                    $HOY = new \DateTime('now');

                    $TIPO_ENTREGA = $doctrine->getRepository('AppBundle:EjercicioTipo')->findOneByTipo('entrega');
                    $SECCION_FELICIDAD = $doctrine->getRepository('AppBundle:EjercicioSeccion')->findOneBySeccion('felicidad');
                    $EJERCICIO = new \AppBundle\Entity\Ejercicio();
                    $EJERCICIO->setCoste(0);
                    $EJERCICIO->setEnunciado('Ejercicio de felicidad');
                    $EJERCICIO->setFecha($HOY);
                    $EJERCICIO->setIcono(null);
                    $EJERCICIO->setIdEjercicioSeccion($SECCION_FELICIDAD);
                    $EJERCICIO->setIdTipoEjercicio($TIPO_ENTREGA);
                    $em->persist($EJERCICIO);

                    $EJERCICIO_FELICIDAD = new \AppBundle\Entity\EjercicioFelicidad();
                    $EJERCICIO_FELICIDAD->setEnunciado('');
                    $EJERCICIO_FELICIDAD->setFecha($HOY);
                    $EJERCICIO_FELICIDAD->setIdUsuario($USUARIO);
                    $EJERCICIO_FELICIDAD->setIdEjercicioEntrega(null);
                    $EJERCICIO_FELICIDAD->setIdEjercicioPropuesta($EJERCICIO);
                    $em->persist($EJERCICIO_FELICIDAD);

                    $ENTREGADO = $doctrine->getRepository('AppBundle:EjercicioEstado')->findOneByEstado('entregado');
                    $EJERCICIO_CALIFICACION = new \AppBundle\Entity\EjercicioCalificacion();
                    $EJERCICIO_CALIFICACION->setFecha($HOY);
                    $EJERCICIO_CALIFICACION->setIdCalificaciones(null);
                    $EJERCICIO_CALIFICACION->setIdEjercicio($EJERCICIO);
                    $EJERCICIO_CALIFICACION->setIdEjercicioEstado($ENTREGADO);
                    $EJERCICIO_CALIFICACION->setIdGrupo(null);
                    $EJERCICIO_CALIFICACION->setIdEvaluador(null);
                    $EJERCICIO_CALIFICACION->setIdUsuario($USUARIO);
                    $em->persist($EJERCICIO_CALIFICACION);
                    

                    $nombre_entrega = $ENTREGA->getClientOriginalName();
                    $EJERCICIO_ENTREGA = new \AppBundle\Entity\EjercicioEntrega();
                    $EJERCICIO_ENTREGA->setFecha($HOY);
                    $EJERCICIO_ENTREGA->setIdEjercicio($EJERCICIO);
                    $EJERCICIO_ENTREGA->setIdUsuario($USUARIO);
                    $EJERCICIO_ENTREGA->setNombre($nombre_entrega);
                    $EJERCICIO_ENTREGA->setMime($ENTREGA->getClientMimeType());
                    $em->persist($EJERCICIO_ENTREGA);

                    $em->flush();

                    $ruta = 'USUARIOS/' . $USUARIO->getDni() . '/felicidad/' . $EJERCICIO_CALIFICACION->getIdEjercicioCalificacion();
                    if (!file_exists($ruta)) {
                        mkdir($ruta, 0777, true);
                    }

                    $ENTREGA->move($ruta, $nombre_entrega);
                    return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => 'Entrega realizada correctamente correctamente')), 200);
                }
                $respuesta = 'El archivo que intenta subir supera el tamaño máximo permitido.'
                        . '<br>Tu archivo: ' . $ENTREGA->getClientSize() / 1024
                        . '<br>Tamaño máx: ' . $ENTREGA->getMaxFilesize() / 1024;
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => $respuesta)), 200);
            }
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No se ha seleccionado archivo')), 200);
        }
        return new JsonResponse(array('estado' => 'ERROR', 'message' => 'No se ha recibido ningún dato'));
    }

}
