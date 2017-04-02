<?php

namespace AppBundle\Utils;

use \Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Utils\Usuario;
use AppBundle\Utils\Utils;

class Usuario {

    public function inicio_usuario($usuario, $session) {
        $rol_usu = $usuario->getIdRol()->getIdRol();

        if ($rol_usu === 1) {

            $render = $this->inicio_ciudadano($usuario, $session);
        }
        if ($rol_usu === 2) {
            $render = $this->inicio_guardian();
        }
        return $render;
    }

    public function inicio_guardian() {
        $DATOS = ['TITULO' => 'InTime - Guardián del Tiempo'];
        return $this->render('guardian/guardian.html.twig', $DATOS);
    }

    public function inicio_ciudadano($usuario, $session) {
        $DATOS = [];

        if ($usuario->getNombre() !== null) {
            $DATOS['TITULO'] = 'InTime - ' . $usuario->getNombre();
        } else {
            $session->set('registro_completo', false);
            $DATOS['TITULO'] = 'InTime - Desconocido';
        }
        return $this->render('ciudadano/ciudadano.html.twig', $DATOS);
    }

    /**
     * Encripta con sha1 y un salt
     * @param type $usuario
     * @param type $password
     * @return string
     */
    static function encriptar($usuario, $password) {
        $salt = $usuario->getIdUsuario();
        return sha1($salt . $password);
    }

    static function activar_usuario($doctrine, $USUARIO) {
        $USUARIO_ESTADO = $USUARIO->getIdEstado();

        // Si el usuario está inactivo (no ha entrado nunca)
        if ($USUARIO_ESTADO->getNombre() === 'Inactivo') {
            // Preparamos el entityManager
            $em = $doctrine->getEntityManager();
            // Creamos un nuevo estado Activo
            $ESTADO_ACTIVO = $doctrine->getRepository('AppBundle:UsuarioEstado')->findOneByNombre('Activo');
            $USUARIO->setIdEstado($ESTADO_ACTIVO);
            $em->persist($USUARIO);
            $em->flush();
        }
    }

    static function addTdVEjercicio($doctrine, $USUARIO, $timestamp, $tdvARestar = null) {
        $em = $doctrine->getManager();
        $TdV_u = $USUARIO->getIdCuenta()->getTdv()->getTimestamp();
        $TdV = 0;
        $TdV += $TdV_u;
        $TdV += $timestamp;
        if ($tdvARestar !== null) {
            $nota_numerica = $tdvARestar->getCorrespondenciaNumerica();
            $tdvARestarTimestamp = $doctrine->getRepository('AppBundle:Constante')
                            ->findOneByClave('pago_paga_' . $nota_numerica)->getValor();
            $TdV -= $tdvARestarTimestamp;
        }
        $HOY = new \DateTime('now');
        //$TDV_TIMESTAMP = $HOY->getTimestamp() + $TdV;
        $TDV_FORMATO = date('Y-m-d H:i:s', intval($TdV));
        $USUARIO->setTiempoSinComer(\DateTime::createFromFormat('Y-m-d H:i:s', $TDV_FORMATO));
        $em->persist($USUARIO);
        $em->flush();
    }

    /**
     * Retorna el TdV de bonificación dependiendo de la nota obtenida
     * @param doctrine $doctrine
     * @param Entity:CALIFICACIONES $CALIFICACION
     * @return TdV|null
     */
    static function getTimesTampCalificacion($doctrine, $CALIFICACION) {
        if (!$CALIFICACION === null) {
            return null;
        }
        $correspondencia_numerica = $CALIFICACION->getCorrespondenciaNumerica();
        return $doctrine->getRepository('AppBundle:Constante')
                        ->findOneByClave('pago_paga_' . $correspondencia_numerica)->getValor();
    }

    /**
     * Obtener todos los usuarios por Rol (especificando el Rol por String)
     * 
     * @param type $doctrine
     * @param type $stringRol
     * @return array<Entity:Usuario> Array de usuarios con el Rol $stringRol, -1 en cualquier otro caso
     */
    static function getAllByStringRol($doctrine, $stringRol) {
        $ROL = $doctrine->getRepository('AppBundle:Rol')->findOneByNombre($stringRol);
        return \AppBundle\Utils\Usuario::getAllByIdRol($doctrine, $ROL);
    }

    /**
     * Obtener todos los usuarios por rol (especificando el Rol por Entidad)
     * 
     * @param type $doctrine
     * @param Entity:Rol $ROL Entidad Rol que queremos buscar
     * @return array<Entity:Usuario> Array de los usuarios con el rol $ROL
     */
    static function getAllByIdRol($doctrine, $ROL) {
        $USUARIOS = $doctrine->getRepository('AppBundle:Usuario')->findByIdRol($ROL);
        return $USUARIOS;
    }

    /**
     * Función que sirve para comprobar si un usuario tiene el rol solicitado por el 4 parámetro
     * (false = ciudadano, true = admin), además se registra el acceso o intento de acceso de un
     * usuario con o sin privilegios respectivamente en la ruta indicada en el tercer parámetro.
     * 
     * @param type $doctrine
     * @param type $session
     * @param type $route
     * @param type $admin
     * @return int 0 si la comprobación da error, 1 si todo correcto
     */
    static function compruebaUsuario($doctrine, $session, $route, $admin = false) {
        $id_usuario = $session->get('id_usuario');

        $usuario = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($id_usuario);
        // Si no se ha logueado el usuario se le envía a login
        if (!$usuario) {
            return 0;
        }
        
        $noEsSuper = $usuario->getIdRol()->getIdRol() !== 2 && $usuario->getIdRol()->getIdRol() !== 5;
        
        $em = $doctrine->getManager();
        $fecha = new \DateTime('now');
        $tdv = $usuario->getIdCuenta()->getTdv();
        
        if ($noEsSuper) {
            if ($tdv < $fecha) {
                return 0;
            }
        }

        $log = new \AppBundle\Entity\LogUser();
        $log->setIdUsuario($usuario);
        $log->setAction($route);
        $log->setFecha($fecha);
        $em->persist($log);
        $em->flush();

        // Si se pide acceso admin
        if ($admin) {
            // Si el usuario no es GdT/admin se le envía fuera y se almacena el intento de acceso en el log
            if ($noEsSuper) {
                return 0;
            }
        }
        return 1;
    }

    /**
     * Comprueba que se pueda realizar una operación de cobro sobre el usuario 
     * de la sesión
     * 
     * @param type $doctrine
     * @param type $USUARIO
     * @param int $cantidadACobrar TdV que se le quiere cobrar al usuario
     * @return int 0 si no tiene TdV suficiente, 1 en cualquier otro caso
     */
    static function puedoRealizarTransaccion($USUARIO, $cantidadACobrar) {
        $hoy = new \DateTime('now');
        $tdvUsuario = $USUARIO->getIdCuenta()->getTdv()->getTimestamp() - $hoy->getTimestamp();
        if ($tdvUsuario - $cantidadACobrar <= 0) {
            return 0;
        }
        return 1;
    }

    /**
     * Esta función retorna si el usuario de la sesión ha realizado ya una 
     * donación al usuario que se pasa por parámetro.
     * 
     * @param type $doctrine
     * @param type $session
     * @param Entity $usuario_destino Usuario destino de la donación
     * @return int 1 si se ha realizado una donación con anterioridad al 
     * usuario pasado por parámetro, 0 en cualquier otro caso
     */
    static function heDonadoYa($doctrine, $USUARIO, $usuario_destino) {
        $em = $doctrine->getManager();
        $qb = $em->createQueryBuilder();
        $query = $qb->select('d')
                ->from('\AppBundle\Entity\UsuarioMovimiento', 'd')
                ->where('d.idUsuario = :Usuario AND d.causa = :Causa')
                ->setParameters(['Usuario' => $USUARIO, 'Causa' => 'Cobro - Donación a @' . $usuario_destino->getSeudonimo()]);
        $DONACION = $query->getQuery()->getOneOrNullResult();

        if (null !== $DONACION) {
//            Utils::pretty_print($USUARIO->getSeudonimo() . ' ha donado a ' . $usuario_destino->getSeudonimo() . ': ' . $DONACION->getCantidad());
            return $DONACION->getCantidad();
        }
        return 0;
    }

    /**
     * Función que realiza operaciones de ingreso o cobro sobre una cuenta
     * de un usuario
     * 
     * @param type $doctrine Para poder hacer operaciones con BD
     * @param Entity $USUARIO Entidad usuario del usuario al que se va a cobrar/ingresar
     * @param type $tdv TdV a pagar/cobrar
     * @param type $concepto Concepto del ingreso o cobro
     */
    static function operacionSobreTdV($doctrine, $USUARIO, $tdv, $concepto, $comprobar_muerte = true) {
        $TDV_USUARIO = $USUARIO->getIdCuenta()->getTdv()->getTimestamp();
        $TDV_RESTANTE = $TDV_USUARIO + $tdv;
        $TDV_RESTANTE_DATE = date('Y-m-d H:i:s', intval($TDV_RESTANTE));
        $TDV_RESTANTE_DATETIME = \DateTime::createFromFormat('Y-m-d H:i:s', $TDV_RESTANTE_DATE);
        $fecha = new \DateTime('now');

        $USUARIO->getIdCuenta()->setTdv($TDV_RESTANTE_DATETIME);
        $MOVIMIENTO = new \AppBundle\Entity\UsuarioMovimiento();
        $MOVIMIENTO->setCantidad($tdv);
        $MOVIMIENTO->setCausa($concepto);
        $MOVIMIENTO->setIdUsuario($USUARIO);
        $MOVIMIENTO->setFecha($fecha);
        $em = $doctrine->getManager();
        $em->persist($USUARIO);
        $em->persist($MOVIMIENTO);
        $em->flush();

        // Opcional para no muera un ciudadano al mejorar la nota
        //    La nota anterior se resta (en TdV) y se sustituye por la nueva
        if ($comprobar_muerte) {
            //Llamada a la función comprobar muerte
        }
    }

    /**
     * Determina si un alias está ocupado por otro ciudadano
     * @param type $doctrine
     * @param type $session
     * @param type $alias
     * @return true|false
     */
    static function aliasDisponible($doctrine, $session, $alias) {
        $em = $doctrine->getManager();
        $qb = $em->createQueryBuilder();
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($session->get("id_usuario"));

        $query = $qb->select('u.seudonimo')
                ->from('\AppBundle\Entity\Usuario', 'u')
                ->where('u.seudonimo IS NOT NULL AND u.idUsuario != :IdUsuario')
                ->setParameters(['IdUsuario' => $USUARIO->getIdUsuario()]);
        $USUARIOS_ALIAS = $query->getQuery()->getResult();
        if (count($USUARIOS_ALIAS)) {
            foreach ($USUARIOS_ALIAS as $a) {
                if ($a['seudonimo'] === $alias) {
                    return 0;
                }
            }
        }
        return 1;
    }

    /**
     * Devuelve el puesto que tiene un usuario dentro de un conjunto de usuarios
     * por su TdV
     * @param type $doctrine
     * @param type $USUARIO
     * @param type $USUARIOS
     * @return JsonResponse
     */
    static function getClasificacion($doctrine, $USUARIO, $USUARIOS) {
        if ($USUARIO->getSeudonimo() === null) {
            $RESPUESTA = [];
            $RESPUESTA['ERROR'] = 'Debes tener un alias para participar en los rankings';
            $RESPUESTA['PUESTO'] = 0;
            return $RESPUESTA;
        }
        if (!count($USUARIOS)) {
            $RESPUESTA = [];
            $RESPUESTA['ERROR'] = 'No hay usuarios';
            $RESPUESTA['PUESTO'] = 0;
            return $RESPUESTA;
            //return new JsonResponse(array('estado' => 'ERROR', 'message' => ''));
        }
        $query = $doctrine
                ->getRepository('AppBundle:UsuarioMovimiento')
                ->createQueryBuilder('u');
        $CUENTAS = [];
        $query->select('SUM(u.cantidad)');
        $query->where('u.idUsuario = :ID_USUARIO');
        //$query->andWhere ('MONTH(u.fecha) = MONTH(CURRENT_DATE())');
        $query->setParameter('ID_USUARIO', $USUARIO->getIdUsuario());
        $cantidad = $query->getQuery()->getSingleScalarResult();
        $puesto = 1;
        foreach ($USUARIOS as $U) {
            if ($U->getSeudonimo() !== null && $U !== $USUARIO) {
                $aux = [];
                $aux['UsuarioId'] = $U->getIdUsuario();
                $aux['UsuarioAlias'] = $U->getSeudonimo();

                $query->select('SUM(u.cantidad)');
                $query->where('u.idUsuario = :ID_USUARIO');
                $query->setParameter('ID_USUARIO', $U->getIdUsuario());
                $aux['UsuarioMovimientos'] = $query->getQuery()->getSingleScalarResult();
                if ($aux['UsuarioMovimientos'] !== null && $aux['UsuarioMovimientos'] > $cantidad) {
                    $puesto++;
                }
                $CUENTAS[] = $aux;
            }
        }
        $RESPUESTA = [];
        $RESPUESTA['ID'] = $USUARIO->getIdUsuario();
        $RESPUESTA['ALIAS'] = $USUARIO->getSeudonimo();
        $RESPUESTA['CANTIDAD'] = $cantidad;
        $RESPUESTA['PUESTO'] = $puesto;
        return $RESPUESTA;
        //return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $RESPUESTA)), 200);
    }

    /**
     * Devuelve el puesto que tiene un usuario dentro de un conjunto de usuarios
     * por su TdV
     * @param type $doctrine
     * @param type $USUARIO
     * @param type $USUARIOS
     * @return JsonResponse
     */
    static function getClasificacionJsonResponse($doctrine, $USUARIO, $USUARIOS) {
        $RESPUESTA = Usuario::getClasificacion($doctrine, $USUARIO, $USUARIOS);
        return new JsonResponse(json_encode(array('estado' => 'OK', 'message' => $RESPUESTA)), 200);
    }

    static function getClasificacionDistritos($doctrine) {
        $RESPUESTA = [];
        $numMaxCiudadanos = 0;
        $DISTRITOS = $doctrine->getRepository('AppBundle:UsuarioDistrito')->findAll();
        if (count($DISTRITOS)) {
            foreach ($DISTRITOS AS $DISTRITO) {
                $CIUDADANOS = Distrito::getCiudadanosVivosDistrito($doctrine, $DISTRITO);
                $contadorCiudadanos = 0;
                if (count($CIUDADANOS)) {
                    foreach ($CIUDADANOS AS $CIUDADANO) {
                        $contadorCiudadanos++;
                        if ($numMaxCiudadanos < $contadorCiudadanos) {
                            $numMaxCiudadanos = $contadorCiudadanos;
                        }
                    }
                }
            }
            foreach ($DISTRITOS AS $DISTRITO) {
                $aux = [];
                $aux['DISTRITO'] = $DISTRITO->getNombre();
                $aux['CANTIDAD'] = 0;
                $USUARIOS = Distrito::getCiudadanosVivosDistrito($doctrine, $DISTRITO);
                if (count($USUARIOS)) {
                    $contadorCiudadanos = 0;
                    foreach ($USUARIOS AS $USUARIO) {
                        $contadorCiudadanos++;
                        $query = $doctrine
                                ->getRepository('AppBundle:UsuarioMovimiento')
                                ->createQueryBuilder('um');
                        $query->select('SUM(um.cantidad)');
                        $query->where('um.idUsuario = :ID_USUARIO');
                        //$query->andWhere('MONTH(um.fecha) = MONTH(CURRENT_DATE())');
                        $query->setParameter('ID_USUARIO', $USUARIO->getIdUsuario());
                        $cant = $query->getQuery()->getSingleScalarResult();
                        if ($cant !== null) {
                            $aux['CANTIDAD'] += $cant;
                        }
                    }
                    $cantidadMedia = $aux['CANTIDAD'] / $contadorCiudadanos;
                    for ($i = $contadorCiudadanos; $i < $numMaxCiudadanos; $i++) {
                        $aux['CANTIDAD'] += $cantidadMedia;
                    }
                }
                $RESPUESTA[] = $aux;
            }
            foreach ($RESPUESTA as $clave => $fila) {
                $C[$clave] = $fila['CANTIDAD'];
                $D[$clave] = $fila['DISTRITO'];
            }
            array_multisort($C, SORT_DESC, $D, SORT_ASC, $RESPUESTA);
        }
        return $RESPUESTA;
    }

    /**
     * Retorna el alias de un usuario dado su id
     * @param type $usuario_share
     * @param type $doctrine
     * @return string|0 
     */
    static function aliasToId($usuario_share, $doctrine) {
        $id_usuario = $doctrine->getRepository('AppBundle:Usuario')->findOneBySeudonimo($usuario_share);
        return $id_usuario;
    }

    /**
     * Registra un conjunto de usuarios dada una lista de DNIs
     * @param type $doctrine
     * @param type $DNIs
     * @param type $TDV
     * @return type
     */
    static function registrarMultiple($doctrine, $DNIs, $TDV) {
        $DNIs_formato = [];
        $DNIs_formato['error'] = 0;
        $DNIs_formato['repetidos'] = [];
        $DNIs_formato['correctos'] = [];
        $DNIs_formato['incorrectos'] = [];

        $patron_dni = "/[0-9]{8}/";
        $patron_nie = "/[X,Y,Z][0-9]{7}/";

        // Eliminamos espacios y separamos formatos de DNI/NIE 
        // correctos de los incorrectos
        foreach ($DNIs as $DNI) {
            if (preg_match($patron_dni, $DNI, $coincidencia, PREG_OFFSET_CAPTURE) || preg_match($patron_nie, $DNI, $coincidencia, PREG_OFFSET_CAPTURE)) {
                if (!Usuario::registrar($doctrine, $coincidencia[0][0], $TDV)) {
                    $DNIs_formato['repetidos'][] = $coincidencia[0][0];
                } else {
                    $DNIs_formato['correctos'][] = $coincidencia[0][0];
                }
            } else {
                $DNIs_formato['error'] = 1;
                $DNIs_formato['incorrectos'][] = $DNI;
            }
        }
        return $DNIs_formato;
    }

    /**
     * Registra un usuario dado un DNI
     * @param type $doctrine
     * @param type $DNI
     * @param type $TDV
     * @return true|false
     */
    static function registrar($doctrine, $DNI, $TDV) {
        $usuario = $doctrine->getRepository('AppBundle:Usuario')->findOneByDni($DNI);
        if ($usuario !== null) {
            return 0;
        }
        $em = $doctrine->getManager();
        $FECHA = new \DateTime('now');

        $cuenta = new \AppBundle\Entity\UsuarioCuenta();
        $TDV_formato = \DateTime::createFromFormat('Y-m-d H:i:s', $TDV);
        $cuenta->setTdv($TDV_formato);
        $em->persist($cuenta);
        $em->flush();
        $usuario = new \AppBundle\Entity\Usuario();
        $usuario->setDni($DNI);
        $usuario->setIdRol($doctrine->getRepository('AppBundle:Rol')->findOneByNombre('Jugador'));
        $usuario->setCertificado('');
        $usuario->setIdEstado($doctrine->getRepository('AppBundle:UsuarioEstado')->findOneByNombre('Inactivo'));
        $usuario->setIdCuenta($cuenta);
        $usuario->setTiempoSinComer(\DateTime::createFromFormat('Y-m-d H:i:s', $FECHA->format('Y-m-d H:i:s')));
        $usuario->setTiempoSinBeber(\DateTime::createFromFormat('Y-m-d H:i:s', $FECHA->format('Y-m-d H:i:s')));
        $em->persist($usuario);
        $em->flush();

//        $usuario = $this->getDoctrine()->getRepository('AppBundle:Usuario')->findOneByDni($DNI);
//        Contraseña por defecto
        $usuario->setClave(Usuario::encriptar($usuario, $DNI));
        $em->persist($usuario);
        $em->flush();

        // No se han producido errores en el proceso
        return 1;
    }

    /**
     * Devuelve qué información le falta al usuario por medio de códigos
     * (-1) Falta alias
     * (-2) Falta nombre
     * (-3) Falta apellidos
     * (-4) Falta email
     * ( 0) Todo OK
     * @param type $USUARIO
     * @return int
     */
    static function comprobarInformacionPersonal($USUARIO) {
        if ($USUARIO->getSeudonimo() === null) {
            return -1;
        }
        if ($USUARIO->getNombre() === null) {
            return -2;
        }
        if ($USUARIO->getApellidos() === null) {
            return -3;
        }
        if ($USUARIO->getEmail() === null) {
            return -4;
        }
        return 1;
    }

    /**
     * Devuelve qué información le falta al usuario en formato JSON por mensajes
     * personalizados
     * @param type $USUARIO
     * @return JsonResponse
     */
    static function comprobarInformacionPersonalJSON($USUARIO) {
        $codigo = Usuario::comprobarInformacionPersonal($USUARIO);
        if ($codigo === -1) {
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Ciudadano! ¿Cuál es tu nombre de pila?'), 200);
        }
        if ($codigo === -2) {
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Detente! ¿Necesito saber cómo te llamas?'), 200);
        }
        if ($codigo === -3) {
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Ciudadano! ¿Cuál es tu apellido?'), 200);
        }
        if ($codigo === -4) {
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Ciudadano! Si necesito contactarte sería bueno tener tu correo personal'), 200);
        }
        return new JsonResponse(array('estado' => 'OK', 'message' => 'Hola @' . $USUARIO->getSeudonimo() . '! Adelante'), 200);
    }

    /**
     * Devuelve el alias del ciudadano. Si no tiene devuelve "desconocido"
     * @param type $USUARIO
     * @return string
     */
    static function aliasODesconocido($USUARIO) {
        $alias = $USUARIO->getSeudonimo();
        if ($alias === null) {
            $alias = 'desconocido';
        }
        return $alias;
    }

    /**
     * Establece el estado Fallecido para un ciudadano específico
     * @param type $doctrine
     * @param type $USUARIO
     * @return int 1|0
     */
    static function setDefuncion($doctrine, $USUARIO) {
        $em = $doctrine->getManager();
        $estadoFallecido = $doctrine->getRepository('AppBundle:UsuarioEstado')->findOneByNombre('Fallecido');
        if ($estadoFallecido === null) {
            Utils::setError($doctrine, 1, 'No se encuentra en la tabla USUARIO_ESTADO el estado con nombre Fallecido');
            return 0;
        }
        if ($USUARIO->getIdEstado() !== $estadoFallecido) {
            $USUARIO->setIdEstado($estadoFallecido);
            $em->persist($USUARIO);
            $em->flush();
            Usuario::mensajeSistemaDefuncion($doctrine, $USUARIO);
        }
        Usuario::dejarHerencia($doctrine, $USUARIO);
        return 1;
    }

    /**
     * Obtiene los ciudadanos con estado distinto de fallecido e inactivo
     * @param type $doctrine
     * @return array
     */
    static function getCiudadanosVivos($doctrine) {
        $ESTADO_FALLECIDO = $doctrine->getRepository('AppBundle:UsuarioEstado')->findOneByNombre('Fallecido');
        $ESTADO_INACTIVO = $doctrine->getRepository('AppBundle:UsuarioEstado')->findOneByNombre('Inactivo');
        $ROL_CIUDADANO = $doctrine->getRepository('AppBundle:Rol')->findOneByNombre('Jugador');
        $query = $doctrine->getRepository('AppBundle:Usuario')->createQueryBuilder('a');
        $query->select('a');
        $query->where('a.idEstado != :ESTADO_F AND a.idEstado != :ESTADO_I AND a.idRol = :ROL');
        $query->setParameters(['ESTADO_F' => $ESTADO_FALLECIDO, 'ESTADO_I' => $ESTADO_INACTIVO, 'ROL' => $ROL_CIUDADANO]);
        $CIUDADANOS = $query->getQuery()->getResult();

        return $CIUDADANOS;
    }

    /**
     * Obtiene los ciudadanos con estado vivo
     * @param type $doctrine
     * @return array
     */
    static function getCiudadanosActivos($doctrine) {
        $ESTADO_ACTIVO = $doctrine->getRepository('AppBundle:UsuarioEstado')->findOneByNombre('Activo');
        $ROL_CIUDADANO = $doctrine->getRepository('AppBundle:Rol')->findOneByNombre('Jugador');
        $CIUDADANOS = $doctrine->getRepository('AppBundle:Usuario')->findBy([
            'idRol' => $ROL_CIUDADANO, 'idEstado' => $ESTADO_ACTIVO
        ]);

        return $CIUDADANOS;
    }

    /**
     * Obtiene los ciudadanos en vacaciones
     * @param type $doctrine
     * @return array
     */
    static function getCiudadanosVacaciones($doctrine) {
        $ESTADO_ACTIVO = $doctrine->getRepository('AppBundle:UsuarioEstado')->findOneByNombre('Vacaciones');
        $ROL_CIUDADANO = $doctrine->getRepository('AppBundle:Rol')->findOneByNombre('Jugador');
        $CIUDADANOS = $doctrine->getRepository('AppBundle:Usuario')->findBy([
            'idRol' => $ROL_CIUDADANO, 'idEstado' => $ESTADO_ACTIVO
        ]);

        return $CIUDADANOS;
    }

    /**
     * Obtiene todos los usuario que no sean el sistema
     * @param type $doctrine
     * @return array
     */
    static function getUsuariosMenosSistema($doctrine) {
        $SISTEMA = $doctrine->getRepository('AppBundle:Rol')->findOneByNombre('Sistema');
        $em = $doctrine->getManager();
        $qb = $em->createQueryBuilder();
        $query = $qb->select('u')
                ->from('\AppBundle\Entity\Usuario', 'u')
                ->where('u.idRol != :idRol')
                ->setParameters(array('idRol' => $SISTEMA));
        $USUARIOS = $query->getQuery()->getResult();
        return $USUARIOS;
    }

    /**
     * Nos dice el número de mensajes por ver en un chat entre dos usuarios específicos
     * @param type $doctrine
     * @param type $YO
     * @param type $USUARIO_CHAT
     * @return int
     */
    static function numeroMensajesChat($doctrine, $YO, $USUARIO_CHAT, $gueto = false) {
        if (!$gueto) {
            $em = $doctrine->getManager();
            $qb = $em->createQueryBuilder();
            $query = $qb->select('c1')
                    ->from('\AppBundle\Entity\Chat', 'c1')
                    ->where('c1.idUsuario1 = :emisor AND c1.idUsuario2 = :receptor_usuario')
                    ->orWhere('c1.idUsuario1 = :receptor_usuario AND c1.idUsuario2 = :emisor')
                    ->orderBy('c1.fecha', 'ASC')
                    ->setParameters(array('emisor' => $YO, 'receptor_usuario' => $USUARIO_CHAT));
            $CHAT = $query->getQuery()->getOneOrNullResult();
        } else {
            $CHAT = $doctrine->getRepository('AppBundle:Chat')->findOneBy([
                'idUsuario1' => null, 'idUsuario2' => null
            ]);
        }
        if ($CHAT !== null) {
            $MENSAJES_SIN_VER = $doctrine->getRepository('AppBundle:ChatSinVer')->findOneBy([
                'idUsuario' => $YO, 'idChat' => $CHAT
            ]);
            if (null === $MENSAJES_SIN_VER) {
                return 0;
            }
            return $MENSAJES_SIN_VER->getCantidad();
//            $query = $qb->select('cm')
//                    ->from('\AppBundle\Entity\ChatMensajes', 'cm')
//                    ->where('cm.idChat = :idChat AND cm.visto = 0')
//                    ->setParameters(array('idChat' => $CHAT));
//            $n_mensajes = $query->getQuery()->getResult();
//            return count($n_mensajes);
        }
        return 0;
    }

    /**
     * Esta función despoja al Usuario de sus deudas pendientes y las pone (de 
     * manera repartida) en herencia a cada uno de los miembros vivos de su
     * distrito
     * @param type $doctrine
     * @param type $USUARIO
     */
    static function dejarHerencia($doctrine, $USUARIO) {
        $em = $doctrine->getManager();
        $DISTRITO = $USUARIO->getIdDistrito();
        if (null !== $DISTRITO) {
            $DEUDAS = $doctrine->getRepository('AppBundle:UsuarioPrestamo')->findBy([
                'idUsuario' => $USUARIO,
                'motivo' => 'prestamo'
            ]);
            if (count($DEUDAS)) {
                $deudaTotal = 0;
                $interes_minimo = 10;
                foreach ($DEUDAS as $DEUDA) {
                    $deudaTotal += $DEUDA->getRestante();
                    if ($DEUDA->getInteres() < $interes_minimo) {
                        $interes_minimo = $DEUDA->getInteres();
                    }
                    $DEUDA->setRestante(0);
                    $em->persist($DEUDA);
                }
                // Si hay deuda
                if ($deudaTotal > 0) {
                    Usuario::operacionSobreTdV($doctrine, $USUARIO, 0, 'Ha dejado en herencia ' . $deudaTotal . ' s de deuda a los miembros de su distrito');
                    $COMPANEROS = Distrito::getCiudadanosVivosDistrito($doctrine, $DISTRITO);
                    $n_companeros = count($COMPANEROS);
                    if ($n_companeros) {
                        $deudaIndividual = $deudaTotal / $n_companeros;
                        foreach ($COMPANEROS as $COMPANERO) {
                            $DEUDAHEREDADA = new \AppBundle\Entity\UsuarioPrestamo();
                            $DEUDAHEREDADA->setCantidad($deudaIndividual);
                            $DEUDAHEREDADA->setRestante($deudaIndividual);
                            $DEUDAHEREDADA->setFecha(new \DateTime('now'));
                            $DEUDAHEREDADA->setIdUsuario($COMPANERO);
                            $DEUDAHEREDADA->setInteres($interes_minimo);
                            $DEUDAHEREDADA->setMotivo('prestamo');
                            $em->persist($DEUDAHEREDADA);
                        }
                    }
                }
            }
        }
        $em->flush();
    }

    /**
     * Comprueba si un usuario está de Vacaciones
     * @param type $doctrine
     * @param type $USUARIO
     * @return int
     */
    static function estaDeVacaciones($doctrine, $USUARIO) {
        $VACACIONES = $doctrine->getRepository('AppBundle:UsuarioEstado')->findOneByNombre('Vacaciones');
        if ($USUARIO->getIdEstado() === $VACACIONES) {
            return 1;
        }
        return 0;
    }

    /**
     * Comprueba si un usuario tiene opción de ganar un punto de experiencia, si
     * tiene opción le ingresa el punto de experiencia
     * @param type $doctrine
     * @param type $USUARIO
     * @return int
     */
    static function comprobarNivel($doctrine, $USUARIO) {
        $balon = Usuario::comprobarSiBalon($doctrine, $USUARIO);
        $deporte = Usuario::comprobarDeporte($doctrine, $USUARIO);
        $inspeccion = Usuario::comprobarInspeccion($doctrine, $USUARIO);

        return array('BALON' => $balon, 'DEPORTE' => $deporte, 'INSPECCION' => $inspeccion);
        if ($balon && $deporte && $inspeccion) {
            Usuario::subirNivel($doctrine, $USUARIO);
            return 1;
        }
        return 0;
    }

    /**
     * Comprueba si un usuario ha obtenido una calificación igual o superior a 
     * un balón durante la semana
     * @param type $doctrine
     * @param type $USUARIO
     * @return int
     */
    static function comprobarSiBalon($doctrine, $USUARIO) {
        $CALIFICACIONES_VALIDAS = [];
        $CALIFICACIONES_VALIDAS[] = $doctrine->getRepository('AppBundle:Calificaciones')->findOneByIdCalificaciones(3);
        $CALIFICACIONES_VALIDAS[] = $doctrine->getRepository('AppBundle:Calificaciones')->findOneByIdCalificaciones(2);
        $CALIFICACIONES_VALIDAS[] = $doctrine->getRepository('AppBundle:Calificaciones')->findOneByIdCalificaciones(1);
        $CALIFICACIONES = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findByIdUsuario($USUARIO);
        $HOY = new \DateTime('now');
        if (count($CALIFICACIONES)) {
            foreach ($CALIFICACIONES as $CALIFICACION) {
                if (in_array($CALIFICACION->getIdCalificaciones(), $CALIFICACIONES_VALIDAS)) {
                    if (intval($CALIFICACION->getFecha()->format('W')) === intval($HOY->format('W') - 1)) {
                        return 1;
                    }
                }
            }
        }
        return 0;
    }

    /**
     * Comprueba si ha superado un reto deportivo esa semana
     * @param type $doctrine
     * @param type $USUARIO
     * @return int
     */
    static function comprobarDeporte($doctrine, $USUARIO) {
        $SECCION_DEPORTE = $doctrine->getRepository('AppBundle:EjercicioSeccion')->findOneBySeccion('deporte');
        $EJERCICIOS = $doctrine->getRepository('AppBundle:Ejercicio')->findByIdEjercicioSeccion($SECCION_DEPORTE);
        $HOY = new \DateTime('now');
        if (count($EJERCICIOS)) {
            foreach ($EJERCICIOS as $EJERCICIO) {
                $CALIFICACIONES = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findBy([
                    'idUsuario' => $USUARIO, 'idEjercicio' => $EJERCICIO
                ]);
                if (count($CALIFICACIONES)) {
                    foreach ($CALIFICACIONES as $CALIFICACION) {
                        if (intval($CALIFICACION->getFecha()->format('W')) === intval($HOY->format('W'))) {
                            return 1;
                        }
                    }
                }
            }
        }
        return 0;
    }

    /**
     * Comprueba si ha superado la última inspección de trabajo
     * @param type $doctrine
     * @param type $USUARIO
     * @return int
     */
    static function comprobarInspeccion($doctrine, $USUARIO) {
        $em = $doctrine->getManager();
        $qb = $em->createQueryBuilder();
        $ESTADO_EVALUADO = $doctrine->getRepository('AppBundle:EjercicioEstado')->findOneByEstado('evaluado');
        $query = $qb->select('a')
                ->from('\AppBundle\Entity\EjercicioCalificacion', 'a')
                ->where('a.idUsuario = :USUARIO')
                ->orderBy('a.fecha', 'DESC')
                ->setParameters(array('USUARIO' => $USUARIO));
        $ULT_INSPECCION = $query->getQuery()->getResult();
        if (count($ULT_INSPECCION)) {
            foreach ($ULT_INSPECCION as $CALIFICACION) {
                if (
                        $CALIFICACION->getIdEjercicio()->getIdEjercicioSeccion()->getSeccion() === 'inspeccion_trabajo' &&
                        $CALIFICACION->getIdEjercicioEstado() === $ESTADO_EVALUADO
                ) {
                    return 1;
                }
            }
        }
        return 0;
    }

    /**
     * Comprueba si un usuario ha desactivado la última mina
     * @param type $doctrine
     * @param type $USUARIO
     * @return int
     */
    static function comprobarMinaDesactivada($doctrine, $USUARIO) {
        $ULTIMA_MINA = Utils::ultimaMinaDesactivada($doctrine);
        if (null === $ULTIMA_MINA) {
            return 1;
        }
        $MINA_DESACTIVADA = $doctrine->getRepository('AppBundle:UsuarioMina')->findOneBy([
            'idUsuario' => $USUARIO, 'idMina' => $ULTIMA_MINA
        ]);
        if (null === $MINA_DESACTIVADA) {
            return 0;
        }
        return 1;
    }

    /**
     * Suma un nivel y un punto de experiencia a un usuario, si no tenía una 
     * entrada en la tabla USUARIO_NIVEL se le crea con nivel 1 y un punto de
     * experiencia
     * @param type $doctrine
     * @param type $USUARIO
     */
    static function subirNivel($doctrine, $USUARIO) {
        $USUARIO_NIVEL = $doctrine->getRepository('AppBundle:UsuarioNivel')->findOneByIdUsuario($USUARIO);
        if (null === $USUARIO_NIVEL) {
            $USUARIO_NIVEL = new \AppBundle\Entity\UsuarioNivel();
            $USUARIO_NIVEL->setIdUsuario($USUARIO);
            $USUARIO_NIVEL->setNivel(1);
            $USUARIO_NIVEL->setPuntos(1);
        } else {
            $USUARIO_NIVEL->setNivel($USUARIO_NIVEL->getNivel() + 1);
            $nuevos_xp = $USUARIO_NIVEL->getNivel() + $USUARIO_NIVEL->getPuntos();
            $USUARIO_NIVEL->setPuntos($nuevos_xp);
        }
        $em = $doctrine->getManager();
        $em->persist($USUARIO_NIVEL);
        $em->flush();
    }

    /**
     * Incrementa un aviso de chat sin ver en un chat determinado
     * @param type $doctrine
     * @param type $USUARIO
     * @param type $CHAT
     */
    static function setChatSinVer($doctrine, $USUARIO, $CHAT) {
        $em = $doctrine->getManager();
        $CHAT_SIN_VER = $doctrine->getRepository('AppBundle:ChatSinVer')->findOneBy([
            'idUsuario' => $USUARIO, 'idChat' => $CHAT
        ]);
        if (null !== $CHAT_SIN_VER) {
            $CHAT_SIN_VER->setCantidad($CHAT_SIN_VER->getCantidad() + 1);
        } else {
            $CHAT_SIN_VER = new \AppBundle\Entity\ChatSinVer();
            $CHAT_SIN_VER->setIdChat($CHAT);
            $CHAT_SIN_VER->setIdUsuario($USUARIO);
            $CHAT_SIN_VER->setCantidad(1);
        }
        $em->persist($CHAT_SIN_VER);
        $em->flush();
    }

    static function mensajeSistemaDefuncion($doctrine, $USUARIO) {
        $ROL_GDT = $doctrine->getRepository('AppBundle:Rol')->findOneByNombre('Guardián');
        $SISTEMA = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdRol($ROL_GDT);
        $CHAT_COMUN = $doctrine->getRepository('AppBundle:Chat')->findOneByIdChat(1);
        
        $mensajesDeDuelo = [
            'El duelo es un proceso, no un estado. <br><span style="float: right;"><b><i>Anne</i></b></span>',
            'El que encubre su dolor no encuentra remedio para él. <br><span style="float: right;"><b><i>Proverbio turco</i></b></span>',
            'El dolor compartido es dolor disminuido. <br><span style="float: right;"><b><i>Rabbi Grollman</i></b></span>',
            'Todo crece con el tiempo, excepto el duelo. <br><span style="float: right;"><b><i>Proverbio</i></b></span>',
            'El tiempo se lleva el dolor del hombre.',
            'La palabra "FELICIDAD" perdería sentido sin la tristeza. <br><span style="float: right;"><b><i>Carl Gustav Jung</i></b></span>',
            'El tiempo es un médico que sana todo duelo. <br><span style="float: right;"><b><i>Dífilo</i></b></span>',
            'No diré "no llores" porque no todas las lágrimas son malas. <br><span style="float: right;"><b><i>J.R.R. Tolkien</i></b></span>'
        ];
        $mensajeDeDuelo = $mensajesDeDuelo[array_rand($mensajesDeDuelo)];
        $MENSAJE = new \AppBundle\Entity\ChatMensajes();
        $MENSAJE->setFecha(new \DateTime('now'));
        $MENSAJE->setIdChat($CHAT_COMUN);
        $MENSAJE->setIdUsuario($SISTEMA);
        $MENSAJE->setMensaje('<center>RIP <b>@' . $USUARIO->getSeudonimo() . '</b></center><br>' . $mensajeDeDuelo . '<br>');
        $MENSAJE->setVisto(0);
        $em = $doctrine->getManager();
        $em->persist($MENSAJE);
        $em->flush();

        $CIUDADANOS = $doctrine->getRepository('AppBundle:Usuario')->findAll();
        if (count($CIUDADANOS)) {
            foreach ($CIUDADANOS as $CIUDADANO) {
                Usuario::setChatSinVer($doctrine, $CIUDADANO, $CHAT_COMUN);
            }
        }
    }

    /**
     * Lanza un mensaje con aviso "te quedan menos de dos días" para el usuario
     * especificado si no lo ha mostrado en el día.
     * @param type $doctrine
     * @param type $CIUDADANO
     */
    static function mensajeTeQuedanDosDias($doctrine, $CIUDADANO) {
        $texto = 'Te quedan menos de 2 días de vida, sería recomendable que pidieras un préstamo o lograras una donación.'
                . ' Sentiría mucho tu pérdida.';
        $MENSAJE_DIRECTO = $doctrine->getRepository('AppBundle:TipoMensaje')->findOneByTipo('directo');
        $mensajeDosDias = $doctrine->getRepository('AppBundle:Mensaje')->findOneBy([
            'idTipoMensaje' => $MENSAJE_DIRECTO, 'mensaje' => $texto
        ]);
        $hoy = new \DateTime('now');
        if (null !== $mensajeDosDias) {
            $mensajesDosDiasCiudadano = $doctrine->getRepository('AppBundle:MensajeXUsuario')->findBy([
                'idUsuario' => $CIUDADANO, 'idMensaje' => $mensajeDosDias
            ]);
            if (count($mensajesDosDiasCiudadano)) {
                $mensajeDeHoy = false;
                foreach ($mensajesDosDiasCiudadano as $msj) {
                    if (!$mensajeDeHoy) {
                        if ($msj->getFecha()->format('d') === $hoy->format('d')) {
                            $mensajeDeHoy = true;
                        }
                    }
                }
                if (!$mensajeDeHoy) {
                    Usuario::mensajeGuardianACiudadano($doctrine, $CIUDADANO, $texto, $mensajeDosDias);
                }
            }
        } else {
            Usuario::mensajeGuardianACiudadano($doctrine, $CIUDADANO, $texto);
        }
    }

    static function mensajeGuardianACiudadano($doctrine, $CIUDADANO, $texto, $MENSAJE = null) {
        $em = $doctrine->getManager();
        $ROL_GDT = $doctrine->getRepository('AppBundle:Rol')->findOneByNombre('Guardián');
        $GDT = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdRol($ROL_GDT);

        $MENSAJE_DIRECTO = $doctrine->getRepository('AppBundle:TipoMensaje')->findOneByTipo('directo');
        if (null === $MENSAJE) {
            $MENSAJE = new \AppBundle\Entity\Mensaje();
        }
        $MENSAJE->setFecha(new \DateTime('now'));
        $MENSAJE->setIdTipoMensaje($MENSAJE_DIRECTO);
        $MENSAJE->setTitulo('Advertencia');
        $MENSAJE->setMensaje($texto);
        $em->persist($MENSAJE);

        $MENSAJE_X_CIUDADANO = new \AppBundle\Entity\MensajeXUsuario();
        $MENSAJE_X_CIUDADANO->setIdMensaje($MENSAJE);
        $MENSAJE_X_CIUDADANO->setIdUsuario($CIUDADANO);
        $MENSAJE_X_CIUDADANO->setVisto(0);
        $em->persist($MENSAJE_X_CIUDADANO);

        $em->flush();
    }

}
