<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Controller;

/**
 * Description of AlimentacionController
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

class AlimentacionController extends Controller {

    /**
     * @Route("/ciudadano/alimentacion", name="alimentacion")
     */
    public function alimentacion_ciudadanoAction(Request $request, $mensaje = null) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/alimentacion');
        if (!$status) {
            return new RedirectResponse('/');
        }
        if(!DataManager::infoUsu($doctrine, $session)){
            return new RedirectResponse('/ciudadano/info');
        }
        $DATOS = DataManager::setDefaultData($doctrine, 'Alimentación', $session);
        if ($mensaje !== null) {
            $DATOS['info'] = $mensaje['info'];
        }
        return $this->render('ciudadano/extensiones/alimentacion.html.twig', $DATOS);
    }

    /**
     * @Route("/ciudadano/alimentacion/comida", name="comida")
     */
    public function comidaAction(Request $request, $mensaje = null) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/alimentacion/comida');
        if (!$status) {
            return new RedirectResponse('/');
        }
        $DATOS = DataManager::setDefaultData($doctrine, 'Comida', $session);
        if ($mensaje !== null) {
            $DATOS['info']['message'] = $mensaje['info'];
        }
        $DATOS['max_size'] = $this->return_bytes(ini_get('upload_max_filesize'));
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get('id_usuario'));
        $SECCION = $doctrine->getRepository('AppBundle:EjercicioSeccion')->findOneBySeccion('comida');
        Alimentacion::getDatosAlimentacion($doctrine, $USUARIO, $DATOS, $SECCION);
        return $this->render('ciudadano/alimentacion/comida.twig', $DATOS);
    }

    /**
     * @Route("/ciudadano/alimentacion/bebida", name="bebida")
     */
    public function bebida(Request $request, $mensaje = null) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/alimentacion/comida');
        if (!$status) {
            return new RedirectResponse('/');
        }
        $DATOS = DataManager::setDefaultData($doctrine, 'Bebida', $session);
        if ($mensaje !== null) {
            $DATOS['info']['message'] = $mensaje['info'];
        }
        $DATOS['max_size'] = $this->return_bytes(ini_get('upload_max_filesize'));
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get('id_usuario'));
        $SECCION = $doctrine->getRepository('AppBundle:EjercicioSeccion')->findOneBySeccion('bebida');
        Alimentacion::getDatosAlimentacion($doctrine, $USUARIO, $DATOS, $SECCION);
        return $this->render('ciudadano/alimentacion/bebida.twig', $DATOS);
    }
    
    /**
     * @Route("/ciudadano/trabajo/alimentacion/obtenerCalificacion", name="obtenerCalificacionAlimentacion")
     */
    public function obtenerCalificacionAlimentacionAction(Request $request) {
        $session = $request->getSession();
        $doctrine = $this->getDoctrine();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/trabajo/alimentacion/obtenerCalificacion');
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado')), 200);
        }
        if ($request->getMethod() == 'POST') {
            $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get("id_usuario"));
            $id_ejercicio = $request->request->get('id_ejercicio');
            $EJERCICIO = $doctrine->getRepository('AppBundle:Ejercicio')->findOneByIdEjercicio($id_ejercicio);
            if (null === $EJERCICIO) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'El ejercicio no existe ' . $id_ejercicio)), 200);
            }
            $EJERCICIO_CALIFICACION = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findOneBy([
                'idUsuario' => $USUARIO, 'idEjercicio' => $EJERCICIO
            ]);
            $DATOS = [];
            $DATOS['ICONO'] = $EJERCICIO_CALIFICACION->getIdCalificaciones()->getCorrespondenciaIcono();
            $DATOS['TEXTO'] = $EJERCICIO_CALIFICACION->getIdCalificaciones()->getCorrespondenciaTexto();
            return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $DATOS)), 200);
        }
        return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No se han recibido datos')), 200);
    }

    /**
     * 
     * @Route("/getTiempoSinComer", name="tiempoSinComer")
     */
    public function getTiempoSinComerAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $id_usuario = $session->get('id_usuario');
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($id_usuario);
        if ($USUARIO === null) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado')), 200);
        }
        $tsc = $USUARIO->getTiempoSinComer();
        if ($tsc === null) {
            return new JsonResponse(json_encode(array('porcentaje' => 'null')), 200);
        }
        $TSC_DEFECTO = $doctrine->getRepository('AppBundle:Constante')->findOneByClave('tiempo_acabar_de_comer');
        $respuesta = Alimentacion::porcetajeEnergia($tsc, $TSC_DEFECTO);
        if($respuesta['porcentaje'] <= 0){
            Usuario::setDefuncion($doctrine, $USUARIO);
        }
        return new JsonResponse(json_encode($respuesta), 200);
    }

    /**
     * 
     * @Route("/getTiempoSinBeber", name="tiempoSinBeber")
     */
    public function getTiempoSinBeberAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $id_usuario = $session->get('id_usuario');
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($id_usuario);
        if ($USUARIO === null) {
            return new JsonResponse(json_encode(array('error' => 'El usuario no está logueado')), 200);
        }
        $tsb = $USUARIO->getTiempoSinBeber();
        if ($tsb === null) {
            return new JsonResponse(json_encode(array('porcentaje' => 'null')), 200);
        }
        $TSB_DEFECTO = $doctrine->getRepository('AppBundle:Constante')->findOneByClave('tiempo_acabar_de_beber');
        $respuesta = Alimentacion::porcetajeEnergia($tsb, $TSB_DEFECTO);
        if($respuesta['porcentaje'] <= 0){
            Usuario::setDefuncion($doctrine, $USUARIO);
        }
        return new JsonResponse(json_encode($respuesta), 200);
    }

    /**
     * @Route("/ciudadano/alimentacion/comida/tienda", name="tiendaComida")
     */
    public function tiendaComidaAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/alimentacion/comida/tienda');
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado')), 200);
        }
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get('id_usuario'));

        // Obtenemos los ejercicios de comida del usuario
        $SECCION_COMIDA = $doctrine->getRepository('AppBundle:EjercicioSeccion')->findOneBySeccion('comida');
        $EJERCICIOS_COMIDA_DEL_USUARIO = $doctrine->getRepository('AppBundle:EjercicioXUsuario')->findBy([
            'idUsu' => $USUARIO, 'idSeccion' => $SECCION_COMIDA
        ]);
        $DATOS = [];
        $DATOS['NUMERO_EJERCICIOS'] = count($EJERCICIOS_COMIDA_DEL_USUARIO);

        $DATOS['EJERCICIOS'] = [];
        $EJERCICIOS_CONSUMIDOS = [];
        // Si el usuario tiene ejercicios...
        if ($DATOS['NUMERO_EJERCICIOS']) {
            $DATOS['YA_SOLICITADO_INDIVIDUAL'] = Alimentacion::getSolicitadosComida($doctrine, $USUARIO);
            $DATOS['YA_SOLICITADO_DISTRITO'] = Alimentacion::getSolicitadosComida($doctrine, $USUARIO, true);
            foreach ($EJERCICIOS_COMIDA_DEL_USUARIO as $EJERCICIO_USUARIO) {
                $EJERCICIO = $EJERCICIO_USUARIO->getIdEjercicio();
                $EJERCICIOS_CONSUMIDOS[] = $EJERCICIO;
                $aux = [];
                $aux['VISTO'] = $EJERCICIO_USUARIO->getVisto();
                $aux['ID'] = $EJERCICIO->getIdEjercicio();
                $aux['ICONO'] = null;
                if ($EJERCICIO->getIcono() !== null) {
                    $aux['ICONO'] = $EJERCICIO->getIcono()->getNombreImg();
                }
                $aux['ESTADO'] = 'no_solicitado';
                $aux['ES_DISTRITO'] = Ejercicio::esEjercicioDistrito($doctrine, $EJERCICIO);
                $CALIFICACION = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findOneBy([
                    'idUsuario' => $USUARIO, 'idEjercicio' => $EJERCICIO
                ]);
                if ($CALIFICACION !== null) {
                    $aux['ESTADO'] = $CALIFICACION->getIdEjercicioEstado()->getEstado();
                }

                $DATOS['EJERCICIOS'][] = $aux;
            }
        }
        // Ejercicios no solicitados aún
        $EJERCICIOS = $doctrine->getRepository('AppBundle:Ejercicio')->findByIdEjercicioSeccion($SECCION_COMIDA);
        if (count($EJERCICIOS)) {
            foreach ($EJERCICIOS AS $E) {
                if (!in_array($E, $EJERCICIOS_CONSUMIDOS)) {
                    $aux = [];
                    $aux['VISTO'] = $EJERCICIO_USUARIO->getVisto();
                    $aux['ID'] = $EJERCICIO->getIdEjercicio();
                    $aux['ICONO'] = null;
                    if ($EJERCICIO->getIcono() !== null) {
                        $aux['ICONO'] = $EJERCICIO->getIcono()->getNombreImg();
                    }
                    $aux['ESTADO'] = 'no_solicitado';
                    $aux['ELEGIBLE'] = 1;
                    $DATOS['EJERCICIOS'][] = $aux;
                }
            }
        }
        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $DATOS)), 200);
    }

    /**
     * @Route("/ciudadano/alimentacion/bebida/tienda", name="tiendaBebida")
     */
    public function tiendaBebidaAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/alimentacion/bebida/tienda');
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado')), 200);
        }
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get('id_usuario'));

        // Obtenemos los ejercicios de comida del usuario
        $SECCION_BEBIDA = $doctrine->getRepository('AppBundle:EjercicioSeccion')->findOneBySeccion('bebida');
        $EJERCICIOS_AGUA_DEL_USUARIO = $doctrine->getRepository('AppBundle:EjercicioXUsuario')->findBy([
            'idUsu' => $USUARIO, 'idSeccion' => $SECCION_BEBIDA
        ]);
        $DATOS = [];
        $DATOS['NUMERO_EJERCICIOS'] = count($EJERCICIOS_AGUA_DEL_USUARIO);

        $DATOS['EJERCICIOS'] = [];
        $EJERCICIOS_CONSUMIDOS = [];
        $EJERCICIOS_ALEATORIOS = [];
        // Si el usuario tiene ejercicios...
        if ($DATOS['NUMERO_EJERCICIOS']) {
            $contador = 0;
            $DATOS['YA_SOLICITADO_INDIVIDUAL'] = Alimentacion::getSolicitadosBebida($doctrine, $USUARIO);
            $DATOS['YA_SOLICITADO_DISTRITO'] = Alimentacion::getSolicitadosBebida($doctrine, $USUARIO, true);
            foreach ($EJERCICIOS_AGUA_DEL_USUARIO as $EJERCICIO_USUARIO) {
                $EJERCICIO = $EJERCICIO_USUARIO->getIdEjercicio();
                $EJERCICIOS_CONSUMIDOS[] = $EJERCICIO;
                $aux = [];
                $aux['VISTO'] = $EJERCICIO_USUARIO->getVisto();
                $aux['ID'] = $EJERCICIO->getIdEjercicio();
                $aux['ICONO'] = null;
                if ($EJERCICIO->getIcono() !== null) {
                    $aux['ICONO'] = $EJERCICIO->getIcono()->getNombreImg();
                }
                $aux['ESTADO'] = 'no_solicitado';
                $aux['ES_DISTRITO'] = Ejercicio::esEjercicioDistrito($doctrine, $EJERCICIO);
                $CALIFICACION = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findOneBy([
                    'idUsuario' => $USUARIO, 'idEjercicio' => $EJERCICIO
                ]);
                if ($CALIFICACION !== null) {
                    $aux['ESTADO'] = $CALIFICACION->getIdEjercicioEstado()->getEstado();
                }

                $DATOS['EJERCICIOS'][] = $aux;
                if ($aux['ESTADO'] === 'no_solicitado' && !$aux['ES_DISTRITO']) {
                    $EJERCICIOS_ALEATORIOS[] = $aux;
                    $contador++;
                }
            }
            $aleatorio = rand(0, $contador);
            $DATOS['ALEATORIO'] = $EJERCICIOS_ALEATORIOS[$aleatorio];
        }
        // Ejercicios no solicitados aún
        $EJERCICIOS = $doctrine->getRepository('AppBundle:Ejercicio')->findByIdEjercicioSeccion($SECCION_BEBIDA);
        if (count($EJERCICIOS)) {
            foreach ($EJERCICIOS AS $E) {
                if (!in_array($E, $EJERCICIOS_CONSUMIDOS)) {
                    $aux = [];
                    $aux['VISTO'] = $EJERCICIO_USUARIO->getVisto();
                    $aux['ID'] = $EJERCICIO->getIdEjercicio();
                    $aux['ICONO'] = null;
                    if ($EJERCICIO->getIcono() !== null) {
                        $aux['ICONO'] = $EJERCICIO->getIcono()->getNombreImg();
                    }
                    $aux['ESTADO'] = 'no_solicitado';
                    $aux['ELEGIBLE'] = 1;
                    $DATOS['EJERCICIOS'][] = $aux;
                }
            }
        }
        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $DATOS)), 200);
    }

    /**
     * @Route("/ciudadano/alimentacion/comida/obtenerDetalle/{id}", name="obtenerDetalle")
     */
    public function obtenerDetalleAction(Request $request, $id) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/alimentacion/comida/obtenerDetalle/' . $id);
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado')), 200);
        }
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get('id_usuario'));
        $EJERCICIO = $doctrine->getRepository('AppBundle:Ejercicio')->findOneByIdEjercicio($id);
        if ($EJERCICIO === null) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'El ejercicio no existe')), 200);
        }
        $resp = [];
        $resp['ID'] = $EJERCICIO->getIdEjercicio();
        $resp['FECHA'] = $EJERCICIO->getFecha();
        $resp['ENUNCIADO'] = $EJERCICIO->getEnunciado();
        $resp['ES_DISTRITO'] = Ejercicio::esEjercicioDistrito($doctrine, $EJERCICIO);
        if ($resp['ES_DISTRITO']) {
            $resp['NUM_SOLICITANTES_DISTRITO'] = Alimentacion::numeroSolicitantes($doctrine, $EJERCICIO, $USUARIO->getIdDistrito());
            $resp['NUM_CIUDADANOS_DISTRITO'] = count(Distrito::getCiudadanosDistrito($doctrine, $USUARIO->getIdDistrito()));
        }
        $resp['COSTE'] = Utils::segundosToDias($EJERCICIO->getCoste());
        $resp['ICONO'] = null;
        if ($EJERCICIO->getIcono() !== null) {
            $resp['ICONO'] = $EJERCICIO->getIcono()->getNombreImg();
        }
        $CALIFICACION = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findOneBy([
            'idUsuario' => $USUARIO, 'idEjercicio' => $EJERCICIO
        ]);
        if ($CALIFICACION !== null) {
            $resp['ESTADO'] = $CALIFICACION->getIdEjercicioEstado()->getEstado();
        }
        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $resp)), 200);
    }

    /**
     * @Route("/ciudadano/alimentacion/comida/entregarAlimento", name="entregarAlimento")
     */
    public function entregarAlimentoAction(Request $request) {
        if ($request->getMethod() == 'POST') {
            $doctrine = $this->getDoctrine();
            $em = $doctrine->getManager();
            $session = $request->getSession();
            $id_ejercicio = $request->request->get('id_ejercicio');
            $ENTREGA = $request->files->get('entrega');
            $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/alimentacion/comida/entregarAlimento');
            if (!$status) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado')), 200);
            }
            $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get('id_usuario'));
            $EJERCICIO = $doctrine->getRepository('AppBundle:Ejercicio')->findOneByIdEjercicio($id_ejercicio);
            if ($EJERCICIO === null) {
                Utils::setError($doctrine, 0, 'El ejercicio con id: ' . $id_ejercicio . ' no existe (entregarAlimentoAction)', $USUARIO);
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Error inesperado')), 200);
            }
            $SECCION = $EJERCICIO->getIdEjercicioSeccion();
            if ($ENTREGA !== null) {
                if ($ENTREGA->getClientSize() < $ENTREGA->getMaxFilesize()) {
                    $CALIFICACION_MEDIA = $doctrine->getRepository('AppBundle:Calificaciones')->findOneByIdCalificaciones(4);
                    if (Ejercicio::esEjercicioDistrito($doctrine, $EJERCICIO)) {
                        $DISTRITO = $USUARIO->getIdDistrito();
                        if (!Alimentacion::tiempoEntreEntregas($doctrine, $SECCION, $USUARIO, $DISTRITO)) {
                            $tiempoEntreEntregas = Utils::getConstante($doctrine, 'diasDifEntregas');
                            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Tiempo mínimo entre entregas: ' . $tiempoEntreEntregas)), 200);
                        }
                        $CIUDADANOS = Distrito::getCiudadanosVivosDistrito($doctrine, $DISTRITO);
//                        return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => count($CIUDADANOS))), 200);
                        if (count($CIUDADANOS)) {
                            foreach ($CIUDADANOS as $CIUDADANO) {
                                $RESULTADOS = Utils::setNota($doctrine, $CIUDADANO, $EJERCICIO, $CALIFICACION_MEDIA);
                            }
                        }
                    } else {
                        if (!Alimentacion::tiempoEntreEntregas($doctrine, $SECCION, $USUARIO, null)) {
                            $tiempoEntreEntregas = Utils::getConstante($doctrine, 'diasDifEntregas');
                            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Tiempo mínimo entre entregas: ' . $tiempoEntreEntregas)), 200);
                        }
                        $RESULTADOS = Utils::setNota($doctrine, $USUARIO, $EJERCICIO, $CALIFICACION_MEDIA);
                    }
                    $RESULTADOS['SECCION'] = $SECCION->getSeccion();

                    $EJERCICIO_CALIFICACION = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findOneBy([
                        'idUsuario' => $USUARIO, 'idEjercicio' => $EJERCICIO
                    ]);

                    $ruta = 'USUARIOS/' . $USUARIO->getDni() . '/' . $SECCION->getSeccion() . '/' . $EJERCICIO_CALIFICACION->getIdEjercicioCalificacion();
                    if (!file_exists($ruta)) {
                        mkdir($ruta, 0777, true);
                    }
                    $nombre_entrega = $ENTREGA->getClientOriginalName();
                    $ENTREGA->move($ruta, $nombre_entrega);
                    $EJERCICIO_ENTREGA = $doctrine->getRepository('AppBundle:EjercicioEntrega')->findOneBy([
                        'idUsuario' => $USUARIO, 'idEjercicio' => $EJERCICIO
                    ]);
                    if ($EJERCICIO_ENTREGA === null) {
                        $EJERCICIO_ENTREGA = new \AppBundle\Entity\EjercicioEntrega();
                    }
                    $EJERCICIO_ENTREGA->setIdUsuario($USUARIO);
                    $EJERCICIO_ENTREGA->setIdEjercicio($EJERCICIO);
                    $EJERCICIO_ENTREGA->setNombre($ENTREGA->getClientOriginalName());
                    $EJERCICIO_ENTREGA->setMime($ENTREGA->getClientMimeType());
                    $EJERCICIO_ENTREGA->setFecha($EJERCICIO_CALIFICACION->getFecha());
                    $em->persist($EJERCICIO_ENTREGA);
                    $em->flush();
                    return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => 'Ejercicio entregado correctamente')), 200);
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
     * 
     * @Route("/guardian/ejercicios/alimentacion", name="ejerciciosAlimentacion")
     */
    public function ejerciciosAlimentacionAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        // Comprobamos que el usuario es admin, si no, redireccionamos a /
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/ejerciciosAlimentacion', true);
        if (!$status) {
            return new RedirectResponse('/');
        }
        $DATOS['TITULO'] = 'Alimentación';
        $SECCION_COMIDA = $doctrine->getRepository('AppBundle:EjercicioSeccion')->findOneBySeccion('comida');
        $SECCION_BEBIDA = $doctrine->getRepository('AppBundle:EjercicioSeccion')->findOneBySeccion('bebida');
        if ($SECCION_COMIDA !== null) {
            $DATOS['ULT_CAL_C'] = Utils::getUltimasCalificacionesSeccion($doctrine, 'comida');
            $DATOS['ICONOS_COMIDA'] = $doctrine->getRepository('AppBundle:EjercicioIcono')->findBySeccion($SECCION_COMIDA);
        }
        if ($SECCION_BEBIDA !== null) {
            $DATOS['ULT_CAL_B'] = Utils::getUltimasCalificacionesSeccion($doctrine, 'bebida');
            $DATOS['ICONOS_BEBIDA'] = $doctrine->getRepository('AppBundle:EjercicioIcono')->findBySeccion($SECCION_BEBIDA);
        }
        return $this->render('guardian/ejercicios/ejerciciosAlimentacion.twig', $DATOS);
    }

    /**
     * 
     * @Route("/guardian/ejercicios/alimentacion/publicar", name="guardianPublicarAlimentacion")
     */
    public function guardianPublicarAlimentacionAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/ejercicios/alimentacion/publicar', true);
        if (!$status) {
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado'));
        }

        // Si se ha enviado un formulario
        if ($request->getMethod() == 'POST') {
            $em = $doctrine->getManager();
            // Obtenemos la sección donde se va a publicar el ejercicio
            $SECCION = $request->request->get('SECCION');
            // Obtenemos el objeto asociado a esa sección
            $EJERCICIO_SECCION = $doctrine->getRepository('AppBundle:EjercicioSeccion')->findOneBySeccion($SECCION);
            if ($EJERCICIO_SECCION === null) {
                return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Seccion no existe'));
            }
            $EJERCICIO_TIPO = $doctrine->getRepository('AppBundle:EjercicioTipo')->findOneByTipo('entrega');
            if ($EJERCICIO_TIPO === null) {
                return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Entrega no existe'));
            }
            // Obtenemos todos los enunciados del formulario
            $ENUNCIADO = $request->request->get('ENUNCIADO');
            // Obtenemos el icono del ejercicio
            $ICONO = $doctrine->getRepository('AppBundle:EjercicioIcono')->findOneByIdEjercicioIcono($request->request->get('ICONO'));
            if ($ICONO === null) {
                return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Icono no existe'));
            }
            // Obtenemos el coste del ejercicio
            $COSTE = $request->request->get('COSTE');
            // Obtenemos si es un ejercicio de distrito o no
            $ES_DISTRITO = $request->request->get('DISTRITO');
            // Buscamos si el enunciado ya existía para ese tipo y esa sección
            $EJERCICIO = $doctrine->getRepository('AppBundle:Ejercicio')->findOneBy([
                'idEjercicioSeccion' => $EJERCICIO_SECCION,
                'enunciado' => $ENUNCIADO
            ]);
            // Si el ejercicio no existe se crea uno nuevo
            if ($EJERCICIO === null) {
                $EJERCICIO = new \AppBundle\Entity\Ejercicio();
            }
            $EJERCICIO->setIdTipoEjercicio($EJERCICIO_TIPO);
            $EJERCICIO->setIdEjercicioSeccion($EJERCICIO_SECCION);
            $EJERCICIO->setEnunciado($ENUNCIADO);
            $EJERCICIO->setFecha(new \DateTime('now'));
            $EJERCICIO->setIcono($ICONO);
            $EJERCICIO->setCoste($COSTE);
            $em->persist($EJERCICIO);
            $em->flush();

            $CALIFICACIONES = $doctrine->getRepository('AppBundle:Calificaciones')->findAll();
            foreach ($CALIFICACIONES as $CALIFICACION) {
                $b = $request->request->get('BONIFICACION_' . $CALIFICACION->getIdCalificaciones());
                $BONIFICACION = new \AppBundle\Entity\EjercicioBonificacion();
                $BONIFICACION->setIdEjercicio($EJERCICIO);
                $BONIFICACION->setIdCalificacion($CALIFICACION);
                $BONIFICACION->setBonificacion($b);
                $em->persist($BONIFICACION);
            }
            // Si es un ejercicio de tipo distrito
            if ($ES_DISTRITO) {
                $EJERCICIO_DISTRITO = new \AppBundle\Entity\EjercicioDistrito();
                $EJERCICIO_DISTRITO->setIdEjercicio($EJERCICIO);
                $em->persist($EJERCICIO_DISTRITO);
            }
            $em->flush();
            return new JsonResponse(array('estado' => 'OK', 'message' => 'Ejercicio publicado correctamente'));
        }
        return new JsonResponse(array('estado' => 'ERROR', 'message' => 'No se han enviado datos'));
    }

    /**
     * 
     * @Route("/guardian/test/{SECCION}/{ENUNCIADO}/{ICONO}/{COSTE}", name="testGuardianAction")
     */
    public function testGuardianAction(Request $request, $SECCION, $ENUNCIADO, $ICONO, $COSTE) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/ejercicios/alimentacion/publicar', true);
        if (!$status) {
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado'));
        }

        $em = $doctrine->getManager();
        // Obtenemos el objeto asociado a esa sección
        $EJERCICIO_SECCION = $doctrine->getRepository('AppBundle:EjercicioSeccion')->findOneBySeccion($SECCION);
        if ($EJERCICIO_SECCION === null) {
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Seccion no existe'));
        }
        $EJERCICIO_TIPO = $doctrine->getRepository('AppBundle:EjercicioTipo')->findOneByTipo('entrega');
        if ($EJERCICIO_TIPO === null) {
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Entrega no existe'));
        }
        // Obtenemos el icono del ejercicio
        $ICONO = $doctrine->getRepository('AppBundle:EjercicioIcono')->findOneByIdEjercicioIcono($ICONO);
        if ($ICONO === null) {
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Icono no existe'));
        }
        // Buscamos si el enunciado ya existía para ese tipo y esa sección
        $EJERCICIO = $doctrine->getRepository('AppBundle:Ejercicio')->findOneBy([
            'idEjercicioSeccion' => $EJERCICIO_SECCION,
            'enunciado' => $ENUNCIADO
        ]);
        // Si el ejercicio no existe se crea uno nuevo
        if ($EJERCICIO === null) {
            $EJERCICIO = new \AppBundle\Entity\Ejercicio();
            $EJERCICIO->setIdTipoEjercicio($EJERCICIO_TIPO);
            $EJERCICIO->setIdEjercicioSeccion($EJERCICIO_SECCION);
            $EJERCICIO->setEnunciado($ENUNCIADO);
            $EJERCICIO->setFecha(new \DateTime('now'));
            $EJERCICIO->setIcono($ICONO);
            $EJERCICIO->setCoste($COSTE);
            $em->persist($EJERCICIO);
            $em->flush();
        } else {
            
        }
        return new JsonResponse(array('estado' => 'OK', 'message' => 'Ejercicio publicado correctamente'));
    }

    /**
     * @Route("/ciudadano/alimentacion/comida/retirarSolicitud/{id_ejercicio}", name="retirarSolicitud")
     */
    public function retirarSolicitudAction(Request $request, $id_ejercicio) {
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/alimentacion/comida/retirarSolicitud/' . $id_ejercicio);
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado')));
        }
        $id_usuario = $session->get('id_usuario');
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($id_usuario);
        $EJERCICIO = $doctrine->getRepository('AppBundle:Ejercicio')->findOneByIdEjercicio($id_ejercicio);
        if ($EJERCICIO === null) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Este ejercicio no existe')));
        }
        $coste = $EJERCICIO->getCoste();
        $estadoSolicitado = $doctrine->getRepository('AppBundle:EjercicioEstado')->findOneByEstado('solicitado');

        $CALIFICACION = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findOneBy([
            'idUsuario' => $USUARIO, 'idEjercicio' => $EJERCICIO, 'idEjercicioEstado' => $estadoSolicitado
        ]);
        if ($CALIFICACION === null) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Este ejercicio no había sido solicitado')));
        }

        $em->remove($CALIFICACION);
        $em->flush();
        Usuario::operacionSobreTdV($doctrine, $USUARIO, $coste, 'Ingreso - Reingreso al revocar solicitud');
        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => 'Solicitud retirada')), 200);
    }

    public function return_bytes($val) {
        $val = trim($val);
        $last = strtolower($val[strlen($val) - 1]);
        switch ($last) {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }
        return $val;
    }

}
