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
 * Description of PrestamosController
 *
 * @author araluce
 */
class PrestamosController extends Controller {

    /**
     * 
     * @Route("/ciudadano/prestamos", name="prestamos")
     */
    public function prestamosAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/prestamos');
        if (!$status) {
            return new RedirectResponse('/');
        }
        $id_usuario = $session->get('id_usuario');
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($id_usuario);
        $DEUDAS = $doctrine->getRepository('AppBundle:UsuarioPrestamo')->findBy([
            'idUsuario' => $USUARIO,
            'motivo' => 'prestamo'
        ]);
        $DATOS = DataManager::setDefaultData($doctrine, 'Préstamos', $session);
        $DATOS['INTERES'] = Utils::getConstante($doctrine, 'interes_prestamo');
        $DATOS['TIEMPO_MAX_PRESTADO'] = Utils::getConstante($doctrine, 'tiempo_max_prestamo');
        $DATOS['TIEMPO_MAX_PRESTADO_FORMATO'] = Utils::segundosToDias($DATOS['TIEMPO_MAX_PRESTADO']);
        if (count($DEUDAS)) {
            $DATOS['DEUDAS'] = [];
            foreach ($DEUDAS as $DEUDA) {
                $aux = [];
                $aux['ID'] = $DEUDA->getIdUsuarioPrestamo();
                $aux['CANTIDAD'] = Utils::segundosToDias($DEUDA->getCantidad());
                $aux['RESTANTE'] = Utils::segundosToDias($DEUDA->getRestante());
                $aux['FECHA'] = $DEUDA->getFecha();
                $aux['ESTADO'] = $DEUDA->getRestante() > 0;
                $DATOS['DEUDAS'][] = $aux;
            }
        }
        return $this->render('ciudadano/prestamos/prestamos.twig', $DATOS);
    }

    /**
     * 
     * @Route("/ciudadano/prestamos/solicitarPrestamo", name="solicitarPrestamo")
     */
    public function solicitarPrestamoAction(Request $request) {
        if ($request->getMethod() == 'POST') {
            $doctrine = $this->getDoctrine();
            $em = $doctrine->getManager();
            $session = $request->getSession();
            $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/prestamos/solicitarPrestamo');
            if (!$status) {
                return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado'), 200);
            }
            $id_usuario = $session->get('id_usuario');
            $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($id_usuario);
            $alias = Usuario::aliasODesconocido($USUARIO);
            if($alias === 'desconocido'){
                return new JsonResponse(array('estado' => 'ERROR', 'message' => 'No te conozco amigo. Registra '
                    . 'tu alias en información y luego hablaremos.'), 200);
            }
            
            $tiempo = $request->request->get('tiempo');
            $DEUDA = $doctrine->getRepository('AppBundle:UsuarioPrestamo')->findOneBy([
                'idUsuario' => $USUARIO,
                'motivo' => 'prestamo'
            ]);
            $crear = false;
            if ($DEUDA === null) {
                $crear = true;
            } else {
                if ($DEUDA->getRestante() === 0) {
                    $crear = true;
                }
            }
            if ($crear) {
                $PRESTAMO = new \AppBundle\Entity\UsuarioPrestamo();
                $PRESTAMO->setIdUsuario($USUARIO);
                $PRESTAMO->setMotivo('prestamo');
                $PRESTAMO->setCantidad($tiempo);
                $INTERES = Utils::getConstante($doctrine, 'interes_prestamo');
                $PENDIENTE = $tiempo + ($tiempo * $INTERES);
                $PRESTAMO->setRestante($PENDIENTE);
                $PRESTAMO->setFecha(new \DateTime('now'));
                $em->persist($PRESTAMO);
                $em->flush();

                Usuario::operacionSobreTdV($doctrine, $USUARIO, $tiempo, 'Ingreso - Préstamo solicitado');
                return new JsonResponse(array(
                    'estado' => 'OK',
                    'message' => 'Muy bien @' . $alias . '... ahora eres mío. '
                    . 'Iré cobrándote mi parte diariamente hasta que... bueno, '
                    . 'hasta que tenga lo que me corresponde.'
                        ), 200);
            } else {
                $DEUDA_FORMATO = Utils::segundosToDias($DEUDA->getRestante());
                return new JsonResponse(array(
                    'estado' => 'ERROR',
                    'message' => 'Aún tienes una deuda conmigo... '
                    . 'Me debes ' . $DEUDA_FORMATO['dias'] . 'd ' . $DEUDA_FORMATO['horas'] . 'h '
                    . $DEUDA_FORMATO['minutos'] . 'm ' . $DEUDA_FORMATO['segundos'] . 's. '
                    . 'Para solicitar un nuevo préstamo debes liquidarla.'
                        ), 200);
            }
        }
        return new JsonResponse(array('estado' => 'ERROR', 'message' => 'No se ha recibido ningún dato'), 200);
    }

    /**
     * 
     * @Route("/ciudadano/prestamos/liquidarPrestamo", name="liquidarPrestamo")
     */
    public function liquidarPrestamoAction(Request $request) {
        if ($request->getMethod() == 'POST') {
            $doctrine = $this->getDoctrine();
            $em = $doctrine->getManager();
            $session = $request->getSession();
            $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/prestamos/liquidarPrestamo');
            if (!$status) {
                return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado'), 200);
            }
            $id_usuario = $session->get('id_usuario');
            $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($id_usuario);
            $idDeuda = $request->request->get('id');
            $DEUDA = $doctrine->getRepository('AppBundle:UsuarioPrestamo')->findOneBy([
                'idUsuarioPrestamo' => $idDeuda,
                'idUsuario' => $USUARIO,
                'motivo' => 'prestamo'
            ]);
            if ($DEUDA === null) {
                return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Creo que esta deuda no te corresponde. Otra vez será'), 200);
            }
            $alias = Usuario::aliasODesconocido($USUARIO);
            if (Usuario::puedoRealizarTransaccion($doctrine, $session, $DEUDA->getRestante())) {
                Usuario::operacionSobreTdV($doctrine, $USUARIO, (-1)*$DEUDA->getRestante(), 'Cobro - Liquidación total de deuda', true);
                $DEUDA->setRestante(0);
                $em->persist($DEUDA);
                $em->flush();
                
                return new JsonResponse(array(
                    'estado' => 'OK',
                    'message' => 'Tu deuda ha quedado saldada @' .$alias. '.'
                        ), 200);
            }
            return new JsonResponse(array(
                    'estado' => 'ERROR',
                    'message' => 'No tienes suficiente @' .$alias. '...'
                        ), 200);
        }
        return new JsonResponse(array('estado' => 'ERROR', 'message' => 'No se ha recibido ningún dato'), 200);
    }

}
