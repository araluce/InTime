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
use AppBundle\Utils\Distrito;

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
        $session = $request->getSession();
        $doctrine = $this->getDoctrine();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/ocio/amigos/chat');
        if (!$status) {
            return new RedirectResponse('/');
        }
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get("id_usuario"));
        $ROL = $doctrine->getRepository('AppBundle:Rol')->findOneByNombre('Jugador');
        $MI_ROL = $USUARIO->getIdRol();
        $DATOS = DataManager::setDefaultData($doctrine, 'Chat', $session);
        unset($DATOS['TDV']);
        $DATOS['DISTRITOS'] = null;
        if ($USUARIO->getIdDistrito() !== null) {
            if ($MI_ROL === $ROL) {
                $DATOS['DISTRITOS'][] = $USUARIO->getIdDistrito();
            } else {
                $DATOS['DISTRITOS'] = $doctrine->getRepository('AppBundle:UsuarioDistrito')->findAll();
            }
        }
        $CIUDADANOS = Usuario::getUsuariosMenosSistema($doctrine);
        $DATOS['CIUDADANOS'] = [];
        foreach ($CIUDADANOS as $CIUDADANO) {
            if ($CIUDADANO->getSeudonimo() !== null) {
                $DATOS['CIUDADANOS'][] = $CIUDADANO;
            }
        }
        return $this->render('ciudadano/ocio/chat.twig', $DATOS);
    }

    /**
     * @Route("/ciudadano/ocio/amigos/chat/getSinVer", name="getSinVer")
     */
    public function getSinVerAction(Request $request) {
        $session = $request->getSession();
        $doctrine = $this->getDoctrine();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/ocio/amigos/chat/getSinVer');
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Permiso denegado')), 200);
        }
        $DATOS = [];
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get("id_usuario"));
        $CIUDADANOS = Usuario::getUsuariosMenosSistema($doctrine);
        $DATOS['CIUDADANOS'] = [];
        if (count($CIUDADANOS)) {
            foreach ($CIUDADANOS as $CIUDADANO) {
                if ($CIUDADANO->getSeudonimo() !== null) {
                    $aux = [];
                    $aux['ID'] = $CIUDADANO->getIdUsuario();
                    $aux['NUM_MENSAJES'] = Usuario::numeroMensajesChat($doctrine, $USUARIO, $CIUDADANO);
                    $DATOS['CIUDADANOS'][] = $aux;
                }
            }
        }
        $DATOS['GUETO'] = [];
        $DATOS['GUETO']['ID'] = 0;
        $DATOS['GUETO']['NUM_MENSAJES'] = Usuario::numeroMensajesChat($doctrine, $USUARIO, null, true);

        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $DATOS)), 200);
    }

    /**
     * @Route("/ciudadano/getChat/{id_usuario_destino}/{id_grupo_destino}/{offset}", name="getChat")
     */
    public function getChat(Request $request, $id_usuario_destino, $id_grupo_destino, $offset) {
        $session = $request->getSession();
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $qb = $em->createQueryBuilder();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/getChat/' . $id_usuario_destino . '/' . $id_grupo_destino . '/' . $offset);
        if (!$status) {
            $JsonResponse_data["estado"] = "ERROR";
            $JsonResponse_data["message"] = "No autorizado";
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
            $JsonResponse_data['distrito']['nombre'] = $Distrito->getNombre();
            $query = $qb->select('ch')
                    ->from('\AppBundle\Entity\Chat', 'ch')
                    ->where('ch.idDistrito = :receptor_distrito')
                    ->orderBy('ch.fecha', 'ASC')
                    ->setParameters(array('receptor_distrito' => $Distrito));
        }
        $CHAT = $query->getQuery()->getOneOrNullResult();
        if ($CHAT === null) {
            $JsonResponse_data["estado"] = "ERROR";
            $JsonResponse_data["message"] = "<center>Sé el primero en saludar<center>";
            return new JsonResponse($JsonResponse_data, 200);
        }
        $CHAT_SIN_VER = $doctrine->getRepository('AppBundle:ChatSinVer')->findOneBy([
            'idUsuario' => $Usuario, 'idChat' => $CHAT
        ]);
        if (null !== $CHAT_SIN_VER) {
            $em->remove($CHAT_SIN_VER);
        }
        $CHAT->setFechaUltimoMensaje(new \DateTime('now'));
        $em->persist($CHAT);
        $em->flush();

        $query = $qb->select('cm')
                ->from('\AppBundle\Entity\ChatMensajes', 'cm')
                ->where('cm.idChat = :idChat')
                ->orderBy('cm.fecha', 'ASC')
//                ->orderBy('cm.fecha', 'DESC')
                ->setParameters(array('idChat' => $CHAT));
        $CHATS = $query->getQuery()->getResult();

        if (!count($CHATS)) {
            $JsonResponse_data["estado"] = "ERROR";
            $JsonResponse_data["message"] = "<center>Sé el primero en saludar<center>";
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
                if ($CHAT->getIdUsuario() !== $Usuario) {
                    $CHAT->setVisto(1);
                }
                $em->persist($CHAT);
            }
            $em->flush();
        }
        return new JsonResponse($JsonResponse_data, 200);
    }

    /**
     * @Route("/ciudadano/getChatComun", name="getChatComun")
     */
    public function getChatComunAction(Request $request) {
        $session = $request->getSession();
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $qb = $em->createQueryBuilder();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/getChatComun');
        if (!$status) {
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'No autorizado'), 200);
        }
        $Usuario = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get("id_usuario"));
        if (null === $Usuario) {
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'No se encuentra el usuario'), 200);
        }

        $CHAT = $doctrine->getRepository('AppBundle:Chat')->findOneByIdChat(1);
        if ($CHAT === null) {
            $JsonResponse_data["estado"] = "ERROR";
            $JsonResponse_data["message"] = "<center>Sé el primero en saludar<center>";
            return new JsonResponse($JsonResponse_data, 200);
        }
        $CHAT_SIN_VER = $doctrine->getRepository('AppBundle:ChatSinVer')->findOneBy([
            'idUsuario' => $Usuario, 'idChat' => $CHAT
        ]);
        if (null !== $CHAT_SIN_VER) {
            $em->remove($CHAT_SIN_VER);
        }
        $CHAT->setFechaUltimoMensaje(new \DateTime('now'));
        $em->persist($CHAT);
        $em->flush();

        $query = $qb->select('cm')
                ->from('\AppBundle\Entity\ChatMensajes', 'cm')
                ->where('cm.idChat = :idChat')
                ->orderBy('cm.fecha', 'ASC')
                ->setParameters(array('idChat' => $CHAT))
                ->setFirstResult(0);
                //->setMaxResults( 50 );
        $CHATS = $query->getQuery()->getResult();

        if (!count($CHATS)) {
            $JsonResponse_data["estado"] = "ERROR";
            $JsonResponse_data["message"] = "<center>Sé el primero en saludar<center>";
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
                if ($CHAT->getIdUsuario() !== $Usuario) {
                    $CHAT->setVisto(1);
                }
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
        if ($request->getMethod() == 'POST') {
            $session = $request->getSession();
            $doctrine = $this->getDoctrine();
            $em = $doctrine->getManager();
            $qb = $em->createQueryBuilder();
            $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/enviarMensajeChat');
            if (!$status) {
                return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Acceso denegado'), 200);
            }

            $Usuario = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get("id_usuario"));
            $Usuario2 = $request->request->get('id_usuario');
            $Distrito = $request->request->get('id_distrito');
            $COMUN = $request->request->get('comun');
            $mensaje = $request->request->get('mensaje');
            if ($Usuario2) {
                $receptor_usuario = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($Usuario2);
                $query = $qb->select('ch')
                        ->from('\AppBundle\Entity\Chat', 'ch')
                        ->where('ch.idUsuario1 = :emisor AND ch.idUsuario2 = :receptor_usuario')
                        ->orWhere('ch.idUsuario1 = :receptor_usuario AND ch.idUsuario2 = :emisor')
                        ->orderBy('ch.fecha', 'ASC')
                        ->setParameters(array('emisor' => $Usuario, 'receptor_usuario' => $receptor_usuario));
            } else if ($Distrito) {
                $distrito_chat = $doctrine->getRepository('AppBundle:UsuarioDistrito')->findOneByIdUsuarioDistrito($Distrito);
                $query = $qb->select('ch2')
                        ->from('\AppBundle\Entity\Chat', 'ch2')
                        ->where('ch2.idDistrito = :receptor_distrito')
                        ->orderBy('ch2.fecha', 'ASC')
                        ->setParameters(array('receptor_distrito' => $distrito_chat));
            } else if ($COMUN) {
                $CHAT = $doctrine->getRepository('AppBundle:Chat')->findOneByIdChat(1);
            }
            if (!$COMUN) {
                $CHAT = $query->getQuery()->getOneOrNullResult();
            }
            $fecha = new \DateTime('now');

            if ($CHAT === null) {
                $CHAT = new \AppBundle\Entity\Chat();
                $CHAT->setIdUsuario1($Usuario);
                if (isset($receptor_usuario)) {
                    $CHAT->setIdUsuario2($receptor_usuario);
                } else if (isset($distrito_chat)) {
                    $CHAT->setIdDistrito($distrito_chat);
                } else {
                    Utils::setError($doctrine, 0, 'Se ha intentado obtener un chat sin usuario/distrito');
                    return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Se ha prodicido un error'), 200);
                }
                $CHAT->setFecha($fecha);
                $CHAT->setFechaUltimoMensaje($fecha);
            } else {
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

            if (isset($receptor_usuario)) {
                Usuario::setChatSinVer($doctrine, $receptor_usuario, $CHAT);
            } else if (isset($distrito_chat)) {
                $CIUDADANOS = Distrito::getCiudadanosActivosDistrito($doctrine, $distrito_chat);
                if (count($CIUDADANOS)) {
                    foreach ($CIUDADANOS as $CIUDADANO) {
                        Usuario::setChatSinVer($doctrine, $CIUDADANO, $CHAT);
                    }
                }
            } else {
                $USUARIOS = $doctrine->getRepository('AppBundle:Usuario')->findAll();
                if (count($USUARIOS)) {
                    foreach ($USUARIOS as $USUARIO) {
                        Usuario::setChatSinVer($doctrine, $USUARIO, $CHAT);
                    }
                }
            }

            return new JsonResponse(array('estado' => 'OK', 'message' => 'El mesaje ha sido enviado correctamente'), 200);
        }
        return new JsonResponse(array('estado' => 'ERROR', 'message' => 'No se han enviado datos'), 200);
    }

    /**
     * @Route("/ciudadano/ocio/amigos/fotos", name="fotos")
     */
    public function fotosAction(Request $request) {
        $session = $request->getSession();
        $doctrine = $this->getDoctrine();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/ocio/amigos/fotos');
        if (!$status) {
            return new RedirectResponse('/');
        }
        $DATOS = DataManager::setDefaultData($doctrine, 'Fotos', $session);

        return $this->render('ciudadano/ocio/fotos.twig', $DATOS);
    }

    /**
     * @Route("/ciudadano/obtenerFotos/{limite}", name="getPhotos")
     */
    public function getPhotosAction(Request $request, $limite) {
        $session = $request->getSession();
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $qb = $em->createQueryBuilder();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/obtenerFotos');
        if (!$status) {
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado'));
        }
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get('id_usuario'));
        $offset = $limite - 5;
        $query = $qb->select('f')
                ->from('\AppBundle\Entity\AlbumFoto', 'f')
                ->orderBy('f.fecha', 'DESC')
                ->setFirstResult( $limite )
                ->setMaxResults( 5 );
        $FOTOS = $query->getQuery()->getResult();

        if (count($FOTOS)) {
            $datos = [];
            foreach ($FOTOS AS $FOTO) {
                $aux['usuario']['mi_alias'] = $USUARIO->getSeudonimo();
                $aux['usuario']['alias'] = $FOTO->getIdUsuario()->getSeudonimo();
                $aux['usuario']['dni'] = $FOTO->getIdUsuario()->getDni();
                $aux['fecha'] = $FOTO->getFecha();
                $aux['imagen'] = $FOTO->getImagen();
                $aux['id'] = $FOTO->getIdAlbumFoto();
                $aux['titulo'] = $FOTO->getTitulo();
                $REACCIONES = $doctrine->getRepository('AppBundle:FotoReaccion')->findByIdAlbumFoto($FOTO);
                $aux['mi_like'] = 0;
                $aux['mi_dislike'] = 0;
                $aux['likes'] = 0;
                $aux['dislikes'] = 0;
                if (count($REACCIONES)) {
                    foreach ($REACCIONES AS $REACCION) {
                        if ($REACCION->getIdUsuario() === $USUARIO) {
                            if ($REACCION->getLikeSocial()) {
                                $aux['mi_like'] = 1;
                            } else {
                                $aux['mi_dislike'] = 1;
                            }
                        }
                        if ($REACCION->getLikeSocial()) {
                            $aux['likes'] ++;
                        } else {
                            $aux['dislikes'] ++;
                        }
                    }
                }

                $datos[] = $aux;
            }
        }
        return new JsonResponse(array('estado' => 'OK', 'fotos' => $datos), 200);
    }
    
    /**
     * @Route("/ciudadano/obtenerMisFotos/{limite}", name="getMisPhotos")
     */
    public function getMisPhotosAction(Request $request, $limite) {
        $session = $request->getSession();
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $qb = $em->createQueryBuilder();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/obtenerMisFotos');
        if (!$status) {
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado'));
        }
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get('id_usuario'));
        $offset = $limite - 5;
        $query = $qb->select('f')
                ->from('\AppBundle\Entity\AlbumFoto', 'f')
                ->where('f.idUsuario = :USUARIO')
                ->setParameter('USUARIO', $USUARIO)
                ->orderBy('f.fecha', 'DESC')
                ->setFirstResult( $limite )
                ->setMaxResults( 5 );
        $FOTOS = $query->getQuery()->getResult();

        if (count($FOTOS)) {
            $datos = [];
            foreach ($FOTOS AS $FOTO) {
                $aux['usuario']['mi_alias'] = $USUARIO->getSeudonimo();
                $aux['usuario']['alias'] = $FOTO->getIdUsuario()->getSeudonimo();
                $aux['usuario']['dni'] = $FOTO->getIdUsuario()->getDni();
                $aux['fecha'] = $FOTO->getFecha();
                $aux['imagen'] = $FOTO->getImagen();
                $aux['id'] = $FOTO->getIdAlbumFoto();
                $aux['titulo'] = '';
                $REACCIONES = $doctrine->getRepository('AppBundle:FotoReaccion')->findByIdAlbumFoto($FOTO);
                $aux['mi_like'] = 0;
                $aux['mi_dislike'] = 0;
                $aux['likes'] = 0;
                $aux['dislikes'] = 0;
                if (count($REACCIONES)) {
                    foreach ($REACCIONES AS $REACCION) {
                        if ($REACCION->getIdUsuario() === $USUARIO) {
                            if ($REACCION->getLikeSocial()) {
                                $aux['mi_like'] = 1;
                            } else {
                                $aux['mi_dislike'] = 1;
                            }
                        }
                        if ($REACCION->getLikeSocial()) {
                            $aux['likes'] ++;
                        } else {
                            $aux['dislikes'] ++;
                        }
                    }
                }

                $datos[] = $aux;
            }
        }
        return new JsonResponse(array('estado' => 'OK', 'fotos' => $datos), 200);
    }

    /**
     * @Route("/ciudadano/subirImagenAlbum", name="subirImagenAlbum")
     */
    public function subirImagenAlbumAction(Request $request) {
        $session = $request->getSession();
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/subirImagenAlbum');
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR - Permiso denegado')), 200);
        }
        if ($request->getMethod() == 'POST') {
            $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get("id_usuario"));
            $IMAGENES = $request->files->get('imagenes');
            $TITULO = $request->request->get('titulo');
            if (count($IMAGENES)) {
                foreach ($IMAGENES as $IMAGEN) {
                    $IMG = new \AppBundle\Entity\AlbumFoto();
                    $IMG->setFecha(new \DateTime('now'));
                    $IMG->setTitulo($TITULO);
                    $IMG->setIdUsuario($USUARIO);
                    $em->persist($IMG);
                    $em->flush();

                    $ruta = 'images/users/' . $USUARIO->getDni() . '/galeria';
                    if (!file_exists($ruta)) {
                        mkdir($ruta, 0777, true);
                    }
                    $nombre_foto = Utils::replaceAccented($IMG->getIdAlbumFoto() . '.' . $IMAGEN->getClientOriginalExtension());
                    $IMG->setImagen($nombre_foto);
                    $em->persist($IMG);
                    $em->flush();
                    $IMAGEN->move($ruta, $nombre_foto);
                }
            }
            return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => 'Imagen publicada correctamente')), 200);
        }
        return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No se han enviado datos')), 200);
    }

    /**
     * @Route("/ciudadano/ocio/amigos/fotos/like/{id_album_foto}/{like}", name="likeFoto")
     */
    public function likeFotoAction(Request $request, $id_album_foto, $like) {
        $session = $request->getSession();
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/ocio/amigos/fotos/like/' . $id_album_foto . '/' . $like);
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
                if (intval($like) !== intval($REACCION->getLikeSocial())) {
                    $REACCION->setLikeSocial($like);
                } else {
                    $em->remove($REACCION);
                }
            }
            $em->flush();
        } else {
            return new JsonResponse(array('estado' => 'ERROR - No había ninguna foto con el id: ' . $id_album_foto), 200);
        }
        return new JsonResponse(array('estado' => 'OK'), 200);
    }

    /**
     * @Route("/ciudadano/ocio/altruismo", name="altruismo")
     */
    public function altruismo_ciudadanoAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $qb = $em->createQueryBuilder();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/ocio/altruismo');
        if (!$status) {
            return new RedirectResponse('/');
        }
        $DATOS = DataManager::setDefaultData($doctrine, 'Altruismo', $session);
        $ROL = $doctrine->getRepository('AppBundle:Rol')->findOneByNombre('Jugador');
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get("id_usuario"));
        $query = $qb->select(['c.seudonimo', 'c.idUsuario'])
                ->from('\AppBundle\Entity\Usuario', 'c')
                ->where('c.idRol = :ROL AND c.idUsuario != :ID_USUARIO AND c.seudonimo IS NOT NULL')
                ->setParameters(['ROL' => $ROL, 'ID_USUARIO' => $USUARIO->getIdUsuario()]);
        $DATOS['CIUDADANOS'] = $query->getQuery()->getResult();

        return $this->render('ciudadano/ocio/altruismo.html.twig', $DATOS);
    }

    /**
     * @Route("/ciudadano/ocio/altruismo/getCiudadanosDonar", name="getCiudadanosDonar")
     */
    public function getCiudadanosDonarAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/ocio/altruismo/getCiudadanosDonar');
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Permiso denegado')), 200);
        }
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get("id_usuario"));
        $DATOS = [];
        $CIUDADANOS_VIVOS = Usuario::getCiudadanosVivos($doctrine);
        if (count($CIUDADANOS_VIVOS)) {
            $DATOS['CIUDADANOS'] = [];
            foreach ($CIUDADANOS_VIVOS as $CIUDADANO) {
                if ($CIUDADANO !== $USUARIO && $CIUDADANO->getSeudonimo() !== '') {
                    $aux = [];
                    $aux['ID'] = $CIUDADANO->getIdUsuario();
                    $aux['ALIAS'] = $CIUDADANO->getSeudonimo();
                    $DATOS['CIUDADANOS'][] = $aux;
                }
            }
            $CARTA = $doctrine->getRepository('AppBundle:BonificacionExtra')->findOneByIdBonificacionExtra(4);
            $MI_CARTA = $doctrine->getRepository('AppBundle:BonificacionXUsuario')->findOneBy([
                'idUsuario' => $USUARIO, 'idBonificacionExtra' => $CARTA, 'usado' => 0
            ]);
            $DATOS['BONIFICACION'] = 0;
            if (null !== $MI_CARTA) {
                $DATOS['BONIFICACION'] = 1;
            }
            return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $DATOS)), 200);
        }
        return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Por el momento no hay ciudadanos a los que puedas donar')), 200);
    }

    /**
     * @Route("/ciudadano/ocio/altruismo/donarTdv/{id_usuario}/{tdv}/{bonificacion}", name="donar")
     */
    public function donarAction(Request $request, $id_usuario, $tdv, $bonificacion) {
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/ocio/altruismo/donarTdv/' . $id_usuario . '/' . $tdv);
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Permiso denegado')), 200);
        }
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get("id_usuario"));
        if (!Usuario::puedoRealizarTransaccion($USUARIO, $tdv)) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No tienes suficiente TdV.')), 200);
        }
        if ($USUARIO->getSeudonimo() === '') {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Debes tener seudonimo para poder donar.')), 200);
        }
        $USUARIO_DESTINO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($id_usuario);
        if ($USUARIO_DESTINO === null || $USUARIO === null) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Se ha producido un error al intentar encontrar al usuario.')), 200);
        }
        if (Usuario::heDonadoYa($doctrine, $USUARIO, $USUARIO_DESTINO)) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Lo siento, ya habías donado a @' . $USUARIO_DESTINO->getSeudonimo() . ' anteriormente. No puedes volver a donarle TdV.')), 200);
        }
        if ($bonificacion) {
            $DOS_DIAS = 172800;
            $CARTA = $doctrine->getRepository('AppBundle:BonificacionExtra')->findOneByIdBonificacionExtra(4);
            $MI_CARTA = $doctrine->getRepository('AppBundle:BonificacionXUsuario')->findOneBy([
                'idUsuario' => $USUARIO, 'idBonificacionExtra' => $CARTA, 'usado' => 0
            ]);
            if (null === $MI_CARTA) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No tienes carta de bonificación.')), 200);
            }
            $MI_CARTA->setUsado(1);
            $em->persist($MI_CARTA);
            $em->flush();
            Usuario::operacionSobreTdV($doctrine, $USUARIO_DESTINO, $DOS_DIAS, 'Ingreso - Donación de @' . $USUARIO->getSeudonimo());
        } else {
            Usuario::operacionSobreTdV($doctrine, $USUARIO, $tdv * (-1), 'Cobro - Donación a @' . $USUARIO_DESTINO->getSeudonimo());
            Usuario::operacionSobreTdV($doctrine, $USUARIO_DESTINO, $tdv, 'Ingreso - Donación de @' . $USUARIO->getSeudonimo());
        }
        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => 'Tdv donado')), 200);
    }

    /**
     * @Route("/ciudadano/compruebaAlias/{alias}", name="compruebaAlias")
     */
    public function compruebaAlias(Request $request, $alias) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/compruebaAlias/' . $alias);
        if (!$status) {
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Permiso denegado'), 200);
        }
        if (Usuario::aliasDisponible($doctrine, $session, $alias)) {
            return new JsonResponse(array('estado' => 'OK', 'message' => 'Alias disponible'), 200);
        }
        return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Este alias no está disponible'), 200);
    }

    /**
     * 
     * @Route("/ciudadano/ocio/getVacaciones", name="getVacaciones")
     */
    public function getVacacionesAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/ocio/getVacaciones');
        if (!$status) {
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado')), 200);
        }
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get('id_usuario'));
        $TARJETA = $doctrine->getRepository('AppBundle:BonificacionExtra')->findOneByIdBonificacionExtra(9);
        if (null === $TARJETA) {
            Utils::setError($doctrine, 1, 'No se encuentra BONIFICACION_EXTRA con id 1', $USUARIO);
            return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Error inesperado')), 200);
        }
        $bonificacion = false;
        $TARJETA_VACACIONES = $doctrine->getRepository('AppBundle:BonificacionXUsuario')->findOneBy([
            'idBonificacionExtra' => $TARJETA, 'idUsuario' => $USUARIO, 'usado' => 0
        ]);
        if (null !== $TARJETA_VACACIONES) {
            $bonificacion = true;
        }

        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $bonificacion)), 200);
    }

    /**
     * 
     * @Route("/ciudadano/ocio/solicitarVacaciones", name="solicitarVacaciones")
     */
    public function solicitarVacacionesAction(Request $request) {
        if ($request->getMethod() == 'POST') {
            $doctrine = $this->getDoctrine();
            $em = $doctrine->getManager();
            $session = $request->getSession();
            $status = Usuario::compruebaUsuario($doctrine, $session, '/ciudadano/ocio/solicitarVacaciones');
            if (!$status) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado')), 200);
            }
            $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get('id_usuario'));
            $tarjeta_experiencia = $request->request->get('tarjeta_experiencia');
            if ($tarjeta_experiencia) {
                $TARJETA = $doctrine->getRepository('AppBundle:BonificacionExtra')->findOneByIdBonificacionExtra(9);
                if (null === $TARJETA) {
                    Utils::setError($doctrine, 1, 'No se encuentra BONIFICACION_EXTRA con id 1', $USUARIO);
                    return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Error inesperado')), 200);
                }
                $TARJETA_VACACIONES = $doctrine->getRepository('AppBundle:BonificacionXUsuario')->findOneBy([
                    'idBonificacionExtra' => $TARJETA, 'idUsuario' => $USUARIO, 'usado' => 0
                ]);
                if (null === $TARJETA_VACACIONES) {
                    return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No tienes tarjeta de vacaciones')), 200);
                }
            }
            $tiempo = 432000;
            $VACACIONES = $doctrine->getRepository('AppBundle:UsuarioPrestamo')->findBy([
                'idUsuario' => $USUARIO,
                'motivo' => 'vacaciones'
            ]);
            if (count($VACACIONES)) {
                return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'Ya habías solicitado vacaciones anteriormente.')), 200);
            }
            $PRESTAMO = new \AppBundle\Entity\UsuarioPrestamo();
            $PRESTAMO->setIdUsuario($USUARIO);
            $PRESTAMO->setMotivo('vacaciones');
            $PRESTAMO->setCantidad($tiempo);
            $PRESTAMO->setRestante(0);
            $PRESTAMO->setInteres(0);
            $PRESTAMO->setFecha(new \DateTime('now'));
            $em->persist($PRESTAMO);
            if ($tarjeta_experiencia) {
                $TARJETA_VACACIONES->setUsado(1);
                $em->persist($TARJETA_VACACIONES);
            } else {
                Usuario::operacionSobreTdV($doctrine, $USUARIO, (-1) * ($tiempo / 2), 'Gasto - Semana de vacaciones');
            }
            $ESTADO_VACACIONES = $doctrine->getRepository('AppBundle:UsuarioEstado')->findOneByNombre('Vacaciones');

            $CUENTA = $USUARIO->getIdCuenta();
            $finBloqueo = new \DateTime('now');
            $finBloqueo->add(new \DateInterval('P5D'));
            $CUENTA->setFinbloqueo($finBloqueo);
            $em->persist($CUENTA);

            $USUARIO->setIdEstado($ESTADO_VACACIONES);
            $HOY = new \DateTime('now');
            $DATE = date('Y-m-d H:i:s', $HOY->getTimestamp() + $tiempo);
            $USUARIO->setTiempoSinComer(\DateTime::createFromFormat('Y-m-d H:i:s', $DATE));
            $USUARIO->setTiempoSinBeber(\DateTime::createFromFormat('Y-m-d H:i:s', $DATE));
            $em->persist($USUARIO);

            $em->flush();
            Usuario::operacionSobreTdV($doctrine, $USUARIO, $tiempo, 'Ingreso - Vacaciones');
            return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => 'Vacaciones en marcha.')), 200);
        }
        return new JsonResponse(json_encode(array('estado' => 'ERROR', 'message' => 'No se ha recibido ningún dato')), 200);
    }

}
