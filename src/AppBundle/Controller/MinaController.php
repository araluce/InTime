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
 * Description of MinaController
 *
 * @author araluce
 */
class MinaController extends Controller {

    /**
     * @Route("/ciudadano/ocio/altruismo/getMina", name="getMina")
     */
    public function getMinaAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/ocio/altruismo/getMina');
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Permiso denegado')), 200);
        }
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get("id_usuario"));
        if ($USUARIO->getSeudonimo() === null) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Para participar debes tener un alias.<br>Puedes crearte uno en la sección Jugador de la página principal')), 200);
        }
        if ($USUARIO->getIdDistrito() === null) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Para participar debes pertenecer a un distrito.<br> Solicita un distrito a tu Guardián del tiempo')), 200);
        }
        $MINA = Utils::minaActiva($doctrine);
        if (!$MINA) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Actualmente no hay minas que desactivar. El ingreso por desactivación de minas se hará sobre las 00:00')), 200);
        }
        $PISTAS_MINA = $doctrine->getRepository('AppBundle:MinaPista')->findByIdMina($MINA);
        $DATOS = [];
        $DATOS['HAY_PISTAS'] = count($PISTAS_MINA);
        if ($DATOS['HAY_PISTAS']) {
            $DATOS['PISTAS'] = [];
            $query = $doctrine->getRepository('AppBundle:MinaPistaXUsuario')->createQueryBuilder('a');
            $query->select('a');
            $query->where('a.idUsuario = :USUARIO AND a.idMinaPista IN (:PISTAS)');
            $query->setParameters(['USUARIO' => $USUARIO, 'PISTAS' => array_values($PISTAS_MINA)]);
            $MIS_PISTAS = $query->getQuery()->getResult();
            $DATOS['TENGO_PISTAS'] = count($MIS_PISTAS);
            if ($DATOS['TENGO_PISTAS']) {
                $DATOS['MIS_PISTAS'] = [];
                foreach ($MIS_PISTAS as $PISTA) {
                    $DATOS['MIS_PISTAS'][] = $PISTA->getIdMinaPista()->getPista();
                }
            }
            $DATOS['PISTAS_RESTANTES'] = $DATOS['HAY_PISTAS'] - $DATOS['TENGO_PISTAS'];
            if ($DATOS['PISTAS_RESTANTES']) {
                $DATOS['COSTE_PISTA'] = Utils::segundosToDias(Utils::getConstante($doctrine, 'coste_pista'));
            }
            $DATOS['BONIFICACION'] = 0;
            $CARTA = $doctrine->getRepository('AppBundle:BonificacionExtra')->findOneByIdBonificacionExtra(8);
            $MI_CARTA = $doctrine->getRepository('AppBundle:BonificacionXUsuario')->findOneBy([
                'idUsuario' => $USUARIO, 'idBonificacionExtra' => $CARTA, 'usado' => 0
            ]);
            if (null !== $MI_CARTA) {
                $DATOS['BONIFICACION'] = 1;
            }
        }

        $DATOS['ID_MINA'] = $MINA->getIdMina();
        $DATOS['ID_EJERCICIO'] = $MINA->getIdEjercicio()->getIdEjercicio();
        $DATOS['FECHA_FINAL'] = $MINA->getFechaFinal();
        $query = $doctrine->getRepository('AppBundle:UsuarioMina')->createQueryBuilder('a');
        $query->select('a');
        $query->where('a.idMina = :IdMina');
        $query->orderBy('a.fecha', 'DESC');
        $query->setParameters(['IdMina' => $MINA]);
        $USUARIOS_MINA = $query->getQuery()->getResult();

        $DATOS['DESACTIVADA'] = 0;
        $DATOS['DESACTIVADA_X_MI'] = 0;
        if (count($USUARIOS_MINA)) {
            $DATOS['DESACTIVADA'] = 1;
            $DATOS['DESACTIVADORES'] = [];
            foreach ($USUARIOS_MINA as $USUARIO_M) {
                $aux = [];
                $aux['ALIAS'] = $USUARIO_M->getIdUsuario()->getSeudonimo();
                $DATOS['DESACTIVADORES'][] = $aux;
                if ($USUARIO_M->getIdUsuario() === $USUARIO) {
                    $DATOS['DESACTIVADA_X_MI'] = 1;
                }
            }
        }
        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $DATOS)), 200);
    }

    /**
     * @Route("/ciudadano/ocio/altruismo/enviarCodigo", name="enviarCodigo")
     */
    public function enviarCodigoAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $em = $doctrine->getManager();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/ocio/altruismo/enviarCodigo');
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Permiso denegado')), 200);
        }
        if ($request->getMethod() == 'POST') {
            $CODIGO = strtolower($request->request->get('CODIGO'));
            $ID_MINA = $request->request->get('ID_MINA');
            $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get("id_usuario"));
            $MINA = $doctrine->getRepository('AppBundle:Mina')->findOneByIdMina($ID_MINA);
            if (null === $MINA) {
                Utils::setError($doctrine, 1, 'No existe mina en MINA con id_mina = ' . $ID_MINA . ' (enviarCodigoAction)');
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Error inesperado')), 200);
            }
            $CODIGO_FORMATO = preg_replace('/\s+/', '', $CODIGO);
            if ($CODIGO_FORMATO === $MINA->getCodigo()) {
                $USUARIO_MINA = new \AppBundle\Entity\UsuarioMina();
                $USUARIO_MINA->setFecha(new \DateTime('now'));
                $USUARIO_MINA->setIdMina($MINA);
                $USUARIO_MINA->setIdUsuario($USUARIO);
                $em->persist($USUARIO_MINA);
                $em->flush();
                return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => 'Felicidades! Has desactivado la mina')), 200);
            }
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Código erroneo. Vuelva a intentarlo')), 200);
        }
    }

    /**
     * @Route("/ciudadano/ocio/altruismo/comprarPista", name="comprarPista")
     */
    public function comprarPistaAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $em = $doctrine->getManager();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/ocio/altruismo/comprarPista');
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Permiso denegado')), 200);
        }
        if ($request->getMethod() == 'POST') {
            $ID_MINA = strtolower($request->request->get('ID_MINA'));
            $BONO = $request->request->get('BONO');
            $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get("id_usuario"));
            $MINA = $doctrine->getRepository('AppBundle:Mina')->findOneByIdMina($ID_MINA);
            if (null === $MINA) {
                Utils::setError($doctrine, 1, 'No existe mina en MINA con id_mina = ' . $ID_MINA . ' (enviarCodigoAction)');
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Error inesperado')), 200);
            }
            $PISTAS = $doctrine->getRepository('AppBundle:MinaPista')->findByIdMina($MINA);
            if (!count($PISTAS)) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No hay pistas disponibles para comprar')), 200);
            }
            $query = $doctrine->getRepository('AppBundle:MinaPistaXUsuario')->createQueryBuilder('a');
            $query->select('a');
            $query->where('a.idUsuario = :USUARIO AND a.idMinaPista IN (:PISTAS)');
            $query->setParameters(['USUARIO' => $USUARIO, 'PISTAS' => array_values($PISTAS)]);
            $PISTAS_COMPRADAS = $query->getQuery()->getResult();

            if (count($PISTAS_COMPRADAS)) {
                if (count($PISTAS_COMPRADAS) < count($PISTAS)) {
                    $ids_pistas_compradas = [];
                    foreach ($PISTAS_COMPRADAS as $PISTA_COMPRADA) {
                        $ids_pistas_compradas[] = $PISTA_COMPRADA->getIdMinaPista();
                    }
                    // Condición de parada
                    $comprado = 0;
                    foreach ($PISTAS as $PISTA) {
                        if (!in_array($PISTA, $ids_pistas_compradas)) {
                            if (!$comprado) {
                                $comprado = 1;
                                $MI_PISTA = new \AppBundle\Entity\MinaPistaXUsuario();
                                $MI_PISTA->setIdMinaPista($PISTA);
                                $MI_PISTA->setIdUsuario($USUARIO);
                                $em->persist($MI_PISTA);
                                $em->flush();
                            }
                        }
                    }
                } else {
                    return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Ya habías comprado todas las pistas disponibles')), 200);
                }
            } else {
                $MI_PISTA = new \AppBundle\Entity\MinaPistaXUsuario();
                $MI_PISTA->setIdMinaPista($PISTAS[0]);
                $MI_PISTA->setIdUsuario($USUARIO);
                $em->persist($MI_PISTA);
                $em->flush();
            }

            // Fase de cobro
            if ($BONO) {
                $CARTA = $doctrine->getRepository('AppBundle:BonificacionExtra')->findOneByIdBonificacionExtra(8);
                $MI_CARTA = $doctrine->getRepository('AppBundle:BonificacionXUsuario')->findOneBy([
                    'idUsuario' => $USUARIO, 'idBonificacionExtra' => $CARTA, 'usado' => 0
                ]);
                if (null !== $MI_CARTA) {
                    $MI_CARTA->setUsado(1);
                    $em->persist($MI_CARTA);
                    $em->flush();
                } else {
                    $em->remove($MI_PISTA);
                    $em->flush();
                    Utils::setError($doctrine, 1, 'Ha intentado solicitar una pista de mina de bonificación sin bonificación', $USUARIO);
                    return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No tienes carta de bonificación')), 200);
                }
            } else {
                $COSTE = Utils::getConstante($doctrine, 'coste_pista');
                Usuario::operacionSobreTdV($doctrine, $USUARIO, (-1) * $COSTE, 'Cobro - Pista de mina');
            }
            return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => 'Compra realizada con éxito')), 200);
        }
        return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No se han enviado datos')), 200);
    }

    /**
     * @Route("/guardian/mina", name="guardianMina")
     */
    public function guardianMinaAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        // Comprobamos que el usuario es admin, si no, redireccionamos a /
        $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/mina', true);
        if (!$status) {
            return new RedirectResponse('/');
        }
        $DATOS['TITULO'] = 'Minas';
        return $this->render('guardian/mina.twig', $DATOS);
    }

    /**
     * @Route("/guardian/mina/publicar", name="publicarMina")
     */
    public function publicarMinaAction(Request $request) {
        if ($request->getMethod() == 'POST') {
            $doctrine = $this->getDoctrine();
            $em = $doctrine->getManager();
            $session = $request->getSession();
            $status = Usuario::compruebaUsuario($doctrine, $session, '/guardian/mina/publicar', true);
            if (!$status) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado')), 200);
            }
            if (Utils::minaActiva($doctrine)) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Actualmente hay una mina activa')), 200);
            }
            $CODIGO = strtolower($request->request->get('CODIGO'));
            $PISTA = $request->request->get('PISTA');
            $CODIGO_FORMATO = preg_replace('/\s+/', '', $CODIGO);
            $FECHA_TOPE = str_replace("T", " ", $request->request->get('FECHA')) . ":00";
            $FECHA_TOPE_formato = \DateTime::createFromFormat('Y-m-d H:i:s', $FECHA_TOPE);

            $ULTIMA_MINA = Utils::ultimaMinaDesactivada($doctrine);
            if ($ULTIMA_MINA) {
                if ($ULTIMA_MINA->getFechaFinal()->format('W') === $FECHA_TOPE_formato->format('W') && $ULTIMA_MINA->getFechaFinal()->format('d') === $FECHA_TOPE_formato->format('d')) {
                    return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No puedes poner más de una mina en el mismo día')), 200);
                }
            }

            $SECCION_MINA = $doctrine->getRepository('AppBundle:EjercicioSeccion')->findOneBySeccion('localizacion_minas');
            $TIPO_MINA = $doctrine->getRepository('AppBundle:EjercicioTipo')->findOneByTipo('mina');
            $EJERCICIO = new \AppBundle\Entity\Ejercicio();
            $EJERCICIO->setCoste(0);
            $EJERCICIO->setEnunciado('Mina');
            $EJERCICIO->setFecha(new \DateTime('now'));
            $EJERCICIO->setIcono(null);
            $EJERCICIO->setIdEjercicioSeccion($SECCION_MINA);
            $EJERCICIO->setIdTipoEjercicio($TIPO_MINA);
            $em->persist($EJERCICIO);

            $MINA = new \AppBundle\Entity\Mina();
            $MINA->setCodigo($CODIGO_FORMATO);
            $MINA->setFecha(new \DateTime('now'));
            $MINA->setFechaFinal($FECHA_TOPE_formato);
            $MINA->setIdEjercicio($EJERCICIO);
            $em->persist($MINA);

            $PISTA_MINA = new \AppBundle\Entity\MinaPista();
            $PISTA_MINA->setIdMina($MINA);
            $PISTA_MINA->setPista($PISTA);
            $em->persist($PISTA_MINA);

            $em->flush();
            return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => 'Mina registrada')), 200);
        }
        return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No se han enviado datos')), 200);
    }

}
