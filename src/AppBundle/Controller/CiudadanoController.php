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

        return $this->render('ciudadano/ocio/fotos.twig', $DATOS);
    }

    /**
     * @Route("/ciudadano/obtenerFotos", name="getPhotos")
     */
    public function getPhotos(Request $request) {
        $session = $request->getSession();
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $qb = $em->createQueryBuilder();
        $UsuarioClass = new \AppBundle\Utils\Usuario();
        $status = $UsuarioClass->compruebaUsuario($doctrine, $session, '/ciudadano/ocio/amigos/fotos');
        if (!$status) {
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Acceso no autorizado'));
        }

        $query = $qb->select('f')
                ->from('\AppBundle\Entity\AlbumFoto', 'f')
                ->orderBy('f.fecha', 'DESC');
        $FOTOS = $query->getQuery()->getResult();

        if (count($FOTOS)) {
            $datos = [];
            foreach ($FOTOS AS $FOTO) {
                $aux['usuario']['alias'] = $FOTO->getIdUsuario()->getSeudonimo();
                $aux['usuario']['dni'] = $FOTO->getIdUsuario()->getDni();
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

                $datos[] = $aux;
            }
        }
        return new JsonResponse(array('estado' => 'OK', 'fotos' => $datos), 200);
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
     * @Route("/ciudadano/ocio/altruismo", name="altruismo")
     */
    public function altruismo_ciudadanoAction(Request $request) {
        $DataManager = new \AppBundle\Utils\DataManager();
        $UsuarioClass = new \AppBundle\Utils\Usuario();
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $qb = $em->createQueryBuilder();
        $session = $request->getSession();
        $status = $UsuarioClass->compruebaUsuario($doctrine, $session, '/ciudadano/ocio/altruismo');
        if (!$status) {
            return new RedirectResponse('/');
        }
        $DATOS = $DataManager->setDefaultData($doctrine, 'Altruismo', $session);
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
     * @Route("/ciudadano/ocio/altruismo/getMina", name="getMina")
     */
    public function getMinaAction(Request $request) {
        $UsuarioClass = new \AppBundle\Utils\Usuario();
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $em = $doctrine->getManager();
        $qb = $em->createQueryBuilder();
        $status = $UsuarioClass->compruebaUsuario($doctrine, $session, '/ciudadano/ocio/altruismo/getMina');
        if (!$status) {
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Permiso denegado'), 200);
        }
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get("id_usuario"));
        $MINAS = $doctrine->getRepository('AppBundle:Mina')->findAll();
        if (count($MINAS)) {
            $ahora = new \DateTime('now');
            foreach ($MINAS as $MINA) {
                if ($ahora <= $MINA->getFechaFinal()) {
                    $datos = [];
                    $datos['fecha_final'] = $MINA->getFechaFinal();
                    $datos['ciudadanos_mina'] = null;
                    $datos['alias'] = null;
                    $datos['distrito'] = null;
                    $datos['tiempo_prorroga'] = null;
                    $query = $qb->select('um')
                            ->from('\AppBundle\Entity\UsuarioMina', 'um')
                            ->where('um.idMina = :IdMina')
                            ->orderBy('um.fecha', 'DESC')
                            ->setParameters(['IdMina' => $MINA]);
                    $USUARIO_MINA = $query->getQuery()->getOneOrNullResult();

                    if ($USUARIO->getSeudonimo() === null) {
                        return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Para participar debes tener un alias.<br>Puedes crearte uno en la sección Jugador de la página principal'), 200);
                    }
                    if ($USUARIO->getIdDistrito() === null) {
                        return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Para participar debes pertenecer a un distrito.<br> Solicita un distrito a tu Guardián del tiempo'), 200);
                    }
                    $datos['mi_distrito'] = $USUARIO->getIdDistrito()->getNombre();
                    if ($USUARIO_MINA !== null) {
                        $datos['alias'] = $USUARIO_MINA->getIdUsuario()->getSeudonimo();
                        $datos['distrito'] = $USUARIO_MINA->getIdUsuario()->getIdDistrito()->getNombre();
                        $datos['tiempo_prorroga'] = $USUARIO_MINA->getFecha();
                    }
                    return new JsonResponse(array('estado' => 'OK', 'datos' => $datos), 200);
                }
            }
        }
        return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Actualmente no hay minas que desactivar'), 200);
    }

    /**
     * @Route("/ciudadano/ocio/altruismo/enviarCodigo", name="enviarCodigo")
     */
    public function enviarCodigoAction(Request $request) {
        $UsuarioClass = new \AppBundle\Utils\Usuario();
        $PagoClass = new \AppBundle\Utils\Pago();
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $em = $doctrine->getManager();
        $status = $UsuarioClass->compruebaUsuario($doctrine, $session, '/ciudadano/ocio/altruismo/enviarCodigo');
        if (!$status) {
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Permiso denegado'), 200);
        }
        if ($request->getMethod() == 'POST') {
            $codigo = $request->request->get('codigo');
            $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get("id_usuario"));
            $MINAS = $doctrine->getRepository('AppBundle:Mina')->findAll();
            if (count($MINAS)) {
                $ahora = new \DateTime('now');
                foreach ($MINAS as $MINA) {
                    if ($ahora <= $MINA->getFechaFinal()) {
                        if ($MINA->getCodigo() === $codigo) {
                            $USUARIO_MINA = new \AppBundle\Entity\UsuarioMina();
                            $USUARIO_MINA->setFecha(new \DateTime('now'));
                            $USUARIO_MINA->setIdMina($MINA);
                            $USUARIO_MINA->setIdUsuario($USUARIO);
                            $em->persist($USUARIO_MINA);
                            $em->flush();
                            $PagoClass->pagarMina($doctrine, $USUARIO);
                            return new JsonResponse(array('estado' => 'OK', 'message' => 'Enhorabuena! La mina ha sido desactivada'), 200);
                        } else {
                            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'El código es incorrecto'), 200);
                        }
                    }
                }
            }
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Actualmente no hay minas que desactivar'), 200);
        }
    }

    /**
     * @Route("/ciudadano/ocio/altruismo/donarTdv/{id_usuario}/{tdv}", name="donar")
     */
    public function donarAction(Request $request, $id_usuario, $tdv) {
        $UsuarioClass = new \AppBundle\Utils\Usuario();
        $doctrine = $this->getDoctrine();
        $session = $request->getSession();
        $status = $UsuarioClass->compruebaUsuario($doctrine, $session, '/ciudadano/ocio/altruismo/donarTdv/' . $id_usuario . '/' . $tdv);
        if (!$status) {
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Permiso denegado'), 200);
        }

        if (!$UsuarioClass->puedoRealizarTransaccion($doctrine, $session, $tdv)) {
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'No tienes suficiente TdV'), 200);
        }
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get("id_usuario"));
        $USUARIO_DESTINO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($id_usuario);
        if ($USUARIO_DESTINO === null || $USUARIO === null) {
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Se ha producido un error al intentar encontrar al usuario'), 200);
        }
        if ($UsuarioClass->heDonadoYa($doctrine, $session, $USUARIO_DESTINO)) {
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Lo siento, ya habías donado a @' . $USUARIO_DESTINO->getSeudonimo() . ' anteriormente. No puedes volver a donarle TdV.'), 200);
        }
        $UsuarioClass->operacionSobreTdV($doctrine, $USUARIO, $tdv * (-1), 'Cobro - Donación a @' . $USUARIO_DESTINO->getSeudonimo());
        $UsuarioClass->operacionSobreTdV($doctrine, $USUARIO_DESTINO, $tdv, 'Ingreso - Donación a @' . $USUARIO->getSeudonimo());
        return new JsonResponse(array('estado' => 'OK', 'message' => 'Tdv donado'), 200);
    }

}
