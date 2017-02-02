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

        $em = $doctrine->getManager();
        $fecha = new \DateTime('now');
        $log = new \AppBundle\Entity\LogUser();
        $log->setIdUsuario($usuario);
        $log->setAction($route);
        $log->setFecha($fecha);
        $em->persist($log);
        $em->flush();

        // Si se pide acceso admin
        if ($admin) {
            // Si el usuario no es administrador se le envía fuera y se almacena el intento de acceso en el log
            if ($usuario->getIdRol()->getIdRol() !== 2) {
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
     * @param type $session
     * @param int $tdv TdV que se le quiere cobrar al usuario
     * @return int 0 si no tiene TdV suficiente, 1 en cualquier otro caso
     */
    static function puedoRealizarTransaccion($doctrine, $session, $tdv) {
        $id_usuario = $session->get('id_usuario');
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($id_usuario);
        $TDV_USUARIO = $USUARIO->getIdCuenta()->getTdv()->getTimestamp();
        $TDV_RESTANTE = $TDV_USUARIO - $tdv;
        $TDV_RESTANTE_DATE = date('Y-m-d H:i:s', intval($TDV_RESTANTE));
        $TDV_RESTANTE_DATETIME = \DateTime::createFromFormat('Y-m-d H:i:s', $TDV_RESTANTE_DATE);
        $HOY = new \DateTime('now');
        if ($TDV_RESTANTE_DATETIME <= $HOY) {
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
    static function heDonadoYa($doctrine, $session, $usuario_destino) {
        $id_usuario = $session->get('id_usuario');
        $USUARIO = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($id_usuario);
        $em = $doctrine->getManager();
        $qb = $em->createQueryBuilder();
        $query = $qb->select('d')
                ->from('\AppBundle\Entity\UsuarioMovimiento', 'd')
                ->where('d.idUsuario = :Usuario AND d.causa = :Causa')
                ->setParameters(['Usuario' => $USUARIO, 'Causa' => 'Cobro - Donación a @' . $usuario_destino->getSeudonimo()]);
        $DONACION = $query->getQuery()->getResult();
        if (count($DONACION)) {
            return 1;
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
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'Debes tener un alias para participar'
                . 'en los rankings'));
        }
        if (!count($USUARIOS)) {
            return new JsonResponse(array('estado' => 'ERROR', 'message' => 'No hay usuarios'));
        }
        $query = $doctrine
                ->getRepository('AppBundle:UsuarioMovimiento')
                ->createQueryBuilder('u');
        $CUENTAS = [];
        $query->select('SUM(u.cantidad)');
        $query->where('u.idUsuario = :ID_USUARIO');
        $query->setParameter('ID_USUARIO', $USUARIO->getIdUsuario());
        $cantidad = $query->getQuery()->getSingleScalarResult();
        $puesto = 1;
        foreach ($USUARIOS as $U) {
            if ($U->getSeudonimo() !== null) {
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
        return new JsonResponse(array('estado' => 'OK', 'message' => $RESPUESTA));
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

        $patron_dni = "/[0-9]{8}[A-Z]/";
        $patron_nie = "/[X,Y,Z][0-9]{7}[A-Z]/";

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
        $usuario->setTiempoSinComer(\DateTime::createFromFormat('Y-m-d H:i:s', $FECHA));
        $usuario->setTiempoSinComerDistrito(\DateTime::createFromFormat('Y-m-d H:i:s', $FECHA));
        $usuario->setTiempoSinBeber(\DateTime::createFromFormat('Y-m-d H:i:s', $FECHA));
        $usuario->setTiempoSinBeberDistrito(\DateTime::createFromFormat('Y-m-d H:i:s', $FECHA));
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
        }
        return 1;
    }
    
    /**
     * Obtiene los ciudadanos vivos
     * @param type $doctrine
     * @return null|array
     */
    static function getCiudadanosVivos($doctrine){
        $ESTADO_ACTIVO = $doctrine->getRepository('AppBundle:UsuarioEstado')->findOneByNombre('Activo');
        $ROL_CIUDADANO = $doctrine->getRepository('AppBundle:Rol')->findOneByNombre('Jugador');
        $CIUDADANOS = $doctrine->getRepository('AppBundle:Usuario')->findBy([
            'idRol' => $ROL_CIUDADANO, 'idEstado' => $ESTADO_ACTIVO
        ]);
        
        return $CIUDADANOS;
    }

}
