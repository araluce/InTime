<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Controller;

/**
 * Description of ApuestaController
 *
 * @author araluce
 */
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ApuestaController extends Controller {

    /**
     * @Route("/ciudadano/ocio/apuestas", name="apuestasCiudadano")
     */
    public function apuestasCiudadanoAction(Request $request) {
        $DataManager = new \AppBundle\Utils\DataManager();
        $UsuarioClass = new \AppBundle\Utils\Usuario();
        $session = $request->getSession();
        $doctrine = $this->getDoctrine();
        $status = $UsuarioClass->compruebaUsuario($doctrine, $session, '/ciudadano/ocio/apuestas');
        if (!$status) {
            return new RedirectResponse('/');
        }
        $DATOS = $DataManager->setDefaultData($doctrine, 'Apuestas', $session);
        $DATOS['SECCION'] = 'APUESTAS';
        return $this->render('ciudadano/ocio/apuestas.twig', $DATOS);
    }

    /**
     * 
     * @Route("/ciudadano/ocio/apuestas/actualizar", name="actualizarApuestasCiudadanos")
     */
    public function actualizarApuestasCiudadanosAction(Request $request) {
        $session = $request->getSession();
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $qb = $em->createQueryBuilder();
        $UsuarioClass = new \AppBundle\Utils\Usuario();
        $status = $UsuarioClass->compruebaUsuario($doctrine, $session, '/ciudadano/ocio/apuestas/actualizar');
        if (!$status) {
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado'));
        }
        $resultado['resultado'] = 'OK';
        $APUESTAS_ACTUALES = [];

        $query = $qb->select('a')
                ->from('\AppBundle\Entity\Apuesta', 'a')
                ->orderBy('a.fecha', 'DESC');
        $APUESTAS = $query->getQuery()->getResult();
        if (!count($APUESTAS)) {
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'No hay apuestas'), 200);
        }
        foreach ($APUESTAS as $APUESTA) {
//            $aniadir = true;
            $aux = [];
            $aux['DESCRIPCION'] = $APUESTA->getDescripcion();
            $aux['ID'] = $APUESTA->getIdApuesta();
            $aux['ESTADO'] = $APUESTA->getDisponible();
            $aux['TIEMPO_TOTAL'] = 0;
            $aux['N_APUESTAS'] = 0;
            $APUESTA_POSIBILIDAD = $doctrine->getRepository('AppBundle:ApuestaPosibilidad')->findByidApuesta($APUESTA);
            foreach ($APUESTA_POSIBILIDAD as $POSIBILIDAD) {
//                if ($POSIBILIDAD->getResultado() === null) {
                $aux2 = [];

                $aux2['ENUNCIADO'] = $POSIBILIDAD->getPosibilidad();
                $aux2['ID'] = $POSIBILIDAD->getIdApuestaPosibilidad();
                $aux2['TdV'] = 0;
                $aux2['N_APUESTAS'] = 0;
                $aux2['APOSTADORES'] = [];
                $array_apostadores = [];
                $USUARIOS_APUESTA = $doctrine->getRepository('AppBundle:UsuarioApuesta')->findByIdApuestaPosibilidad($POSIBILIDAD);
                if (count($USUARIOS_APUESTA)) {
                    foreach ($USUARIOS_APUESTA as $USUARIO_APUESTA) {
                        $apostador = [];
                        $apostador['alias'] = $USUARIO_APUESTA->getIdUsuario()->getSeudonimo();
                        $apostador['TdV'] = $USUARIO_APUESTA->getTdvApostado();
                        if (in_array($apostador['alias'], $array_apostadores)) {
                            foreach ($aux2['APOSTADORES'] as $a) {
                                if ($a['alias'] === $apostador['alias']) {
                                    $a['TdV'] += $apostador['TdV'];
                                }
                            }
                        } else {
                            $aux2['APOSTADORES'][] = $apostador;
                            $array_apostadores[] = $apostador['alias'];
                            $aux2['N_APUESTAS'] += 1;
                            $aux['N_APUESTAS'] += 1;
                        }

                        $aux['TIEMPO_TOTAL'] += $USUARIO_APUESTA->getTdvApostado();
                        $aux2['TdV'] += $USUARIO_APUESTA->getTdvApostado();
                    }
                }
                $aux['POSIBILIDAD'][] = $aux2;
//                } else {
//                    $aniadir = false;
//                }
            }
//            if ($aniadir) {
            $APUESTAS_ACTUALES[] = $aux;
//            }
        }
        $resultado['apuestas'] = $APUESTAS_ACTUALES;
//        \AppBundle\Utils\Utils::pretty_print($APUESTAS_ACTUALES);
        return new JsonResponse($resultado, 200);
    }

    /**
     * @Route("/ciudadano/ocio/apuestas/apostar", name="apostar")
     */
    public function apostarAction(Request $request) {
        $UsuarioClass = new \AppBundle\Utils\Usuario();
        $session = $request->getSession();
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $status = $UsuarioClass->compruebaUsuario($doctrine, $session, '/ciudadano/ocio/apuestas/apostar');
        if (!$status) {
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Permiso denegado'), 200);
        }
        if ($request->getMethod() == 'POST') {
            $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get("id_usuario"));
            $segundos = $request->request->get('segundos');
            $minutos = $request->request->get('minutos');
            $horas = $request->request->get('horas');
            $dias = $request->request->get('dias');
            $id = $request->request->get('id');

            $OPCION_APUESTA = $doctrine->getRepository('AppBundle:ApuestaPosibilidad')->findOneByIdApuestaPosibilidad($id);
            if ($OPCION_APUESTA === null) {
                return new JsonResponse(array('estado' => 'ERROR', 'message' => 'No se ha encontrado la apuesta'), 200);
            }
            $TIEMPO = (((( ($dias * 24) + $horas ) * 60) + $minutos) * 60) + $segundos;
            if ($TIEMPO > 0) {
                $haApostado = $this->haApostado($session, $OPCION_APUESTA, $TIEMPO);
                if (!$haApostado) {
                    $APUESTA = new \AppBundle\Entity\UsuarioApuesta();
                    $APUESTA->setIdApuestaPosibilidad($OPCION_APUESTA);
                    $APUESTA->setIdUsuario($USUARIO);
                    $APUESTA->setTdvApostado($TIEMPO);
                    $em->persist($APUESTA);
                    $em->flush();
                    return new JsonResponse(array('estado' => 'OK', 'message' => 'La apuesta se ha realizado correctamente'), 200);
                }
                else if($haApostado === 2){
                    return new JsonResponse(array('estado' => 'OK', 'message' => 'Se ha actualizado la apuesta'), 200);
                }
                return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Ya se había realizado una apuesta anteriormente'), 200);
            }
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'No se ha apostado tiempo'), 200);
        }
    }

    public function haApostado($session, $OPCION_APUESTA, $TIEMPO) {
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $qb = $em->createQueryBuilder();

        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get("id_usuario"));
        $APUESTA_PRINCIPAL = $OPCION_APUESTA->getIdApuesta();
        $OPCIONES_APUESTA = $doctrine->getRepository('AppBundle:ApuestaPosibilidad')->findByIdApuesta($APUESTA_PRINCIPAL);
        foreach ($OPCIONES_APUESTA as $OA) {
            $query = $qb->select('ua')
                    ->from('\AppBundle\Entity\UsuarioApuesta', 'ua')
                    ->where('ua.idApuestaPosibilidad = :IdApuestaPosibilidad AND ua.idUsuario = :IdUsuario')
                    ->setParameters(['IdApuestaPosibilidad' => $OA, 'IdUsuario' => $USUARIO]);
            $APUESTA = $query->getQuery()->getOneOrNullResult();
            if ($APUESTA === null) {
                return 0;
            } else {
                if ($APUESTA->getIdApuestaPosibilidad() === $OPCION_APUESTA) {
                    $APUESTA->getTdvApostado($APUESTA->getTdvApostado() + $TIEMPO);
                    $em->persist($APUESTA);
                    $em->flush();
                    return 2;
                } else {
                    return 1;
                }
            }
        }
    }

}
