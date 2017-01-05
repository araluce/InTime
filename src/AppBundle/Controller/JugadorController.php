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
 * Description of JugadorController
 *
 * @author araluce
 */
class JugadorController extends Controller {
    /**
     * @Route("/ciudadano/info", name="informacion")
     */
    public function infoAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/info');
        if (!$status) {
            return new RedirectResponse('/');
        }
        $id_usuario = $session->get('id_usuario');
        $usuario = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($id_usuario);

        $DATOS = [];
        $DATOS['TITULO'] = 'Informacion';

        if ($request->getMethod() == 'POST') {
            $ALIAS = trim($request->request->get('ALIAS'));
            $NOMBRE = trim($request->request->get('NOMBRE'));
            $APELLIDOS = trim($request->request->get('APELLIDOS'));
            $EMAIL = trim($request->request->get('EMAIL'));
            $CLAVE = trim($request->request->get('CLAVE'));
            $IMAGEN = $request->files->get('IMAGEN');

            if ($ALIAS !== '') {
                if (Usuario::aliasDisponible($doctrine, $session, $ALIAS)) {
                    $usuario->setSeudonimo($ALIAS);
                }
            }
            if ($NOMBRE !== '') {
                $usuario->setNombre($NOMBRE);
            }
            if ($APELLIDOS !== '') {
                $usuario->setApellidos($APELLIDOS);
            }
            if ($EMAIL !== '') {
                $usuario->setEmail($EMAIL);
            }
            if ($CLAVE !== '') {
                $usuario->setClave($this->encriptar($usuario, $CLAVE));
            }
            if (!is_null($IMAGEN)) {
                $ruta = 'images/users/' . $usuario->getDni();
                if (!file_exists($ruta)) {
                    mkdir($ruta, 0777, true);
                }
                // generate a random name for the file but keep the extension
                //                $nombre_foto = uniqid() . "." . $file->getClientOriginalExtension();
                $nombre_foto = "profile." . $IMAGEN->getClientOriginalExtension();
                $usuario->setImagen($nombre_foto);
                $IMAGEN->move($ruta, $nombre_foto);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($usuario);
            $em->flush();

            $DATOS['info'] = [];
            $DATOS['info']['message'] = 'Gracias por actualizar tu información';
            $DATOS['info']['type'] = 'success';
        }
        if ($usuario->getSeudonimo()) {
            $DATOS['ALIAS'] = $usuario->getSeudonimo();
        }
        if ($usuario->getNombre()) {
            $DATOS['NOMBRE'] = $usuario->getNombre();
        }
        if ($usuario->getApellidos()) {
            $DATOS['APELLIDOS'] = $usuario->getApellidos();
        }
        if ($usuario->getEmail()) {
            $DATOS['EMAIL'] = $usuario->getEmail();
        }
        if ($usuario->getImagen()) {
            $DATOS['IMAGEN'] = $usuario->getDni() . '/' . $usuario->getImagen();
        }
        $DATOS['TDV'] = $usuario->getIdCuenta()->getTdv();
        return $this->render('ciudadano/extensiones/informacion.html.twig', $DATOS);
    }
    
    /**
     * 
     * @Route("/ciudadano/info/actualizarMovimientos", name="actualizarMovimiento")
     */
    public function actualizarMovimientoAction(Request $request){
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $qb = $em->createQueryBuilder();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/info/actualizarMovimientos');
        if (!$status) {
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado'));
        }
        $id_usuario = $session->get('id_usuario');
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($id_usuario);
        $query = $qb->select('f')
                ->from('\AppBundle\Entity\UsuarioMovimiento', 'f')
                ->where('f.idUsuario = :ID_USUARIO')
                ->orderBy('f.fecha', 'DESC')
                ->setParameters(['ID_USUARIO' => $USUARIO->getIdUsuario()]);
        $MOVIMIENTOS = $query->getQuery()->getResult();
        if(!count($MOVIMIENTOS)){
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Aún no se han producido movimientos'));
        }
        $DATOS['MOVIMIENTOS'] = [];
        foreach($MOVIMIENTOS as $MOVIMIENTO){
            $aux = [];
            $aux['ID'] = $MOVIMIENTO->getIdUsuarioMovimiento();
            $aux['CANTIDAD'] = $MOVIMIENTO->getCantidad();
            if($aux['CANTIDAD'] < 0){
                $aux['VALANCE'] = 'NEG';
            } else {
                $aux['VALANCE'] = 'POS';
            }
            $aux['CANTIDAD'] = Utils::segundosToDias($aux['CANTIDAD']);
            $aux['CONCEPTO'] = $MOVIMIENTO->getCausa();
            $aux['FECHA'] = $MOVIMIENTO->getFecha();
            $DATOS['MOVIMIENTOS'][] = $aux;
        }
        return new JsonResponse(array('estado' => 'OK', 'message' => $DATOS['MOVIMIENTOS']));
    }
}
