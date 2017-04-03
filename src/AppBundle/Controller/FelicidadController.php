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
     * @Route("/ciudadano/felicidad", name="felicidad")
     */
    public function felicidadAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/felicidad');
        if (!$status) {
            return new RedirectResponse('/');
        }
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get("id_usuario"));
        Utils::setEjerciciosFelicidadUsuario($doctrine, $USUARIO);
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
        $DATOS = DataManager::getRetosFelicidad($doctrine, $USUARIO);
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
            $ID_RETO = $request->request->get('ID_RETO');
            if ($ID_RETO === 0) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No hay reto')), 200);
            }
            $ENTREGA = $request->files->get('entrega-entrega');
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
                    $tiempoEntreEntregas = Utils::getConstante($doctrine, 'diasDifEntregasFelicidad');
                    $HOY = new \DateTime('now');
                    $fechaEntrega = $EJERCICIO_FELICIDAD->getIdEjercicioPropuesta()->getFecha();
                    $diasDiferencia = ((($HOY->getTimestamp() - $fechaEntrega->getTimestamp()) / 60) / 60) / 24;
                    if ($diasDiferencia < $tiempoEntreEntregas) {
                        return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Tiempo entre entregas de ' . $tiempoEntreEntregas . ' días')), 200);
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

                    $nombre_entrega = Utils::replaceAccented($ENTREGA->getClientOriginalName());
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
            $TITULO = $request->request->get('titulo');
            $ID_FASE = $request->request->get('ID_FASE');
            if (preg_replace('/\s+/', '', $TITULO) === '') {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Debes escribir un título')), 200);
            }
            $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/felicidad/entregarPropuesta');
            if (!$status) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado')), 200);
            }
            $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get('id_usuario'));

            if ($ENTREGA !== null) {
                if ($ENTREGA->getClientSize() < $ENTREGA->getMaxFilesize()) {
                    $HOY = new \DateTime('now');

                    $ANTERIOR_EJERCICIO_FELICIDAD = $doctrine->getRepository('AppBundle:EjercicioFelicidad')->findOneBy([
                        'idUsuario' => $USUARIO, 'fase' => $ID_FASE
                    ]);

                    if ($ANTERIOR_EJERCICIO_FELICIDAD !== null) {
                        $EJERCICIO = $ANTERIOR_EJERCICIO_FELICIDAD->getIdEjercicioPropuesta();
                        if (null === $EJERCICIO) {
                            $EJERCICIO = new \AppBundle\Entity\Ejercicio();
                        }
                    } else {
                        $EJERCICIO = new \AppBundle\Entity\Ejercicio();
                    }
                    $TIPO_ENTREGA = $doctrine->getRepository('AppBundle:EjercicioTipo')->findOneByTipo('entrega');
                    $SECCION_FELICIDAD = $doctrine->getRepository('AppBundle:EjercicioSeccion')->findOneBySeccion('felicidad');

                    $EJERCICIO->setCoste(0);
                    $EJERCICIO->setEnunciado('Ejercicio de felicidad');
                    $EJERCICIO->setFecha($HOY);
                    $EJERCICIO->setIcono(null);
                    $EJERCICIO->setIdEjercicioSeccion($SECCION_FELICIDAD);
                    $EJERCICIO->setIdTipoEjercicio($TIPO_ENTREGA);
                    $em->persist($EJERCICIO);

                    if ($ANTERIOR_EJERCICIO_FELICIDAD !== null) {
                        $EJERCICIO_FELICIDAD = $ANTERIOR_EJERCICIO_FELICIDAD;
                        if (null === $EJERCICIO_FELICIDAD) {
                            $EJERCICIO_FELICIDAD = new \AppBundle\Entity\EjercicioFelicidad();
                        }
                    } else {
                        $EJERCICIO_FELICIDAD = new \AppBundle\Entity\EjercicioFelicidad();
                    }
                    $EJERCICIO_FELICIDAD->setEnunciado($TITULO);
                    $EJERCICIO_FELICIDAD->setFecha($HOY);
                    $EJERCICIO_FELICIDAD->setIdUsuario($USUARIO);
                    $EJERCICIO_FELICIDAD->setIdEjercicioEntrega(null);
                    $EJERCICIO_FELICIDAD->setIdEjercicioPropuesta($EJERCICIO);
                    $EJERCICIO_FELICIDAD->setFase($ID_FASE);
                    $EJERCICIO_FELICIDAD->setPorcentaje(0);
                    $em->persist($EJERCICIO_FELICIDAD);

                    if ($ANTERIOR_EJERCICIO_FELICIDAD !== null) {
                        $EJERCICIO_CALIFICACION = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findOneByIdEjercicio($EJERCICIO);
                        if (null === $EJERCICIO_CALIFICACION) {
                            $EJERCICIO_CALIFICACION = new \AppBundle\Entity\EjercicioCalificacion();
                        }
                    } else {
                        $EJERCICIO_CALIFICACION = new \AppBundle\Entity\EjercicioCalificacion();
                    }
                    $ENTREGADO = $doctrine->getRepository('AppBundle:EjercicioEstado')->findOneByEstado('entregado');

                    $EJERCICIO_CALIFICACION->setFecha($HOY);
                    $EJERCICIO_CALIFICACION->setIdCalificaciones(null);
                    $EJERCICIO_CALIFICACION->setIdEjercicio($EJERCICIO);
                    $EJERCICIO_CALIFICACION->setIdEjercicioEstado($ENTREGADO);
                    $EJERCICIO_CALIFICACION->setIdGrupo(null);
                    $EJERCICIO_CALIFICACION->setIdEvaluador(null);
                    $EJERCICIO_CALIFICACION->setIdUsuario($USUARIO);
                    $em->persist($EJERCICIO_CALIFICACION);

                    if ($ANTERIOR_EJERCICIO_FELICIDAD !== null) {
                        $EJERCICIO_ENTREGA = $doctrine->getRepository('AppBundle:EjercicioEntrega')->findOneByIdEjercicio($EJERCICIO);
                        if (null === $EJERCICIO_ENTREGA) {
                            $EJERCICIO_ENTREGA = new \AppBundle\Entity\EjercicioEntrega();
                        }
                    } else {
                        $EJERCICIO_ENTREGA = new \AppBundle\Entity\EjercicioEntrega();
                    }
                    $nombre_entrega = Utils::replaceAccented($ENTREGA->getClientOriginalName());
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
                    if ($ANTERIOR_EJERCICIO_FELICIDAD !== null) {
                        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => 'Propuesta actualizada')), 200);
                    } else {
                        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => 'Propuesta entregada correctamente')), 200);
                    }
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
     * @Route("/guardian/felicidad/getPropuestasFelicidad", name="getPropuestasFelicidad")
     */
    public function getPropuestasFelicidadAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/ajustes/getPremioMina', true);
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso denegado')), 200);
        }
        $DATOS = [];
        $FELICIDAD = $doctrine->getRepository('AppBundle:EjercicioFelicidad')->findAll();
        $DATOS['PROPUESTAS'] = [];
        if (count($FELICIDAD)) {
            foreach ($FELICIDAD as $EJERCICIO_FELICIDAD) {
                $aux = [];
                $aux['ID_FELICIDAD'] = $EJERCICIO_FELICIDAD->getIdEjercicioFelicidad();
                $aux['ENUNCIADO'] = $EJERCICIO_FELICIDAD->getEnunciado();
                $aux['FECHA_PROPUESTA'] = $EJERCICIO_FELICIDAD->getFecha();
                // DATOS DEL CIUDADANO
                $aux['CIUDADANO'] = [];
                $aux['CIUDADANO']['NOMBRE'] = $EJERCICIO_FELICIDAD->getIdUsuario()->getNombre();
                $aux['CIUDADANO']['APELLIDOS'] = $EJERCICIO_FELICIDAD->getIdUsuario()->getApellidos();
                $aux['CIUDADANO']['DNI'] = $EJERCICIO_FELICIDAD->getIdUsuario()->getDni();
                $aux['CIUDADANO']['ALIAS'] = $EJERCICIO_FELICIDAD->getIdUsuario()->getSeudonimo();

                // DATOS DE LA PROPUESTA
                $aux['PROPUESTA'] = [];
                $PROPUESTA = $EJERCICIO_FELICIDAD->getIdEjercicioPropuesta();
                $PROPUESTA_CALIFICACION = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findOneByIdEjercicio($PROPUESTA);
                $aux['PROPUESTA']['ESTADO'] = $PROPUESTA_CALIFICACION->getIdEjercicioEstado()->getEstado();
                if ($aux['PROPUESTA']['ESTADO'] === "evaluado") {
                    $aux['PROPUESTA']['CALIFICACION'] = 0;
                    $APTO = $doctrine->getRepository('AppBundle:Calificaciones')->findOneByIdCalificaciones(1);
                    if ($PROPUESTA_CALIFICACION->getIdCalificaciones() === $APTO) {
                        $aux['PROPUESTA']['CALIFICACION'] = 1;
                    }
                }
                $EJERCICIO_ENTREGA = $doctrine->getRepository('AppBundle:EjercicioEntrega')->findOneByIdEjercicio($PROPUESTA);
                $aux['PROPUESTA']['NOMBRE_ENTREGA'] = $EJERCICIO_ENTREGA->getNombre();
                $aux['PROPUESTA']['RUTA_ENTREGA'] = $aux['CIUDADANO']['DNI'] . '/felicidad/' . $PROPUESTA_CALIFICACION->getIdEjercicioCalificacion() . '/' . $aux['PROPUESTA']['NOMBRE_ENTREGA'];
            }
        }
        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $DATOS)), 200);
    }

    /**
     * 
     * @Route("/guardian/ejercicios/felicidad", name="felicidadGuardian")
     */
    public function felicidadGuardianAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        // Comprobamos que el usuario es admin, si no, redireccionamos a /
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/ejercicios/felicidad', true);
        if (!$status) {
            return new RedirectResponse('/');
        }
        $DATOS['TITULO'] = 'Felicidad';
        return $this->render('guardian/ejercicios/ejerciciosFelicidad.twig', $DATOS);
    }

    /**
     * 
     * @Route("/guardian/ejercicios/felicidad/calificar", name="setCalificacionFelicidad")
     */
    public function setCalificacionFelicidadAction(Request $request) {
        if ($request->getMethod() == 'POST') {
            $doctrine = $this->getDoctrine();
            $em = $doctrine->getManager();
            $session = $request->getSession();
            $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/ejercicios/felicidad/calificar', true);
            if (!$status) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado')), 200);
            }
            $idFelicidad = $request->request->get('idFelicidad');
            $porcentaje = $request->request->get('porcentaje');
            $EJERCICIO_FELICIDAD = $doctrine->getRepository('AppBundle:EjercicioFelicidad')->findOneByIdEjercicioFelicidad($idFelicidad);
            if (null === $EJERCICIO_FELICIDAD) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No existe el ejercicio')), 200);
            }
            $BONIFICACION = Utils::getConstante($doctrine, 'felicidadBonificacion' . $porcentaje);
            Usuario::operacionSobreTdV($doctrine, $EJERCICIO_FELICIDAD->getIdUsuario(), $BONIFICACION, 'Ingreso - Felicidad');
            $EJERCICIO_FELICIDAD->setPorcentaje($porcentaje);
            $em->persist($EJERCICIO_FELICIDAD);
            $em->flush();

            return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => 'Ciudadano evaluado correctamente')), 200);
        }
        return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No se han enviado datos')), 200);
    }

}
