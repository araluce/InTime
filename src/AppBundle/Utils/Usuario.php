<?php

namespace AppBundle\Utils;

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

    public function registrar_multiple($DNIs) {
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
                // var_dump($coincidencia[0]);
                if (!$this->registrar($coincidencia[0][0])) {
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

    public function registrar($DNI) {
        $usuario = $this->getDoctrine()->getRepository('AppBundle:Usuario')->findOneByDni($DNI);

        if ($usuario) {
            return 0;
        }
        $em = $this->getDoctrine()->getManager();

        $usuario = new \AppBundle\Entity\Usuario();
        $usuario->setDni($DNI);
        $usuario->setIdRol($this->getDoctrine()->getRepository('AppBundle:Rol')->findOneByNombre('Jugador'));
        $usuario->setCertificado('');
        $usuario->setIdEstado($this->getDoctrine()->getRepository('AppBundle:Estado')->findOneByNombre('Inactivo'));

        $em->persist($usuario);
        $em->flush();

        $usuario = $this->getDoctrine()->getRepository('AppBundle:Usuario')->findOneByDni($DNI);
        //Contraseña por defecto

        $usuario->setClave(\AppBundle\Controller\Usuario::encriptar($usuario, $DNI));
        $em->persist($usuario);
        $em->flush();

        $cuenta = new \AppBundle\Entity\Cuenta();
        $fecha_inicio = new \DateTime('now');
        var_dump($fecha_inicio);

        // No se han producido errores en el proceso
        return 1;
    }

    public function encriptar($usuario, $password) {
        $salt = $usuario->getIdUsuario();
        return sha1($salt . $password);
    }

    public function activar_usuario($doctrine, $USUARIO) {
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
    
    static function addTdV($doctrine, $USUARIO, $timestamp, $causa) {
        if($USUARIO === null){
            return 0;
        }
        $em = $doctrine->getManager();
        $TdV_u = $USUARIO->getIdCuenta()->getTdv()->getTimestamp();
        $TdV = $TdV_u;
        $TdV += $timestamp;
        $HOY = new \DateTime('now');
        //$TDV_TIMESTAMP = $HOY->getTimestamp() + $TdV;
        $TDV_FORMATO = date('Y-m-d H:i:s', intval($TdV));
        $CUENTA = $USUARIO->getIdCuenta();
        $CREATE_FORMAT = \DateTime::createFromFormat('Y-m-d H:i:s', $TDV_FORMATO);
//        \AppBundle\Utils\Utils::pretty_print($CREATE_FORMAT);
        $CUENTA->setTdv($CREATE_FORMAT);
        //\AppBundle\Utils\Utils::pretty_print($CREATE_FORMAT);
        $em->persist($CUENTA);
        $USUARIO_MOVIMIENTO = new \AppBundle\Entity\UsuarioMovimiento();
        $USUARIO_MOVIMIENTO->setIdUsuario($USUARIO);
        $USUARIO_MOVIMIENTO->setCantidad($timestamp);
        $USUARIO_MOVIMIENTO->setCausa($causa);
        $USUARIO_MOVIMIENTO->setFecha($HOY);
        $em->persist($USUARIO_MOVIMIENTO);
        $em->flush();
        
        return 1;
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

}
