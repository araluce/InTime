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
use AppBundle\Utils\Distrito;

/**
 * Para hacer:
 *      1) Ciudadano del mes.
 */

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
            $FECHA_NACIMIENTO = str_replace("T", " ", $request->request->get('FECHA')) . "00:00:00";

            if ($ALIAS !== '') {
                $ALIAS = strtolower(preg_replace('/\s+/', '', $ALIAS));
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
                $usuario->setClave(Usuario::encriptar($usuario, $CLAVE));
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
            if ($FECHA_NACIMIENTO !== '') {
                $FECHA_FORMATO = \DateTime::createFromFormat('Y-m-d H:i:s', $FECHA_NACIMIENTO);
                $usuario->setFechaNacimiento($FECHA_FORMATO);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($usuario);
            $em->flush();

            $DATOS['info'] = [];
            $DATOS['info']['message'] = 'Gracias por actualizar tu información';
            $DATOS['info']['type'] = 'success';
        }
        $USUARIO_NIVEL = $doctrine->getRepository('AppBundle:UsuarioNivel')->findOneByIdUsuario($usuario);
        if (null !== $USUARIO_NIVEL) {
            $DATOS['NIVEL'] = $USUARIO_NIVEL->getNivel();
            $DATOS['PUNTOS'] = $USUARIO_NIVEL->getPuntos();
        } else {
            $DATOS['NIVEL'] = 0;
            $DATOS['PUNTOS'] = 0;
        }
        if (null !== $usuario->getIdDistrito()) {
            $DATOS['DISTRITO'] = $usuario->getIdDistrito()->getNombre();
        } else {
            $DATOS['DISTRITO'] = 'Aún no tienes un distrito asignado';
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
        if ($usuario->getFechaNacimiento()) {
            $fecha = $usuario->getFechaNacimiento();
            $DATOS['FECHA_NACIMIENTO'] = $fecha->format('Y-m-d');
        }
        $DATOS['TDV'] = $usuario->getIdCuenta()->getTdv();
        return $this->render('ciudadano/extensiones/informacion.html.twig', $DATOS);
    }

    /**
     * 
     * @Route("/ciudadano/info/actualizarMovimientos", name="actualizarMovimiento")
     */
    public function actualizarMovimientoAction(Request $request) {
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
        if (!count($MOVIMIENTOS)) {
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Aún no se han producido movimientos'));
        }
        $DATOS['MOVIMIENTOS'] = [];
        foreach ($MOVIMIENTOS as $MOVIMIENTO) {
            $aux = [];
            $aux['ID'] = $MOVIMIENTO->getIdUsuarioMovimiento();
            $aux['CANTIDAD'] = $MOVIMIENTO->getCantidad();
            if ($aux['CANTIDAD'] < 0) {
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

    /**
     * 
     * @Route("/ciudadano/info/getClasificacionGlobal", name="getClasificacionGlobal")
     */
    public function getClasificacionGlobal(Request $request) {
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $qb = $em->createQueryBuilder();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/info/getClasificacionGlobal');
        if (!$status) {
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado'));
        }
        $id_usuario = $session->get('id_usuario');
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($id_usuario);
        $ROL = $doctrine->getRepository('AppBundle:Rol')->findOneByNombre('Jugador');
        $query = $qb->select('u')
                ->from('\AppBundle\Entity\Usuario', 'u')
                ->where('u.idUsuario != :ID_USUARIO AND u.idRol = :ROL')
                ->setParameters(['ID_USUARIO' => $USUARIO->getIdUsuario(), 'ROL' => $ROL]);
        $USUARIOS = $query->getQuery()->getResult();

        return Usuario::getClasificacionJsonResponse($doctrine, $USUARIO, $USUARIOS);
    }

    /**
     * 
     * @Route("/ciudadano/info/getClasificacionGlobalMes", name="getClasificacionGlobalMes")
     */
    public function getClasificacionGlobalMesAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $qb = $em->createQueryBuilder();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/info/getClasificacionGlobalMes');
        if (!$status) {
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado'));
        }
        $id_usuario = $session->get('id_usuario');
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($id_usuario);
        $ROL = $doctrine->getRepository('AppBundle:Rol')->findOneByNombre('Jugador');
        $query = $qb->select('u')
                ->from('\AppBundle\Entity\Usuario', 'u')
                ->where('u.idUsuario != :ID_USUARIO AND u.idRol = :ROL')
                ->setParameters(['ID_USUARIO' => $USUARIO->getIdUsuario(), 'ROL' => $ROL]);
        $USUARIOS = $query->getQuery()->getResult();

        return Usuario::getClasificacionMesJsonResponse($doctrine, $USUARIO, $USUARIOS);
    }

    /**
     * 
     * @Route("/ciudadano/info/getClasificacionDistrito", name="getClasificacionDistrito")
     */
    public function getClasificacionDistrito(Request $request) {
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $qb = $em->createQueryBuilder();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/info/getClasificacionDistrito');
        if (!$status) {
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado'));
        }
        $id_usuario = $session->get('id_usuario');
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($id_usuario);
        $DISTRITO = $USUARIO->getIdDistrito();
        if ($DISTRITO === null) {
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'No perteneces a ningún distrito'));
        }
        $ROL = $doctrine->getRepository('AppBundle:Rol')->findOneByNombre('Jugador');
        $query = $qb->select('u')
                ->from('\AppBundle\Entity\Usuario', 'u')
                ->where('u.idUsuario != :ID_USUARIO AND u.idRol = :ROL AND u.idDistrito = :DISTRITO')
                ->setParameters([
            'ID_USUARIO' => $USUARIO->getIdUsuario(),
            'ROL' => $ROL,
            'DISTRITO' => $DISTRITO
        ]);
        $USUARIOS = $query->getQuery()->getResult();

        return Usuario::getClasificacionJsonResponse($doctrine, $USUARIO, $USUARIOS);
    }

    /**
     * 
     * @Route("/ciudadano/info/getClasificacionDistritoMes", name="getClasificacionDistritoMes")
     */
    public function getClasificacionDistritoMesAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $qb = $em->createQueryBuilder();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/info/getClasificacionDistritoMes');
        if (!$status) {
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado'));
        }
        $id_usuario = $session->get('id_usuario');
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($id_usuario);
        $DISTRITO = $USUARIO->getIdDistrito();
        if ($DISTRITO === null) {
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'No perteneces a ningún distrito'));
        }
        $ROL = $doctrine->getRepository('AppBundle:Rol')->findOneByNombre('Jugador');
        $query = $qb->select('u')
                ->from('\AppBundle\Entity\Usuario', 'u')
                ->where('u.idUsuario != :ID_USUARIO AND u.idRol = :ROL AND u.idDistrito = :DISTRITO')
                ->setParameters([
            'ID_USUARIO' => $USUARIO->getIdUsuario(),
            'ROL' => $ROL,
            'DISTRITO' => $DISTRITO
        ]);
        $USUARIOS = $query->getQuery()->getResult();

        return Usuario::getClasificacionMesJsonResponse($doctrine, $USUARIO, $USUARIOS);
    }

    /**
     * 
     * @Route("/ciudadano/info/getClasificacionDistritos", name="getClasificacionDistritos")
     */
    public function getClasificacionDistritos(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/info/getClasificacionDistritos');
        if (!$status) {
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado'));
        }
        $RESPUESTA = Usuario::getClasificacionDistritos($doctrine);

        return new JsonResponse(array('estado' => 'OK', 'message' => $RESPUESTA));
    }

    /**
     * 
     * @Route("/ciudadano/info/getClasificacionDistritosMes", name="getClasificacionDistritosMes")
     */
    public function getClasificacionDistritosMesAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/info/getClasificacionDistritos');
        if (!$status) {
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado'));
        }
        $RESPUESTA = Usuario::getClasificacionDistritosMes($doctrine);

        return new JsonResponse(array('estado' => 'OK', 'message' => $RESPUESTA));
    }

    /**
     * 
     * @Route("/ciudadano/info/getNivelMC", name="getNivelMC")
     */
    public function getNivelMCAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/info/getNivelMC');
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado')));
        }
        $id_usuario = $session->get('id_usuario');
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($id_usuario);
        $USUARIO_NIVEL = $doctrine->getRepository('AppBundle:UsuarioNivel')->findOneByIdUsuario($USUARIO);
        $DATOS = [];
        $DATOS['CIUDADANOS_VIVOS'] = [];
        $CIUDADANOS_VIVOS = Usuario::getCiudadanosVivos($doctrine);
        if (count($CIUDADANOS_VIVOS)) {
            foreach ($CIUDADANOS_VIVOS as $CIUDADANO_VIVO) {
                $aux = [];
                $aux['ALIAS'] = $CIUDADANO_VIVO->getSeudonimo();
                $aux['ID'] = $CIUDADANO_VIVO->getIdUsuario();
                $DATOS['CIUDADANOS_VIVOS'][] = $aux;
            }
        }
        $DATOS['CIUDADANOS_FALLECIDOS'] = [];
        $CIUDADANOS_FALLECIDOS = Usuario::getCiudadanosFallecidos($doctrine);
        if (count($CIUDADANOS_FALLECIDOS)) {
            foreach ($CIUDADANOS_FALLECIDOS as $CIUDADANO_FALLECIDO) {
                $aux = [];
                $aux['ALIAS'] = $CIUDADANO_FALLECIDO->getSeudonimo();
                $aux['ID'] = $CIUDADANO_FALLECIDO->getIdUsuario();
                $DATOS['CIUDADANOS_FALLECIDOS'][] = $aux;
            }
        }
        $DATOS['NIVEL'] = 0;
        $DATOS['XP'] = 0;
        if (null !== $USUARIO_NIVEL) {
            $DATOS['NIVEL'] = $USUARIO_NIVEL->getNivel();
            $DATOS['XP'] = $USUARIO_NIVEL->getPuntos();
        }
        $DATOS['MC'] = [];
        $MC_REANIMACION = $doctrine->getRepository('AppBundle:BonificacionExtra')->findOneByIdBonificacionExtra(1);
        $MC_BONO_REGALO = $doctrine->getRepository('AppBundle:BonificacionExtra')->findOneByIdBonificacionExtra(6);
        $MINICARTAS = $doctrine->getRepository('AppBundle:BonificacionExtra')->findAll();
        $MIS_MC = $doctrine->getRepository('AppBundle:BonificacionXUsuario')->findBy([
            'idUsuario' => $USUARIO, 'usado' => 0
        ]);
        $array_mc = [];
        if (count($MIS_MC)) {
            foreach ($MIS_MC as $MCU) {
                $array_mc[] = $MCU->getIdBonificacionExtra();
            }
        }
        $hoy = new \DateTime('now');
        if (count($MINICARTAS)) {
            foreach ($MINICARTAS as $MC) {
                $aux = [];
                $aux['LISTA_CIUDADANOS'] = 0;
                $aux['ID'] = $MC->getIdBonificacionExtra();
                $aux['TITULO'] = $MC->getBonificacion();
                $aux['DESCRIPCION'] = $MC->getDescripcion();
                $aux['IMAGEN'] = $MC->getImagen();
                $aux['COSTE'] = $MC->getCosteXp();
                $aux['COMPRADA'] = 0;
                $compras = $doctrine->getRepository('AppBundle:BonificacionXUsuario')->findByIdBonificacionExtra($MC);
                $aux['VECES_COMPRADA'] = count($compras);
                if (in_array($MC, $array_mc) && $MC->getDisponible()) {
                    $MI_MC = $doctrine->getRepository('AppBundle:BonificacionXUsuario')->findOneBy([
                        'idBonificacionExtra' => $MC, 'idUsuario' => $USUARIO
                    ]);
                    if (null !== $MI_MC) {
                        if (!$MI_MC->getUsado()) {
                            $aux['COMPRADA'] = 1;
                        }
                    }
                }
                if ($MC === $MC_BONO_REGALO) {
                    $aux['LISTA_CIUDADANOS'] = 1;
                }
                if ($MC === $MC_REANIMACION) {
                    $aux['LISTA_CIUDADANOS'] = 2;
                }
                if ($MC->getDisponible()) {
                    $DATOS['MC'][] = $aux;
                }
            }
        }
        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $DATOS)));
    }

    /**
     * 
     * @Route("/ciudadano/info/comprarMC/{idMC}/{idCiudadano}", name="comprarMC")
     */
    public function comprarMCAction(Request $request, $idMC, $idCiudadano) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/info/comprarMC/' . $idMC);
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado')));
        }
        $id_usuario = $session->get('id_usuario');
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($id_usuario);
        $USUARIO_NIVEL = $doctrine->getRepository('AppBundle:UsuarioNivel')->findOneByIdUsuario($USUARIO);
        if (null === $USUARIO_NIVEL) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No tienes suficientes puntos de experiencia')));
        }
        $MC = $doctrine->getRepository('AppBundle:BonificacionExtra')->findOneByIdBonificacionExtra($idMC);
        $MI_MC = $doctrine->getRepository('AppBundle:BonificacionXUsuario')->findOneBy([
            'idUsuario' => $USUARIO, 'idBonificacionExtra' => $MC
        ]);
        if (null !== $MI_MC) {
            if (!$MI_MC->getUsado()) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Ya tenías comprada esta carta')));
            }
        }
        if ($USUARIO_NIVEL->getPuntos() < $MC->getCosteXp()) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No tienes suficientes puntos de experiencia')));
        }
        $em = $doctrine->getManager();
        $USUARIO_NIVEL->setPuntos($USUARIO_NIVEL->getPuntos() - $MC->getCosteXp());
        $em->persist($USUARIO_NIVEL);
        if (!$idCiudadano) {
            if ($MI_MC === null) {
                $MI_MC = new \AppBundle\Entity\BonificacionXUsuario();
                $MI_MC->setContador(0);
            }

            $MI_MC->setFecha(new \DateTime('now'));
            $MI_MC->setContador($MI_MC->getContador() + 1);
            $MI_MC->setIdBonificacionExtra($MC);
            $MI_MC->setIdUsuario($USUARIO);
            $MI_MC->setUsado(0);
            $em->persist($MI_MC);
            $em->flush();

            if ($MC->getIdBonificacionExtra() === 11) {
                $USUARIO->setTiempoSinComer(new \DateTime('now'));
                $USUARIO->setTiempoSinBeber(new \DateTime('now'));
                $MI_MC->setUsado(1);
                $em->persist($USUARIO);
                $em->persist($MI_MC);
                $em->flush();
                return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => 'Tus barras ahora están al 100%')));
            }
        } else {
            $CIUDADANO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($idCiudadano);
            if (null === $CIUDADANO) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'El ciudadano seleccionado no existe')));
            }
            $MC_REANIMACION = $doctrine->getRepository('AppBundle:BonificacionExtra')->findOneByIdBonificacionExtra(1);
            $MC_BONO_REGALO = $doctrine->getRepository('AppBundle:BonificacionExtra')->findOneByIdBonificacionExtra(6);
            if($MC === $MC_BONO_REGALO){
                $USUARIO_NIVEL = $doctrine->getRepository('AppBundle:UsuarioNivel')->findOneByIdUsuario($CIUDADANO);
                if(null === $USUARIO_NIVEL){
                    $USUARIO_NIVEL = new \AppBundle\Entity\UsuarioNivel();
                    $USUARIO_NIVEL->setNivel(0);
                    $USUARIO_NIVEL->setIdUsuario($CIUDADANO);
                    $USUARIO_NIVEL->setPuntos(2);
                } else {
                    $USUARIO_NIVEL->setPuntos($USUARIO_NIVEL->getPuntos() + 2);
                }
                $em->persist($USUARIO_NIVEL);
                $em->flush();
            }
            if($MC === $MC_REANIMACION){
                // Se le da una semana de vida
                $CUENTA = $CIUDADANO->getIdCuenta();
                $semana = new \DateTime('now');
                $semana->add(new \DateInterval('P7D'));
                $CUENTA->setTdv($semana);
                $em->persist($CUENTA);
                
                // Se le da de comer y beber
                $CIUDADANO->setTiempoSinComer(new \DateTime('now'));
                $CIUDADANO->setTiempoSinBeber(new \DateTime('now'));
                
                // Se le cambia el estado a vivo
                $VIVO = $doctrine->getRepository('AppBundle:UsuarioEstado')->findOneByNombre('Activo');
                $CIUDADANO->setIdEstado($VIVO);
                $em->persist($CIUDADANO);
                $em->flush();
            }
        }
        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => 'Su compra ha sido realizada correctamente')));
    }

}
