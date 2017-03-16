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
use AppBundle\Utils\DataManager;
use AppBundle\Utils\Usuario;
use AppBundle\Utils\Utils;

class ApuestaController extends Controller {

    /**
     * @Route("/ciudadano/ocio/apuestas", name="apuestasCiudadano")
     */
    public function apuestasCiudadanoAction(Request $request) {
        $session = $request->getSession();
        $doctrine = $this->getDoctrine();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/ocio/apuestas');
        if (!$status) {
            return new RedirectResponse('/');
        }
        $DATOS = DataManager::setDefaultData($doctrine, 'Apuestas', $session);
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
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/ocio/apuestas/actualizar');
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado')));
        }
        $APUESTAS_ACTUALES = [];

        $query = $qb->select('a')
                ->from('\AppBundle\Entity\Apuesta', 'a')
                ->orderBy('a.fecha', 'DESC');
        $APUESTAS = $query->getQuery()->getResult();
        if (!count($APUESTAS)) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No hay apuestas')), 200);
        }
        foreach ($APUESTAS as $APUESTA) {
            $aux = [];
            $aux['DESCRIPCION'] = $APUESTA->getDescripcion();
            $aux['ID'] = $APUESTA->getIdApuesta();
            $aux['ESTADO'] = $APUESTA->getDisponible();
            $aux['TIEMPO_TOTAL'] = 0;
            $aux['N_APUESTAS'] = 0;
            $APUESTA_POSIBILIDAD = $doctrine->getRepository('AppBundle:ApuestaPosibilidad')->findByidApuesta($APUESTA);
            foreach ($APUESTA_POSIBILIDAD as $POSIBILIDAD) {
                $aux2 = [];
                $aux2['ENUNCIADO'] = $POSIBILIDAD->getPosibilidad();
                $aux2['ID'] = $POSIBILIDAD->getIdApuestaPosibilidad();
                $aux2['TdV'] = 0;
                $aux2['N_APUESTAS'] = 0;
                $aux2['APOSTADORES'] = [];
                $USUARIOS_APUESTA = $doctrine->getRepository('AppBundle:UsuarioApuesta')->findByIdApuestaPosibilidad($POSIBILIDAD);
                if (count($USUARIOS_APUESTA)) {
                    foreach ($USUARIOS_APUESTA as $USUARIO_APUESTA) {
                        $apostador = [];
                        $apostador['alias'] = $USUARIO_APUESTA->getIdUsuario()->getSeudonimo();
                        $apostador['TdV'] = Utils::segundosToDias($USUARIO_APUESTA->getTdvApostado());
                        $aux2['APOSTADORES'][] = $apostador;
                        $aux2['N_APUESTAS'] += 1;
                        $aux['N_APUESTAS'] += 1;

                        $aux['TIEMPO_TOTAL'] += $USUARIO_APUESTA->getTdvApostado();
                        $aux2['TdV'] += $USUARIO_APUESTA->getTdvApostado();
                    }
                    $aux2['TdV'] = Utils::segundosToDias($aux2['TdV']);
                }
                $aux['POSIBILIDAD'][] = $aux2;
            }
            $APUESTAS_ACTUALES[] = $aux;
        }
        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $APUESTAS_ACTUALES)), 200);
    }

    /**
     * @Route("/ciudadano/ocio/apuestas/apostar", name="apostar")
     */
    public function apostarAction(Request $request) {
        $session = $request->getSession();
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/ocio/apuestas/apostar');
        if (!$status) {
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Permiso denegado'), 200);
        }
        if ($request->getMethod() == 'POST') {
            $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get("id_usuario"));
            $TIEMPO = $request->request->get('apuesta');
            $id = $request->request->get('id');

            $OPCION_APUESTA = $doctrine->getRepository('AppBundle:ApuestaPosibilidad')->findOneByIdApuestaPosibilidad($id);
            if ($OPCION_APUESTA === null) {
                return new JsonResponse(array('estado' => 'ERROR', 'message' => 'No se ha encontrado la apuesta'), 200);
            }
            if ($OPCION_APUESTA->getIdApuesta()->getDisponible()) {
                return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Lo siento, esta apuesta acaba de cerrarse.'), 200);
            }
            if ($TIEMPO > 0) {
                $haApostado = Utils::haApostado($doctrine, $USUARIO, $OPCION_APUESTA);
                if ($haApostado === -1 || $haApostado) {
                    return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Ya se había realizado una apuesta anteriormente'), 200);
                }
//                if ($haApostado) {
//                    $APUESTA = $doctrine->getRepository('AppBundle:UsuarioApuesta')->findOneBy([
//                        'idApuestaPosibilidad' => $OPCION_APUESTA, 'idUsuario' => $USUARIO
//                    ]);
//                    $APUESTA->setTdvApostado($APUESTA->getTdvApostado() + $TIEMPO);
//                    $em->persist($APUESTA);
//                    $em->flush();
//                    return new JsonResponse(array('estado' => 'OK', 'message' => 'Se ha actualizado la apuesta'), 200);
//                }
                $APUESTA = new \AppBundle\Entity\UsuarioApuesta();
                $APUESTA->setIdApuestaPosibilidad($OPCION_APUESTA);
                $APUESTA->setIdUsuario($USUARIO);
                $APUESTA->setTdvApostado($TIEMPO);
                $em->persist($APUESTA);
                $em->flush();
                return new JsonResponse(array('estado' => 'OK', 'message' => 'La apuesta se ha realizado correctamente'), 200);
            }
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'No se ha apostado tiempo'), 200);
        }
    }

    /**
     * 
     * @Route("/guardian/apuestas/proponer", name="proponerApuesta")
     */
    public function proponerApuestaAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $em = $doctrine->getManager();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/apuestas/proponer', true);
        if (!$status) {
            return new RedirectResponse('/');
        }
        $DATOS = ['TITULO' => 'Apuestas - Proponer', 'SECCION' => 'APUESTAS', 'SUBSECCION' => 'PROPONER'];
        if ($request->getMethod() == 'POST') {
            $DESCRIPCION = $request->request->get('APUESTA');
            $GET_POSIBILIDADES = $request->request->get('POSIBILIDADES');
            $POSIBILIDADES = [];
            foreach ($GET_POSIBILIDADES as $POSIBILIDAD) {
                if (trim($POSIBILIDAD) !== '') {
                    $POSIBILIDADES[] = $POSIBILIDAD;
                }
            }
            if (count($POSIBILIDADES) > 1) {
                $APUESTA = new \AppBundle\Entity\Apuesta();
                $APUESTA->setDescripcion($DESCRIPCION);
                $APUESTA->setDisponible(0);
                $APUESTA->setFecha(new \DateTime('now'));
                $em->persist($APUESTA);

                foreach ($POSIBILIDADES as $POSIBILIDAD) {
                    $APUESTA_POSIBILIDAD = new \AppBundle\Entity\ApuestaPosibilidad();
                    $APUESTA_POSIBILIDAD->setPosibilidad($POSIBILIDAD);
                    $APUESTA_POSIBILIDAD->setIdApuesta($APUESTA);
                    $em->persist($APUESTA_POSIBILIDAD);
                }
                $em->flush();
            }
        }
        return $this->render('guardian/loteriasApuestas.twig', $DATOS);
    }

    /**
     * @Route("/guardian/apuestas/terminarApuesta/{id_apuesta_posibilidad}", name="terminarApuesta")
     */
    public function terminarApuestaAction(Request $request, $id_apuesta_posibilidad) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $em = $doctrine->getManager();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/apuestas/terminarApuesta/' . $id_apuesta_posibilidad, true);
        if (!$status) {
            return new RedirectResponse('/');
        }
        $POSIBILIDAD = $doctrine->getRepository('AppBundle:ApuestaPosibilidad')->findOneByIdApuestaPosibilidad($id_apuesta_posibilidad);
        $APUESTA = $POSIBILIDAD->getIdApuesta();
        $POSIBILIDADES = $doctrine->getRepository('AppBundle:ApuestaPosibilidad')->findByIdApuesta($APUESTA);
        foreach ($POSIBILIDADES as $P) {
            $P->setResultado(0);
            $em->persist($P);
        }
        $POSIBILIDAD->setResultado(1);
        $em->persist($POSIBILIDAD);
        $APUESTA->setDisponible(1);
        $em->persist($APUESTA);
        $em->flush();

        // Obtenemos todas las apuestas de usuarios de esta apuesta
        $APUESTAS_USUARIO_ALL = $doctrine->getRepository('AppBundle:UsuarioApuesta')->findAll();
        $APUESTAS_USUARIO_ESTA_APUESTA = [];
        $RECAUDACION = 0;
        // Si hay alguna apuesta
        if (count($APUESTAS_USUARIO_ALL)) {
            foreach ($APUESTAS_USUARIO_ALL AS $UA) {
                if (in_array($UA->getIdApuestaPosibilidad(), $POSIBILIDADES)) {
                    $APUESTAS_USUARIO_ESTA_APUESTA[] = $UA;
                    $RECAUDACION += $UA->getTdvApostado();
                    Usuario::operacionSobreTdV($doctrine, $UA->getIdUsuario(), $UA->getTdvApostado() * (-1), 'Cobro - Apuestas');
                }
            }

            // Obtenemos las apuestas ganadoras, calculamos las ganancias y las repartimos entre los ganadores
            $APUESTAS_GANADORAS = $doctrine->getRepository('AppBundle:UsuarioApuesta')->findByIdApuestaPosibilidad($POSIBILIDAD);
            if (count($APUESTAS_GANADORAS)) {
                $DISPARADOR_APUESTAS = $doctrine->getRepository('AppBundle:Constante')->findOneByClave('disparador_apuesta')->getValor();
                foreach ($APUESTAS_GANADORAS as $A) {
                    $GANANCIAS = round(($A->getTdvApostado() * $DISPARADOR_APUESTAS) / count($APUESTAS_GANADORAS));
                    Usuario::operacionSobreTdV($doctrine, $A->getIdUsuario(), $GANANCIAS, 'Ingreso - Apuesta ganadora');
                }
            }
        }

        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => 'Apuesta cerrada')), 200);
    }

    /**
     * @Route("/guardian/apuestas/pararApuesta/{id_apuesta}/{desactivar}", name="pararApuesta")
     */
    public function pararApuestaAction(Request $request, $id_apuesta, $desactivar) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $em = $doctrine->getManager();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/apuestas/pararApuesta/' . $id_apuesta . '/' . $desactivar, true);
        if (!$status) {
            return new RedirectResponse('/');
        }
        $APUESTA = $doctrine->getRepository('AppBundle:Apuesta')->findOneByIdApuesta($id_apuesta);
        if (null === $APUESTA) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No existe la apuesta' . $id_apuesta)), 200);
        }
        if (intval($desactivar) !== 0 && intval($desactivar) !== 1) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Error inesperado')), 200);
        }
        $APUESTA->setDisponible($desactivar);
        $em->persist($APUESTA);
        $em->flush();

        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => 'Apuesta parada')), 200);
    }

    /**
     * 
     * @Route("/guardian/apuestas/actualizarApuestas", name="actualizarApuestas")
     */
    public function actualizarApuestas() {
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $qb = $em->createQueryBuilder();
        $APUESTAS_ACTUALES = [];

        $query = $qb->select('a')
                ->from('\AppBundle\Entity\Apuesta', 'a');
        $APUESTAS = $query->getQuery()->getResult();
        if (!count($APUESTAS)) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No hay apuestas')), 200);
        }
        foreach ($APUESTAS as $APUESTA) {
            $aux = [];
            $aux['DESCRIPCION'] = $APUESTA->getDescripcion();
            $aux['ID'] = $APUESTA->getIdApuesta();
            $aux['ESTADO'] = $APUESTA->getDisponible();
            $aux['TIEMPO_TOTAL'] = 0;
            $aux['N_APUESTAS'] = 0;
            $APUESTA_POSIBILIDAD = $doctrine->getRepository('AppBundle:ApuestaPosibilidad')->findByidApuesta($APUESTA);
            foreach ($APUESTA_POSIBILIDAD as $POSIBILIDAD) {
                $aux2 = [];
                $aux2['ENUNCIADO'] = $POSIBILIDAD->getPosibilidad();
                $aux2['ID'] = $POSIBILIDAD->getIdApuestaPosibilidad();
                $aux2['TdV'] = 0;
                $aux2['N_APUESTAS'] = 0;
                $aux2['RESULTADO'] = $POSIBILIDAD->getResultado();
                $USUARIOS_APUESTA = $doctrine->getRepository('AppBundle:UsuarioApuesta')->findByIdApuestaPosibilidad($POSIBILIDAD);
                if (count($USUARIOS_APUESTA)) {
                    foreach ($USUARIOS_APUESTA as $USUARIO_APUESTA) {
                        $aux['TIEMPO_TOTAL'] += $USUARIO_APUESTA->getTdvApostado();
                        $aux2['TdV'] += $USUARIO_APUESTA->getTdvApostado();
                        $aux['N_APUESTAS'] += 1;
                        $aux2['N_APUESTAS'] += 1;
                    }
                }
                $aux['POSIBILIDAD'][] = $aux2;
            }
            $APUESTAS_ACTUALES[] = $aux;
        }
        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $APUESTAS_ACTUALES)), 200);
    }

}
