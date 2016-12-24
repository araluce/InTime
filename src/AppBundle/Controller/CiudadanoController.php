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

/**
 * Description of CiudadanoController
 *
 * @author araluce
 */
class CiudadanoController extends Controller {

    /**
     * @Route("/ciudadano/ocio/amigos/chat", name="chat")
     */
    public function chatAction(Request $request) {
        $DataManager = new \AppBundle\Utils\DataManager();
        $UsuarioClass = new \AppBundle\Utils\Usuario();
        $session = $request->getSession();
        $doctrine = $this->getDoctrine();
        $status = $UsuarioClass->compruebaUsuario($doctrine, $session, '/ciudadano/ocio/amigos/chat');
        if (!$status) {
            return new RedirectResponse('/');
        }
        $DATOS = $DataManager->setDefaultData($doctrine, 'Chat', $session);
        unset($DATOS['TDV']);
        $DATOS['DISTRITOS'] = $doctrine->getRepository('AppBundle:UsuarioDistrito')->findAll();
        $ROL = $doctrine->getRepository('AppBundle:Rol')->findOneByNombre('Jugador');
        $CIUDADANOS = $doctrine->getRepository('AppBundle:Usuario')->findByIdRol($ROL);
        $DATOS['CIUDADANOS'] = [];
        foreach ($CIUDADANOS as $CIUDADANO) {
            if ($CIUDADANO->getSeudonimo() !== null) {
                $DATOS['CIUDADANOS'][] = $CIUDADANO;
            }
        }
        return $this->render('ciudadano/ocio/chat.twig', $DATOS);
    }

    /**
     * @Route("/ciudadano/getDistrito", name="getDistrito")
     */
    public function getDistrito(Request $request) {
        $UsuarioClass = new \AppBundle\Utils\Usuario();
        $session = $request->getSession();
        $doctrine = $this->getDoctrine();
        $status = $UsuarioClass->compruebaUsuario($doctrine, $session, '/ciudadano/getDistrito');
        if (!$status) {
            $JsonResponse_data["estado"] = "ERROR - No autorizado";
        }
        $Usuario = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get("id_usuario"));
        $DISTRITO = $Usuario->getIdDistrito();
        $JsonResponse_data["estado"] = "OK";
        if ($DISTRITO !== null) {
            $JsonResponse_data["distrito"] = $Usuario->getIdDistrito()->getNombre();
        } else {
            return new JsonResponse(array('estado' => 'ERROR - Este usuario no está asociado a ningún distrito'), 200);
        }
        return new JsonResponse($JsonResponse_data, 200);
    }

    /**
     * @Route("/ciudadano/getUltimoChat", name="getUltimoChat")
     */
    public function getUltimoChat(Request $request) {
        $UsuarioClass = new \AppBundle\Utils\Usuario();
        $session = $request->getSession();
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $qb = $em->createQueryBuilder();
        $status = $UsuarioClass->compruebaUsuario($doctrine, $session, '/ciudadano/getUltimoChat');
        if (!$status) {
            $JsonResponse_data["estado"] = "ERROR - No autorizado";
        }
        $Usuario = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get("id_usuario"));
        $query = $qb->select('c')
                ->from('\AppBundle\Entity\Chat', 'c')
                ->where('c.idUsuario1 = :usuario OR c.idUsuario2 = :usuario')
                ->orderBy('c.fechaUltimoMensaje', 'DESC')
                ->setParameter('usuario', $Usuario)
//                      ->setFirstResult( $offset )
                ->setMaxResults(1);
        $CHAT = $query->getQuery()->getResult();

        $JsonResponse_data["estado"] = "OK";
        if (!count($CHAT)) {
            return new JsonResponse(array('estado' => 'ERROR - Este usuario no tiene chats abiertos'), 200);
        } else {
            if ($CHAT[0]->getIdUsuario1() === $Usuario && $CHAT[0]->getIdUsuario2() !== null) {
                $JsonResponse_data['id_chat'] = $CHAT[0]->getIdUsuario2()->getIdUsuario();
                $JsonResponse_data['id_grupo'] = null;
            } else if ($CHAT[0]->getIdUsuario2() === $Usuario && $CHAT[0]->getIdUsuario1() !== null) {
                $JsonResponse_data['id_chat'] = $CHAT[0]->getIdUsuario1()->getIdUsuario();
                $JsonResponse_data['id_grupo'] = null;
            } else if ($CHAT[0]->getIdUsuario1() === $Usuario && $CHAT[0]->getIdDistrito() !== null) {
                $JsonResponse_data['id_chat'] = null;
                $JsonResponse_data['id_grupo'] = $CHAT[0]->getIdDistrito()->getIdUsuarioDistrito();
            } else if ($CHAT[0]->getIdUsuario2() === $Usuario && $CHAT[0]->getIdDistrito() !== null) {
                $JsonResponse_data['id_chat'] = null;
                $JsonResponse_data['id_grupo'] = $CHAT[0]->getIdDistrito()->getIdUsuarioDistrito();
            }
        }
        return new JsonResponse($JsonResponse_data, 200);
    }

    /**
     * @Route("/ciudadano/getChat/{id_usuario_destino}/{id_grupo_destino}/{offset}", name="getChat")
     */
    public function getChat(Request $request, $id_usuario_destino, $id_grupo_destino, $offset) {
        $UsuarioClass = new \AppBundle\Utils\Usuario();
        $session = $request->getSession();
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $qb = $em->createQueryBuilder();
        $status = $UsuarioClass->compruebaUsuario($doctrine, $session, '/ciudadano/getChat/' . $id_usuario_destino . '/' . $id_grupo_destino . '/' . $offset);
        if (!$status) {
            $JsonResponse_data["estado"] = "ERROR - No autorizado";
        }
        $Usuario = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get("id_usuario"));

        if ($id_usuario_destino !== 'null') {
            $Usuario_destino = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($id_usuario_destino);
            $JsonResponse_data['usuario']['alias'] = $Usuario_destino->getSeudonimo();
            $JsonResponse_data['usuario']['imagen'] = $Usuario_destino->getImagen();
            $JsonResponse_data['usuario']['dni'] = $Usuario_destino->getDni();
            $JsonResponse_data['usuario']['id'] = $Usuario_destino->getIdUsuario();
            $query = $qb->select('c1')
                    ->from('\AppBundle\Entity\Chat', 'c1')
                    ->where('c1.idUsuario1 = :emisor AND c1.idUsuario2 = :receptor_usuario')
                    ->orWhere('c1.idUsuario1 = :receptor_usuario AND c1.idUsuario2 = :emisor')
                    ->orderBy('c1.fecha', 'ASC')
                    ->setParameters(array('emisor' => $Usuario, 'receptor_usuario' => $Usuario_destino));
        } elseif ($id_grupo_destino !== 'null') {
            $Distrito = $doctrine->getRepository('AppBundle:UsuarioDistrito')->findOneByIdUsuarioDistrito($id_grupo_destino);
            $query = $qb->select('ch')
                    ->from('\AppBundle\Entity\Chat', 'ch')
                    ->where('ch.idUsuario1 = :emisor AND ch.idDistrito = :receptor_distrito')
                    ->orderBy('ch.fecha', 'ASC')
                    ->setParameters(array('emisor' => $Usuario, 'receptor_distrito' => $Distrito));
        }
        $CHAT = $query->getQuery()->getResult();
        if (!count($CHAT)) {
            $JsonResponse_data["estado"] = "ERROR - No hay conversación";
            return new JsonResponse($JsonResponse_data, 200);
        }
        $CHAT[0]->setFechaUltimoMensaje(new \DateTime('now'));
        $em->persist($CHAT[0]);
        $em->flush();

        $query = $qb->select('cm')
                ->from('\AppBundle\Entity\ChatMensajes', 'cm')
                ->where('cm.idChat = :idChat')
                ->orderBy('cm.fecha', 'ASC')
                ->setParameters(array('idChat' => $CHAT[0]))
                ->setFirstResult(0)
                ->setMaxResults($offset + 100);
        $CHATS = $query->getQuery()->getResult();

        if (!count($CHATS)) {
            $JsonResponse_data["estado"] = "ERROR - No hay intercambio de mensajes";
            return new JsonResponse($JsonResponse_data, 200);
        } else {
            $JsonResponse_data["estado"] = "OK";
            $JsonResponse_data['mensajes'] = [];
            foreach ($CHATS as $CHAT) {
                $aux['usuario'] = [];
                $aux['usuario']['mi_alias'] = $Usuario->getSeudonimo();
                $aux['usuario']['alias'] = $CHAT->getIdUsuario()->getSeudonimo();
                $aux['usuario']['imagen'] = $CHAT->getIdUsuario()->getImagen();
                $aux['usuario']['dni'] = $CHAT->getIdUsuario()->getDni();
                $aux['mensaje'] = $CHAT->getMensaje();
                $aux['visto'] = $CHAT->getVisto();
                $aux['fecha'] = $CHAT->getFecha();
                $JsonResponse_data['mensajes'][] = $aux;

                $CHAT->setVisto(1);
                $em->persist($CHAT);
            }
            $em->flush();
        }
        return new JsonResponse($JsonResponse_data, 200);
    }

    /**
     * @Route("/ciudadano/enviarMensajeChat", name="enviarMensajeChat")
     */
    public function enviarMensajeChat(Request $request) {
        $UsuarioClass = new \AppBundle\Utils\Usuario();
        $session = $request->getSession();
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $qb = $em->createQueryBuilder();
        $status = $UsuarioClass->compruebaUsuario($doctrine, $session, '/ciudadano/enviarMensajeChat');
        if (!$status) {
            return new RedirectResponse('/');
        }

        if ($request->getMethod() == 'POST') {
            $Usuario = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get("id_usuario"));
            $Usuario2 = $request->request->get('id_usuario');
            $Distrito = $request->request->get('id_distrito');
            $mensaje = $request->request->get('mensaje');

            if ($Usuario2 !== null) {
                $receptor_usuario = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($Usuario2);
                $query = $qb->select('ch')
                        ->from('\AppBundle\Entity\Chat', 'ch')
                        ->where('ch.idUsuario1 = :emisor AND ch.idUsuario2 = :receptor_usuario')
                        ->orWhere('ch.idUsuario1 = :receptor_usuario AND ch.idUsuario2 = :emisor')
                        ->orderBy('ch.fecha', 'ASC')
                        ->setParameters(array('emisor' => $Usuario, 'receptor_usuario' => $receptor_usuario));
            } else if ($Distrito !== null) {
                $Distrito = $doctrine->getRepository('AppBundle:UsuarioDistrito')->findOneByIdUsuarioDistrito($Distrito);
                $query = $qb->select('ch2')
                        ->from('\AppBundle\Entity\Chat', 'ch2')
                        ->where('ch2.idUsuario1 = :emisor AND ch2.idDistrito = :receptor_distrito')
                        ->orderBy('ch2.fecha', 'ASC')
                        ->setParameters(array('emisor' => $Usuario, 'receptor_distrito' => $Distrito));
            }
            $CHAT = $query->getQuery()->getResult();
            $fecha = new \DateTime('now');

            if (!count($CHAT)) {
                $CHAT = new \AppBundle\Entity\Chat();
                $CHAT->setIdUsuario1($Usuario);
                if ($receptor_usuario !== null) {
                    $CHAT->setIdUsuario2($receptor_usuario);
                } else if ($Distrito !== null) {
                    $CHAT->setIdDistrito($Distrito);
                }
                $CHAT->setFecha($fecha);
                $CHAT->setFechaUltimoMensaje($fecha);
            } else {
                $CHAT = $CHAT[0];
                $CHAT->setFechaUltimoMensaje($fecha);
            }
            $em->persist($CHAT);
            $em->flush();

            $MENSAJE = new \AppBundle\Entity\ChatMensajes();
            $MENSAJE->setIdChat($CHAT);
            $MENSAJE->setIdUsuario($Usuario);
            $MENSAJE->setMensaje($mensaje);
            $MENSAJE->setFecha($fecha);
            $MENSAJE->setVisto(0);

            $em->persist($MENSAJE);
            $em->flush();

            return new JsonResponse(array('estado' => 'OK'), 200);
        }
        return new JsonResponse(array('estado' => 'ERROR'), 200);
    }

    /**
     * @Route("/ciudadano/ocio/amigos/fotos", name="fotos")
     */
    public function fotosAction(Request $request) {
        $DataManager = new \AppBundle\Utils\DataManager();
        $UsuarioClass = new \AppBundle\Utils\Usuario();
        $session = $request->getSession();
        $doctrine = $this->getDoctrine();
        $status = $UsuarioClass->compruebaUsuario($doctrine, $session, '/ciudadano/ocio/amigos/fotos');
        if (!$status) {
            return new RedirectResponse('/');
        }
        $DATOS = $DataManager->setDefaultData($doctrine, 'Fotos', $session);

        $FOTOS = $doctrine->getRepository('AppBundle:AlbumFoto')->findAll();
        if (count($FOTOS)) {
            $DATOS['FOTOS'] = [];
            foreach ($FOTOS AS $FOTO) {
                $aux['usuario'] = $FOTO->getIdUsuario();
                $aux['fecha'] = $FOTO->getFecha();
                $aux['imagen'] = $FOTO->getImagen();
                $aux['id'] = $FOTO->getIdAlbumFoto();
                $REACCIONES = $doctrine->getRepository('AppBundle:FotoReaccion')->findByIdAlbumFoto($FOTO);
                $aux['likes'] = 0;
                $aux['dislikes'] = 0;
                if (count($REACCIONES)) {
                    foreach ($REACCIONES AS $REACCION) {
                        if ($REACCION->getLikeSocial()) {
                            $aux['likes'] ++;
                        } else {
                            $aux['dislikes'] ++;
                        }
                    }
                }

                $DATOS['FOTOS'][] = $aux;
            }
        }

        return $this->render('ciudadano/ocio/fotos.twig', $DATOS);
    }

    /**
     * @Route("/ciudadano/subirImagenAlbum", name="subirImagenAlbum")
     */
    public function subirImagenAlbumAction(Request $request) {
        $UsuarioClass = new \AppBundle\Utils\Usuario();
        $session = $request->getSession();
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $status = $UsuarioClass->compruebaUsuario($doctrine, $session, '/ciudadano/subirImagenAlbum');
        if (!$status) {
            return new JsonResponse(array('estado' => 'ERROR - Permiso denegado'), 200);
        }
        if ($request->getMethod() == 'POST') {
            $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get("id_usuario"));
            $IMAGENES = $request->files->get('imagenes');

            if (count($IMAGENES)) {
                foreach ($IMAGENES as $IMAGEN) {
                    $IMG = new \AppBundle\Entity\AlbumFoto();
                    $IMG->setFecha(new \DateTime('now'));
                    $IMG->setIdUsuario($USUARIO);
                    $em->persist($IMG);
                    $em->flush();

                    $ruta = 'images/users/' . $USUARIO->getDni() . '/galeria';
                    if (!file_exists($ruta)) {
                        mkdir($ruta, 0777, true);
                    }
                    $nombre_foto = $IMG->getIdAlbumFoto() . '.' . $IMAGEN->getClientOriginalExtension();
                    $IMG->setImagen($nombre_foto);
                    $em->persist($IMG);
                    $em->flush();
                    $IMAGEN->move($ruta, $nombre_foto);
                }
            }
            return new JsonResponse(array('estado' => 'OK'), 200);
        }
    }

    /**
     * @Route("/ciudadano/ocio/amigos/fotos/like/{id_album_foto}/{like}", name="likeFoto")
     */
    public function likeFotoAction(Request $request, $id_album_foto, $like) {
        $UsuarioClass = new \AppBundle\Utils\Usuario();
        $session = $request->getSession();
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $status = $UsuarioClass->compruebaUsuario($doctrine, $session, '/ciudadano/ocio/amigos/fotos/like/' . $id_album_foto . '/' . $like);
        if (!$status) {
            return new JsonResponse(array('estado' => 'ERROR - Permiso denegado'), 200);
        }
        $FOTO = $doctrine->getRepository('AppBundle:AlbumFoto')->findOneByIdAlbumFoto($id_album_foto);
        if ($FOTO) {
            $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get("id_usuario"));
            $REACCION = $doctrine->getRepository('AppBundle:FotoReaccion')->findOneBy([
                'idUsuario' => $USUARIO, 'idAlbumFoto' => $FOTO]);
            if ($REACCION === null) {
                $REACCION = new \AppBundle\Entity\FotoReaccion();
                $REACCION->setIdAlbumFoto($FOTO);
                $REACCION->setIdUsuario($USUARIO);
                $REACCION->setLikeSocial(intval($like));
                $em->persist($REACCION);
            } else {
                if(intval($like) !== intval($REACCION->getLikeSocial())){
                    $REACCION->setLikeSocial($like);
                }
                else{
                    $em->remove($REACCION);
                }
            }
            $em->flush();            
        } else {
            return new JsonResponse(array('estado' => 'ERROR - No había ninguna foto con el id: ' . $id_album_foto), 200);
        }
        return new JsonResponse(array('estado' => 'OK'), 200);
    }

}
