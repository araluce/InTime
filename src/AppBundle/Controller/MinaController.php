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
use AppBundle\Utils\DataManager;
use AppBundle\Utils\Pago;
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
        $em = $doctrine->getManager();
        $qb = $em->createQueryBuilder();
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
        $DATOS = [];
        $DATOS['ID_MINA'] = $MINA->getIdMina();
        $DATOS['ID_EJERCICIO'] = $MINA->getIdEjercicio()->getIdEjercicio();
        $DATOS['FECHA_FINAL'] = $MINA->getFechaFinal();
        $query = $qb->select('um')
                ->from('\AppBundle\Entity\UsuarioMina', 'um')
                ->where('um.idMina = :IdMina')
                ->orderBy('um.fecha', 'DESC')
                ->setParameters(['IdMina' => $MINA]);
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
            $CODIGO_FORMATO = preg_replace('/\s+/', '', $CODIGO);
//            $CODIGO_F = strtr($CODIGO, "aáäàeéëèiíïìoóòöuúùü", "aaaaeeeeiiiioooouuuu");
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
            $em->flush();
            return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => 'Mina registrada')), 200);
        }
        return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No se han enviado datos')), 200);
    }

}
