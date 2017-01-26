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

/**
 * Description of TrabajoController
 *
 * @author araluce
 */
class TrabajoController extends Controller {

    /**
     * @Route("/guardian/ejercicios/inspeccion", name="ejerciciosInspeccion")
     */
    public function ejerciciosInspeccionAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        // Comprobamos que el usuario es admin, si no, redireccionamos a /
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/ejercicios/inspeccion', true);
        if (!$status) {
            return new RedirectResponse('/');
        }
        $DATOS['TITULO'] = 'Inspección';
        $DATOS['ULT_CAL'] = Utils::getUltimasCalificacionesSeccion($doctrine, 'inspeccion_trabajo');
        return $this->render('guardian/ejercicios/ejerciciosInspeccionTrabajo.twig', $DATOS);
    }

    /**
     * 
     * @Route("/guardian/ejercicios/inspeccion/publicar", name="inspeccionPublicarDeporte")
     */
    public function guardianPublicarInspeccionAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/ejercicios/inspeccion/publicar', true);
        if (!$status) {
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado'));
        }

        // Si se ha enviado un formulario
        if ($request->getMethod() == 'POST') {
            $em = $doctrine->getManager();
            $EJERCICIO_SECCION = $doctrine->getRepository('AppBundle:EjercicioSeccion')->findOneBySeccion('inspeccion_trabajo');
            if ($EJERCICIO_SECCION === null) {
                return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Sección no existe'));
            }
            $EJERCICIO_TIPO = $doctrine->getRepository('AppBundle:EjercicioTipo')->findOneByTipo('test');
            if ($EJERCICIO_TIPO === null) {
                return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Entrega no existe'));
            }
            // Obtenemos todos los enunciados del formulario
            $ENUNCIADO = $request->request->get('ENUNCIADO');
            // Obtenemos el coste del ejercicio
            $COSTE = $request->request->get('COSTE');
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
            $EJERCICIO->setCoste($COSTE);
            $em->persist($EJERCICIO);
            $em->flush();

            // Creamos las respuestas
            $RESPUESTAS = $request->request->get('RESPUESTAS');
            $checkbox = $request->request->get('RESPUESTAS_CHECK');
            $size = count($RESPUESTAS);
            for ($n = 0; $n < $size; $n++) {
                if ($checkbox[$n] !== 'on') {
                    $CORRECTA = 0;
                } else {
                    $CORRECTA = 1;
                }
                // Almacenamos cada respuesta en la BD asociadas al ejercicio
                $EJERCICIO_RESPUESTA = new \AppBundle\Entity\EjercicioRespuesta();
                $EJERCICIO_RESPUESTA->setIdEjercicio($EJERCICIO);
                $EJERCICIO_RESPUESTA->setRespuesta($RESPUESTAS[$n]);
                $EJERCICIO_RESPUESTA->setCorrecta($CORRECTA);
                $em->persist($EJERCICIO_RESPUESTA);
            }
            $CALIFICACIONES = $doctrine->getRepository('AppBundle:Calificaciones')->findAll();
            foreach ($CALIFICACIONES as $CALIFICACION) {
                $b = $request->request->get('BONIFICACION_' . $CALIFICACION->getIdCalificaciones());
                $BONIFICACION = new \AppBundle\Entity\EjercicioBonificacion();
                $BONIFICACION->setIdEjercicio($EJERCICIO);
                $BONIFICACION->setIdCalificacion($CALIFICACION);
                $BONIFICACION->setBonificacion($b);
                $em->persist($BONIFICACION);
            }
            $em->flush();
            return new JsonResponse(array('estado' => 'OK', 'message' => 'Ejercicio publicado correctamente'));
        }
        return new JsonResponse(array('estado' => 'ERROR', 'message' => 'No se han enviado datos'));
    }

}
