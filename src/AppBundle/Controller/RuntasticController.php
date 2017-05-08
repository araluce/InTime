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
use AppBundle\Utils\Ejercicio;
use AppBundle\Utils\RuntasticUtils;

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

        $RUNING_DURACION = 0;
        $RUNING_VELOCIDAD = 0;
        $RUNING_CONTADOR = 0;
        $BICI_DURACION = 0;
        $BICI_VELOCIDAD = 0;
        $BICI_CONTADOR = 0;


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
                    $aux['fecha'] = $SESION->getFecha();
                    if (Utils::estaSemana($aux['fecha'])) {
                        $aux['tipo'] = $SESION->getTipo();
                        $duracion = floor($SESION->getDuracion() / 1000);
                        $aux['duracion'] = Utils::formatoDuracion($duracion);
                        $aux['velocidad'] = $SESION->getVelocidad();
                        $DATOS['SESIONES'][] = $aux;
                        if ($aux['tipo'] === 'running') {
                            $RUNING_DURACION += $SESION->getDuracion();
                            $RUNING_VELOCIDAD += $aux['velocidad'];
                            $RUNING_CONTADOR++;
                        } else if ($aux['tipo'] === 'racecycling') {
                            $BICI_DURACION += $SESION->getDuracion();
                            $BICI_VELOCIDAD += $aux['velocidad'];
                            $BICI_CONTADOR++;
                        }
                    }
                }
            }
        }
        if ($RUNING_VELOCIDAD !== 0) {
            $RUNING_VELOCIDAD /= $RUNING_CONTADOR;
        }
        if ($BICI_VELOCIDAD !== 0) {
            $BICI_VELOCIDAD /= $BICI_CONTADOR;
        }
        $EJERCICIOS = $doctrine->getRepository('AppBundle:EjercicioRuntastic')->findAll();
        if (count($EJERCICIOS)) {
            $DATOS['EJERCICIO'] = [];
            foreach ($EJERCICIOS as $EJERCICIO) {
                if (Utils::estaSemana($EJERCICIO->getFecha())) {
                    $DATOS['EJERCICIO']['tipo'] = $EJERCICIO->getTipo();
                    $DATOS['EJERCICIO']['velocidad'] = $EJERCICIO->getVelocidad();
                    $DATOS['EJERCICIO']['duracion'] = Utils::formatoDuracion($EJERCICIO->getDuracion());
                    if ($DATOS['EJERCICIO']['tipo'] === 'running') {
                        if ($RUNING_VELOCIDAD !== 0.0) {
                            $DATOS['EJERCICIO']['velocidad_ac'] = $RUNING_VELOCIDAD;
                        } else {
                            $DATOS['EJERCICIO']['velocidad_ac'] = '-';
                        }
                        if ($RUNING_DURACION !== 0.0) {
                            $duracion = floor($RUNING_DURACION / 1000);
                            $DATOS['EJERCICIO']['duracion_ac'] = Utils::formatoDuracion($duracion);
                        } else {
                            $DATOS['EJERCICIO']['duracion_ac'] = '-';
                        }
                    } else {
                        if ($DATOS['EJERCICIO']['tipo'] === 'racecycling') {
                            if ($BICI_VELOCIDAD !== 0.0) {
                                $DATOS['EJERCICIO']['velocidad_ac'] = $BICI_VELOCIDAD;
                            } else {
                                $DATOS['EJERCICIO']['velocidad_ac'] = '-';
                            }
                            if ($BICI_DURACION !== 0.0) {
                                $duracion = floor($BICI_DURACION / 1000);
                                $DATOS['EJERCICIO']['duracion_ac'] = Utils::formatoDuracion($duracion);
                            } else {
                                $DATOS['EJERCICIO']['duracion_ac'] = '-';
                            }
                        }
                    }
                }
            }
            if (!count($DATOS['EJERCICIO'])) {
                unset($DATOS['EJERCICIO']);
            }
        }
        return $this->render('ciudadano/ocio/deporte.twig', $DATOS);
    }

    /**
     * @Route("/ciudadano/ocio/deporte/getRetoDeporte", name="getRetoDeporte")
     */
    public function getRetoDeporteAction(Request $request) {
        $session = $request->getSession();
        $doctrine = $this->getDoctrine();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/ocio/deporte/getRetoDeporte');
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Permiso denegado')), 200);
        }
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get("id_usuario"));
        $DEPORTE = $doctrine->getRepository('AppBundle:EjercicioSeccion')->findOneBySeccion('deporte');
        $EJERCICIOS = $doctrine->getRepository('AppBundle:Ejercicio')->findByIdEjercicioSeccion($DEPORTE);
        if (!count($EJERCICIOS)) {
            return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => 'No hay retos disponibles')), 200);
        }
        $fase_min = 1000;
        $RETO = $doctrine->getRepository('AppBundle:Ejercicio')->findOneByIdEjercicioSeccion($DEPORTE);

        if ($RETO === 0) {
            return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => 'No hay retos disponibles')), 200);
        }
        if (null === $RETO) {
            return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => 'Todos los retos han sido superados')), 200);
        }
        $RETOS = $doctrine->getRepository('AppBundle:EjercicioRuntastic')->findByIdEjercicio($RETO);
        if (!count($RETOS)) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Error inesperado')), 200);
        }
        $BENEFICIO = $doctrine->getRepository('AppBundle:EjercicioBonificacion')->findOneByIdEjercicio($RETO);
        $DATOS = [];
        $DATOS['BENEFICIO'] = Utils::segundosToDias($BENEFICIO->getBonificacion());
        $DATOS['EJERCICIOS'] = [];
        foreach ($RETOS as $R) {
            $aux = [];
            $aux['TIPO'] = $R->getTipo();
            $aux['VELOCIDAD'] = $R->getVelocidad();
            $aux['RITMO'] = $R->getRitmo();
            $aux['DURACION'] = Utils::formatoDuracion($R->getDuracion());
            $DATOS['EJERCICIOS'][] = $aux;
        }
        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $DATOS)), 200);
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
                return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Un ciudadano del Gueto de Feni está usando actualmente esta cuenta'), 200);
            }
            $r = new Runtastic();
            $r->setUsername($usuario_runtastic)->setPassword($password_runtastic);
            if (!$r->login()) {
                $r->logout();
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
        $UR = $doctrine->getRepository('AppBundle:UsuarioRuntastic')->findByIdUsuario($USUARIO);
        if (!count($UR)) {
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Fallo al actualizar la información'), 200);
        }
        $actividades_semana = RuntasticUtils::actualizarSesionesRuntastic($doctrine, $UR);
        if (!count($actividades_semana)) {
            return new JsonResponse(['estatus' => 'ERROR', 'message' => 'No se han actualizado sus sesiones. '
                . 'Puede que hayas excedido el máximo de sesiones abiertas permitidas por Runtastic. Podría ayudar cerrar sesiones '
                . 'de Runtastic que tengas abiertas en los navegadores. Si esto no funciona prueba a cerrar y abrir la sesión de '
                . 'Runtastic en $inTime.'], 200);
        }

        return new JsonResponse(['estatus' => 'OK', 'message' => 'Datos descargados correctamente'], 200);
    }

    /**
     * @Route("/ciudadano/ocio/deporte/cerrarSesion", name="cerrarSesion")
     */
    public function cerrarSesionAction(Request $request) {
        $session = $request->getSession();
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/ocio/deporte/cerrarSesion');
        if (!$status) {
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Permiso denegado'), 200);
        }
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get("id_usuario"));
        $UR = $doctrine->getRepository('AppBundle:UsuarioRuntastic')->findOneBy([
            'idUsuario' => $USUARIO, 'activo' => 1
        ]);
        if ($UR === null) {
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'No hay sesiones iniciadas'), 200);
        }
        $UR->setActivo(0);
        $em->persist($UR);
        $em->flush();
        $r = new Runtastic();
        $r->setUsername($UR->getUsername())->setPassword($UR->getPassword());
        $r->logout();
        return new JsonResponse(['estatus' => 'OK', 'message' => 'Se ha cerrado la sesión'], 200);
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
        $r = new Runtastic();
        $r->setUsername($USUARIO_RUNTASTIC->getUsername())->setPassword($USUARIO_RUNTASTIC->getPassword());
        $r->login();
    }

    /**
     * @Route("/ciudadano/ocio/deporte/test", name="test")
     */
    public function testAction(Request $request) {
        $session = $request->getSession();
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $qb = $em->createQueryBuilder();
//        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/ocio/deporte/test');
//        if (!$status) {
//            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Permiso denegado'), 200);
//        }
        $r = new Runtastic();
        $r->setUsername('araluce@correo.ugr.es')->setPassword('102938');
        $week_activities = $r->getActivities();
        return new JsonResponse($week_activities, 200);
        if ($r->login()) {
            return new JsonResponse(['estatus' => 'OK', 'message' => 'Datos descargados correctamente'], 200);
        }
        return new JsonResponse(['estatus' => 'ERROR', 'message' => $r->getRedirectUrl()], 200);
    }

    /**
     * 
     * @Route("/guardian/ejercicios/deporte", name="ejerciciosDeporte")
     */
    public function ejerciciosDeporteAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        // Comprobamos que el usuario es admin, si no, redireccionamos a /
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/ejercicios/deporte', true);
        if (!$status) {
            return new RedirectResponse('/');
        }
        $DATOS['TITULO'] = 'Deporte';
        return $this->render('guardian/ejercicios/ejerciciosDeporte.twig', $DATOS);
    }

    /**
     * 
     * @Route("/guardian/ejercicios/deporte/publicar", name="guardianPublicarDeporte")
     */
    public function guardianPublicarDeporteAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/ejercicios/deporte/publicar', true);
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado')), 200);
        }

        // Si se ha enviado un formulario
        if ($request->getMethod() == 'POST') {
            $em = $doctrine->getManager();
            // Obtenemos todos los enunciados del formulario
            $VELOCIDAD_BICI = $request->request->get('VELOCIDAD_BICICLETA');
            $DURACION_BICI = $request->request->get('DURACION_BICICLETA');
            $RITMO_RUN = $request->request->get('VELOCIDAD_RUNNING');
            $DURACION_RUN = $request->request->get('DURACION_RUNNING');
            $BENEFICIO = $request->request->get('BENEFICIO_FASE');
            $FASE = $request->request->get('FASE');
            $OBLIGATORIO = $request->request->get('OBLIGATORIO');

            $SECCION_DEPORTE = $doctrine->getRepository('AppBundle:EjercicioSeccion')->findOneBySeccion('deporte');
            $TIPO_DEPORTE = $doctrine->getRepository('AppBundle:EjercicioTipo')->findOneByTipo('deporte');
            $EJERCICIO = new \AppBundle\Entity\Ejercicio();
            $EJERCICIO->setCoste(0);
            $EJERCICIO->setEnunciado('' . $FASE);
            $EJERCICIO->setFecha(new \DateTime('now'));
            $EJERCICIO->setIcono(null);
            $EJERCICIO->setIdEjercicioSeccion($SECCION_DEPORTE);
            $EJERCICIO->setIdTipoEjercicio($TIPO_DEPORTE);
            $em->persist($EJERCICIO);

            $NOTA_MEDIA = $doctrine->getRepository('AppBundle:Calificaciones')->findOneByIdCalificaciones(5);
            $BONIFICACION = new \AppBundle\Entity\EjercicioBonificacion();
            $BONIFICACION->setBonificacion($BENEFICIO);
            $BONIFICACION->setIdCalificacion($NOTA_MEDIA);
            $BONIFICACION->setIdEjercicio($EJERCICIO);
            $em->persist($BONIFICACION);

            $FASE_BICI = new \AppBundle\Entity\EjercicioRuntastic();
            $FASE_BICI->setDuracion($DURACION_BICI);
            $FASE_BICI->setTipo('cycling');
            $FASE_BICI->setVelocidad($VELOCIDAD_BICI);
            $FASE_BICI->setRitmo($RITMO_RUN);
            $FASE_BICI->setFecha(new \DateTime('now'));
            $FASE_BICI->setIdEjercicio($EJERCICIO);
            $FASE_BICI->setOpcional(1);
            $em->persist($FASE_BICI);

            $FASE_RUN = new \AppBundle\Entity\EjercicioRuntastic();
            $FASE_RUN->setDuracion($DURACION_RUN);
            $FASE_RUN->setTipo('running');
            $FASE_RUN->setVelocidad($VELOCIDAD_BICI);
            $FASE_RUN->setRitmo($RITMO_RUN);
            $FASE_RUN->setFecha(new \DateTime('now'));
            $FASE_RUN->setIdEjercicio($EJERCICIO);
            $FASE_RUN->setOpcional(1);
            $em->persist($FASE_RUN);

            $em->flush();
            return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => 'Fase publicada correctamente')), 200);
        }
        return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No se han enviado datos')), 200);
    }

    /**
     * 
     * @Route("/guardian/ejercicios/deporte/modificarReto", name="modificarReto")
     */
    public function modificarRetoAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/ejercicios/deporte/publicar', true);
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado')), 200);
        }

        // Si se ha enviado un formulario
        if ($request->getMethod() == 'POST') {
            $em = $doctrine->getManager();
            // Obtenemos todos los enunciados del formulario
            $VELOCIDAD_BICI = $request->request->get('RITMO_BICICLETA');
            $DURACION_BICI = $request->request->get('DURACION_BICICLETA');
            $RITMO_RUN = $request->request->get('VELOCIDAD_RUNNING');
            $DURACION_RUN = $request->request->get('DURACION_RUNNING');
            $BENEFICIO = $request->request->get('BENEFICIO_FASE');
            $ID = $request->request->get('ID');

            $EJERCICIO = $doctrine->getRepository('AppBundle:Ejercicio')->findOneByIdEjercicio($ID);
            if (null === $EJERCICIO) {
                $SECCION_DEPORTE = $doctrine->getRepository('AppBundle:EjercicioSeccion')->findOneBySeccion('deporte');
                $TIPO_DEPORTE = $doctrine->getRepository('AppBundle:EjercicioTipo')->findOneByTipo('deporte');
                $EJERCICIO = new \AppBundle\Entity\Ejercicio();
                $EJERCICIO->setCoste(0);
                $EJERCICIO->setEnunciado('Deporte');
                $EJERCICIO->setFecha(new \DateTime('now'));
                $EJERCICIO->setIcono(null);
                $EJERCICIO->setIdEjercicioSeccion($SECCION_DEPORTE);
                $EJERCICIO->setIdTipoEjercicio($TIPO_DEPORTE);
                $em->persist($EJERCICIO);

                $NOTA_MEDIA = $doctrine->getRepository('AppBundle:Calificaciones')->findOneByIdCalificaciones(5);
                $BONIFICACION = new \AppBundle\Entity\EjercicioBonificacion();
                $BONIFICACION->setBonificacion($BENEFICIO);
                $BONIFICACION->setIdCalificacion($NOTA_MEDIA);
                $BONIFICACION->setIdEjercicio($EJERCICIO);
                $em->persist($BONIFICACION);

                $FASE_BICI = new \AppBundle\Entity\EjercicioRuntastic();
                $FASE_BICI->setDuracion($DURACION_BICI);
                $FASE_BICI->setTipo('cycling');
                $FASE_BICI->setVelocidad($VELOCIDAD_BICI);
                $FASE_BICI->setRitmo($RITMO_RUN);
                $FASE_BICI->setFecha(new \DateTime('now'));
                $FASE_BICI->setIdEjercicio($EJERCICIO);
                $FASE_BICI->setOpcional(1);
                $em->persist($FASE_BICI);

                $FASE_RUN = new \AppBundle\Entity\EjercicioRuntastic();
                $FASE_RUN->setDuracion($DURACION_RUN);
                $FASE_RUN->setTipo('running');
                $FASE_RUN->setVelocidad($VELOCIDAD_BICI);
                $FASE_RUN->setRitmo($RITMO_RUN);
                $FASE_RUN->setFecha(new \DateTime('now'));
                $FASE_RUN->setIdEjercicio($EJERCICIO);
                $FASE_RUN->setOpcional(1);
                $em->persist($FASE_RUN);
            } else {
                $BONIFICACION = $doctrine->getRepository('AppBundle:EjercicioBonificacion')->findOneByIdEjercicio($EJERCICIO);
                $BONIFICACION->setBonificacion($BENEFICIO);
                $em->persist($BONIFICACION);

                $FASE_BICI = $doctrine->getRepository('AppBundle:EjercicioRuntastic')->findOneBy(['idEjercicio' => $ID, 'tipo' => 'cycling']);
                $FASE_BICI->setDuracion($DURACION_BICI);
                $FASE_BICI->setVelocidad($VELOCIDAD_BICI);
                $FASE_BICI->setRitmo($RITMO_RUN);
                $FASE_BICI->setOpcional(1);
                $em->persist($FASE_BICI);

                $FASE_RUN = $doctrine->getRepository('AppBundle:EjercicioRuntastic')->findOneBy(['idEjercicio' => $ID, 'tipo' => 'running']);
                $FASE_RUN->setDuracion($DURACION_RUN);
                $FASE_RUN->setVelocidad($VELOCIDAD_BICI);
                $FASE_RUN->setRitmo($RITMO_RUN);
                $FASE_RUN->setOpcional(1);
                $em->persist($FASE_RUN);
            }

            $em->flush();
            return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => 'Fase actualizada correctamente')), 200);
        }
        return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No se han enviado datos')), 200);
    }

    /**
     * 
     * @Route("/guardian/ejercicios/deporte/eliminarReto", name="eliminarReto")
     */
    public function eliminarRetoAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/ejercicios/deporte/eliminarReto', true);
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado')), 200);
        }

        // Si se ha enviado un formulario
        if ($request->getMethod() == 'POST') {
            $em = $doctrine->getManager();
            // Obtenemos todos los enunciados del formulario
            $id_reto = $request->request->get('ID');
            $EJERCICIO = $doctrine->getRepository('AppBundle:Ejercicio')->findOneByIdEjercicio($id_reto);
            if (null === $EJERCICIO) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Error inesperado')), 200);
            }
            $em->remove($EJERCICIO);
            $em->flush();
            return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => 'Fase eliminada')), 200);
        }
        return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No se han enviado datos')), 200);
    }

    /**
     * @Route("/ciudadano/ocio/deporte/getRetosDeporte", name="getRetosDeporte")
     */
    public function getRetosDeporteAction(Request $request) {
        $session = $request->getSession();
        $doctrine = $this->getDoctrine();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/ocio/deporte/getRetosDeporte');
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Permiso denegado')), 200);
        }
        $SECCION_DEPORTES = $doctrine->getRepository('AppBundle:EjercicioSeccion')->findOneBySeccion('deporte');
        $EJERCICIO = $doctrine->getRepository('AppBundle:Ejercicio')->findOneByIdEjercicioSeccion($SECCION_DEPORTES);
        if (null === $EJERCICIO) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No hay retos publicados')), 200);
        }
        $DATOS = [];
        $aux = [];
        $FASE_BICI = $doctrine->getRepository('AppBundle:EjercicioRuntastic')->findOneBy(['idEjercicio' => $EJERCICIO, 'tipo' => 'cycling']);
        $FASE_RUN = $doctrine->getRepository('AppBundle:EjercicioRuntastic')->findOneBy(['idEjercicio' => $EJERCICIO, 'tipo' => 'running']);
        $BONIFICACION = $doctrine->getRepository('AppBundle:EjercicioBonificacion')->findOneByIdEjercicio($EJERCICIO);
        $DATOS['ID'] = $EJERCICIO->getIdEjercicio();
        $DATOS['VELOCIDAD_BICI'] = $FASE_BICI->getVelocidad();
        $DATOS['DURACION_BICI'] = Utils::segundosToDias($FASE_BICI->getDuracion());
        $DATOS['RITMO_RUN'] = $FASE_RUN->getRitmo();
        $DATOS['DURACION_RUN'] = Utils::segundosToDias($FASE_RUN->getDuracion());
        $DATOS['BENEFICIO'] = Utils::segundosToDias($BONIFICACION->getBonificacion());
        $DATOS['OBLIGATORIO'] = 1;
        if ($FASE_BICI->getOpcional()) {
            $DATOS['OBLIGATORIO'] = 0;
        }
        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $DATOS)), 200);
    }

    /**
     * @Route("/ciudadano/ocio/deporte/getMisSesiones", name="getMisSesiones")
     */
    public function getMisSesionesAction(Request $request) {
        $session = $request->getSession();
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $qb = $em->createQueryBuilder();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/ocio/deporte/getMisSesiones');
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Permiso denegado')), 200);
        }
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get("id_usuario"));
        $query = $qb->select('ur')
                ->from('\AppBundle\Entity\UsuarioRuntastic', 'ur')
                ->where('ur.idUsuario = :IdUsuario')
                ->setParameters(['IdUsuario' => $USUARIO]);
        $CUENTAS_RUNTASTIC = $query->getQuery()->getResult();
        if (!count($CUENTAS_RUNTASTIC)) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Error inesperado')), 200);
        }
        $DATOS = [];
        $DATOS['COMPARATIVA'] = 0;
        $DEPORTE = $doctrine->getRepository('AppBundle:EjercicioSeccion')->findOneBySeccion('deporte');
        $EJERCICIO = $doctrine->getRepository('AppBundle:Ejercicio')->findOneByIdEjercicioSeccion($DEPORTE);
        if (null !== $EJERCICIO) {
            $DATOS['COMPARATIVA'] = 1;
            $RETOS = $doctrine->getRepository('AppBundle:EjercicioRuntastic')->findByIdEjercicio($EJERCICIO);
            if (!count($RETOS)) {
                $DATOS['COMPARATIVA'] = 0;
            }
        }
        $DATOS['SESIONES'] = [];
        $duracion_acumulada = 0;
        $id_sesiones = [];
        $n_sesiones = 1;
        foreach ($CUENTAS_RUNTASTIC as $CUENTA) {
            $SESIONES_RUNTASTIC = $doctrine->getRepository('AppBundle:SesionRuntastic')->findByIdUsuarioRuntastic($CUENTA);
            foreach ($SESIONES_RUNTASTIC as $SESION) {
                $aux = [];
                $aux['FECHA'] = $SESION->getFecha();
                if (Utils::estaSemana($aux['FECHA'])) {
                    $aux['TIPO'] = $SESION->getTipo();
                    $duracion = $SESION->getDuracion();
                    $aux['DURACION'] = Utils::formatoDuracion($duracion);
                    $aux['VELOCIDAD'] = $SESION->getVelocidad();
                    $aux['RITMO'] = $SESION->getRitmo();
                    if ($DATOS['COMPARATIVA']) {
                        $aux['VELOCIDAD_SUP'] = 0;
                        $aux['EVALUADO_FASE'] = 0;
                        $aux['RETO'] = [];
                        if (!$SESION->getEvaluado()) {
                            foreach ($RETOS as $RETO) {
                                $aux2 = [];
                                $aux2['TIPO'] = $RETO->getTipo();
                                $aux2['VELOCIDAD'] = $RETO->getVelocidad();
                                $aux2['RITMO'] = $RETO->getRitmo();
                                $aux['RETO'][] = $aux2;
                                if ($aux['TIPO'] === 'running') {
                                    if (($aux2['TIPO'] === $aux['TIPO'] && $aux2['RITMO'] >= $aux['RITMO'])) {
                                        $aux['VELOCIDAD_SUP'] = 1;
                                        $duracion_acumulada += $duracion;
                                        $id_sesiones[] = $SESION;
                                        if ($duracion_acumulada >= $RETO->getDuracion()) {
                                            $DATOS['EVALUADO'] = 1;
//                                        $n_sesiones++;
//                                        if ($n_sesiones >= 3) {
                                            //Ejercicio::evaluaFasePartes($doctrine, $EJERCICIO, $USUARIO, $id_sesiones);
//                                        }
                                        }
                                    }
                                }
                                if ($aux['TIPO'] === 'cycling') {
                                    if ($aux2['TIPO'] === $aux['TIPO'] && $aux2['VELOCIDAD'] <= $aux['VELOCIDAD']) {
                                        $aux['VELOCIDAD_SUP'] = 1;
                                        $duracion_acumulada += $duracion;
                                        $id_sesiones[] = $SESION;
                                        if ($duracion_acumulada >= $RETO->getDuracion()) {
                                            $DATOS['EVALUADO'] = 1;
//                                        $n_sesiones++;
//                                        if ($n_sesiones >= 3) {
                                            //Ejercicio::evaluaFasePartes($doctrine, $EJERCICIO, $USUARIO, $id_sesiones);
//                                        }
                                        }
                                    }
                                }
                            }
                        } else {
                            $aux['EVALUADO_FASE'] = 1;
                        }
                    }
                    $DATOS['SESIONES'][] = $aux;
                }
            }
        }
        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $DATOS)), 200);
    }

}
