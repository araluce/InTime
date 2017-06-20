<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Utils;

use AppBundle\Utils\Usuario;
use AppBundle\Utils\Utils;

/**
 * Clase que contiene métodos útiles que consume la app
 *
 * @author araluce
 */
class Utils {

    /**
     * Imprime cualquier tipo de variable para debugging
     * @param type $var
     */
    static function pretty_print($var) {
        print '<pre style="font-weight: bold;">';
        print_r($var);
        print '</pre>';
    }

    /**
     * Aplica una calificación a un ejercicio realizado por un ciudadano y le 
     * ingresa la correspondiente bonificación
     * 
     * @param type $doctrine
     * @param int $id_usuario id del ciudadano
     * @param int $id_ejercicio id del ejercicio a calificar
     * @param Entity/Calificaciones $CALIFICACION Objeto calificacion
     * @return array
     */
    static function setNota($doctrine, $id_usuario, $id_ejercicio, $CALIFICACION) {
        $SECCION = $id_ejercicio->getIdEjercicioSeccion()->getSeccion();
        $ROL_SISTEMA = $doctrine->getRepository('AppBundle:Rol')->findOneByNombre('Sistema');
        $SISTEMA = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdRol($ROL_SISTEMA);
        $EJERCICIO_CALIFICACION = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findOneBy([
            'idUsuario' => $id_usuario, 'idEjercicio' => $id_ejercicio
        ]);

        $EJERCICIO_CALIFICACION->setIdUsuario($id_usuario);
        $EJERCICIO_CALIFICACION->setIdEjercicio($id_ejercicio);
        $concepto = 'Ingreso - Pago por defecto temporal por entrega (id: ' . $id_ejercicio->getIdEjercicio() . ')';
        $EJERCICIO_BONIFICACION = $doctrine->getRepository('AppBundle:EjercicioBonificacion')->findOneBy([
            'idEjercicio' => $id_ejercicio, 'idCalificacion' => $CALIFICACION
        ]);
        if ($EJERCICIO_BONIFICACION !== null) {
            $TdVDefecto = $EJERCICIO_BONIFICACION->getBonificacion();
            Usuario::operacionSobreTdV($doctrine, $id_usuario, $TdVDefecto, $concepto);
        }
        $EJERCICIO_CALIFICACION->setIdEvaluador($SISTEMA);
        $EJERCICIO_CALIFICACION->setFecha(new \DateTime('now'));
        $EJERCICIO_ESTADO = null;
        if ($SECCION === 'inspeccion_trabajo') {
            $EJERCICIO_ESTADO = $doctrine->getRepository('AppBundle:EjercicioEstado')->findOneByEstado('evaluado');
        }
        if ($SECCION === 'paga_extra' || $SECCION === 'comida' || $SECCION === 'bebida') {
            $EJERCICIO_ESTADO = $doctrine->getRepository('AppBundle:EjercicioEstado')->findOneByEstado('entregado');
        }
        $EJERCICIO_CALIFICACION->setIdEjercicioEstado($EJERCICIO_ESTADO);
        $doctrine->getManager()->persist($EJERCICIO_CALIFICACION);
        $doctrine->getManager()->flush();

        if ($SECCION === 'comida' || $SECCION === 'bebida') {
            Alimentacion::setTSC_TSB($doctrine, $id_usuario, $SECCION);
        }

        $DATOS = [];
        $DATOS['TITULO'] = 'Resultados';
        $DATOS['NOTA_TEXTO'] = $CALIFICACION->getCorrespondenciaTexto();
        $DATOS['NOTA_ICONO'] = $CALIFICACION->getCorrespondenciaIcono();
        $DATOS['NOTA_ID'] = $CALIFICACION->getIdCalificaciones();
        return $DATOS;
    }

    /**
     * Función que marca una actividad como vista por el usuario
     * 
     * @param type $doctrine
     * @param Entity $id_usuario 
     * @param Entity $id_ejercicio null si hacemos referencia a un grupo
     * @param Entity $id_grupo null si hacemos referencia a un ejercicio
     * @return int 0 si el ejercicio no existe para el usuario, 1 para éxito
     */
    static function setVisto($doctrine, $id_usuario, $id_ejercicio, $id_grupo) {
        if ($id_grupo === null) {
            $EJERCICIO_X_USUARIO = $doctrine->getRepository('AppBundle:EjercicioXUsuario')->findOneBy([
                'idEjercicio' => $id_ejercicio, 'idUsu' => $id_usuario
            ]);
        } else {
            $EJERCICIO_X_USUARIO = $doctrine->getRepository('AppBundle:EjercicioXUsuario')->findOneBy([
                'idGrupo' => $id_grupo, 'idUsu' => $id_usuario
            ]);
        }

        if ($EJERCICIO_X_USUARIO === null) {
            return 0;
        }
        $em = $doctrine->getManager();
        $EJERCICIO_X_USUARIO->setVisto(1);
        $em->persist($EJERCICIO_X_USUARIO);
        $em->flush();
        return 1;
    }

    /**
     * Retorna el número de ciudadanos que han solicitado ese reto
     * @param type $doctrine
     * @param Entity $id_ejercicio 
     * @return int número de alumnos que han solicitado realizar el ejercicio
     */
    static function comprueba_numero_solicitudes($doctrine, $id_ejercicio) {
        $SOLICITANTES = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findByIdEjercicio($id_ejercicio);

        return count($SOLICITANTES);
    }

    /**
     * Retorna los ejercicios solicitados por un usuario en una sección concreta
     * @param type $doctrine
     * @param <AppBundle/Entity/Usuario> $USUARIO
     * @param string $SECCION_SOLICITADA Sección a evaluar
     * @return 0 || array de ejercicios solicitados
     */
    static function ejercicios_solicitados_en($doctrine, $USUARIO, $SECCION_SOLICITADA) {
        $SECCION = $doctrine->getRepository('AppBundle:EjercicioSeccion')->findOneBySeccion($SECCION_SOLICITADA);
        $ESTADO_SOLICITADO = $doctrine->getRepository('AppBundle:EjercicioEstado')->findOneByEstado('solicitado');
        $EJERCICIOS_SECCION = $doctrine->getRepository('AppBundle:Ejercicio')->findByIdEjercicioSeccion($SECCION);
        $EJERCICIOS_SOLICITADOS = [];
        $CALIFICACIONES_SOLICITADAS = $doctrine->getRepository('AppBundle:EjercicioCalificacion')->findBy([
            'idUsuario' => $USUARIO, 'idEjercicioEstado' => $ESTADO_SOLICITADO
        ]);
        if (count($CALIFICACIONES_SOLICITADAS)) {
            foreach ($CALIFICACIONES_SOLICITADAS as $EJERCICIO) {
                if (in_array($EJERCICIO->getIdEjercicio(), $EJERCICIOS_SECCION)) {
                    $EJERCICIOS_SOLICITADOS[] = $EJERCICIO->getIdEjercicio();
                }
            }
            return $EJERCICIOS_SOLICITADOS;
        } else {
            return 0;
        }
    }

    /**
     * Retorna el valor de una variable constante
     * @param type $doctrine
     * @param string $constante constante a buscar
     * @return string|int
     */
    static function getConstante($doctrine, $constante) {
        $C = $doctrine->getRepository('AppBundle:Constante')->findOneByClave($constante);
        if ($C !== null) {
            $VALOR = $C->getValor();
            if ($VALOR === 0) {
                Utils::setError($doctrine, 0, 'Constante ' . $constante . ' tiene valor 0');
                return "0";
            }
            return $VALOR;
        }
        Utils::setError($doctrine, 1, 'No se encuentra la constante ' . $constante);
        return 0;
    }

    /**
     * Registra un mal funcionamiento en la base de datos
     * @param type $doctrine
     * @param int $nivel 0 = Warning, 1 = Error, 2 = Cron
     * @param string $accion La acción que ha producido el error
     * @param Entity $usuario Usuario involucrado si lo hubiera
     */
    static function setError($doctrine, $nivel, $accion, $usuario = null) {
        $em = $doctrine->getManager();
        $REPORT = new \AppBundle\Entity\LogError();
        switch ($nivel) {
            case '0':
                $REPORT->setNivel('Warning');
                break;
            case '1':
                $REPORT->setNivel('Error');
                break;
            case '3':
                $REPORT->setNivel('Cron');
        }
        if ($usuario !== null) {
            $REPORT->setIdUsuario($usuario);
        }
        $REPORT->setAccion($accion);
        $REPORT->setFecha(new \DateTime('now'));
        $em->persist($REPORT);
        $em->flush();
    }

    /**
     * Convierte un número de segundos a días, horas, minutos y segundos
     * @param int $segundos segundos
     * @return string Días Horas Minutos Segundos
     */
    static function segundosToDias($segundos) {
        if ($segundos < 0) {
            $segundos *= -1;
        }

        $aux['dias'] = floor($segundos / 86400);
        $aux['horas'] = floor($segundos / 3600) - ($aux['dias'] * 24);
        $aux['minutos'] = floor($segundos / 60) - ($aux['dias'] * 24 * 60) - ($aux['horas'] * 60);
        $aux['segundos'] = floor($segundos) - ($aux['dias'] * 24 * 60 * 60) - ($aux['horas'] * 60 * 60) - ($aux['minutos'] * 60);
        return $aux;
    }

    /**
     * True si la fecha es de esta semana, false en otro caso
     * @param DateTime $fecha
     * @return true|false
     */
    static function estaSemana($fecha) {
        $semana = new \DateTime('now');
        if ($semana->format("W") === $fecha->format("W") &&
                $semana->format("Y") === $fecha->format("Y")) {
            return true;
        }
        return false;
    }

    /**
     * True si la fecha es de esta semana, false en otro caso
     * @param DateTime $fecha
     * @return true|false
     */
    static function semanaPasada($fecha) {
        $semana = new \DateTime('now');
        if (intval($semana->format("W") - 1) === intval($fecha->format("W"))) {
            return true;
        }
        return false;
    }

    /**
     * Devuelve una duración en formato HH:MM:SS
     * @param int $duracion
     * @return string
     */
    static function formatoDuracion($duracion) {
        $horas = $duracion / 60 / 60;
        if ($horas > 0.0) {
            $horas = floor($horas) . '';
            $duracion -= $horas * 60 * 60;
        } else {
            $horas = 0;
        }
        $minutos = $duracion / 60;
        if ($minutos > 0.0) {
            $minutos = floor($minutos);
            $duracion -= $minutos * 60;
        } else {
            $minutos = 0;
        }
        $segundos = $duracion;

        if ($horas === 0) {
            $horas = '00:';
        } else {
            if ($horas < 10) {
                $horas = '0' . $horas . ':';
            } else {
                $horas = $horas . ':';
            }
        }

        if ($minutos === 0) {
            $minutos = '00:';
        } else {
            if ($minutos < 10) {
                $minutos = '0' . $minutos . ':';
            } else {
                $minutos = $minutos . ':';
            }
        }
        if ($segundos === 0) {
            $segundos += '00';
        } else {
            if ($segundos < 10) {
                $segundos = '0' . $segundos;
            } else {
                $segundos = '' . $segundos;
            }
        }
        return $horas . $minutos . $segundos;
    }

    /**
     * Obtener la última bonificación por calificación propuesta por
     * el GdT en una sección determinada
     * @param type $doctrine
     * @param string $nombre_seccion el nombre de la sección
     */
    static function getUltimasCalificacionesSeccion($doctrine, $nombre_seccion) {
        $CALIFICACIONES = $doctrine->getRepository('AppBundle:Calificaciones')->findAll();
        $RESPUESTA = [];
        if (count($CALIFICACIONES)) {
            $SECCION = $doctrine->getRepository('AppBundle:EjercicioSeccion')->findOneBySeccion($nombre_seccion);
            $query = $doctrine->getRepository('AppBundle:Ejercicio')->createQueryBuilder('a');
            $query->select('a');
            $query->where('a.idEjercicioSeccion = :SECCION');
            $query->orderBy('a.fecha', 'DESC');
            $query->setParameters(['SECCION' => $SECCION]);
            $EJERCICIO = $query->getQuery()->getResult();
            foreach ($CALIFICACIONES as $CALIFICACION) {
                $aux['CALIFICACION'] = $CALIFICACION;
                $aux['BONIFICACION'] = Utils::segundosToDias(0);
                if (count($EJERCICIO) && $EJERCICIO[0] !== null) {
                    $BONIFICACION = $doctrine->getRepository('AppBundle:EjercicioBonificacion')->findOneBy([
                        'idEjercicio' => $EJERCICIO[0], 'idCalificacion' => $CALIFICACION
                    ]);
                    if ($BONIFICACION !== null) {
                        $aux['BONIFICACION'] = Utils::segundosToDias($BONIFICACION->getBonificacion());
                    }
                }
                $RESPUESTA[] = $aux;
            }
            return $RESPUESTA;
        }
        return 0;
    }

    /**
     * Convierte un día en estring a su valor numérico
     * @param string $dia
     * @return string
     */
    static function tutoriaDiaToInt($dia) {
        switch ($dia) {
            case 'Lunes':
                return '0';
            case 'Martes':
                return '1';
            case 'Miercoles':
                return '2';
            case 'Jueves':
                return '3';
            case 'Viernes':
                return '4';
            case 'Sabado':
                return '5';
            case 'Domingo':
                return '6';
        }
    }

    /**
     * Convierte segundos en milisegundos
     * @param int $mili
     * @return int Segundos
     */
    static function milisegundosToSegundos($mili) {
        $segundos = $mili / 1000;
        return intval($segundos);
    }

    /**
     * Nos dice si un usuario ha apostado o no a una opción de una apuesta. 
     * Además nos dice si el usuario ha apostado ya a otra opción de la misma
     * apuesta
     * @param type $doctrine
     * @param type $USUARIO
     * @param type $OPCION_APUESTA
     * @return int 0 - No ha apostado, -1 - Ha apostado a otra opción, 1 ha apostado a esa opción
     */
    static function haApostado($doctrine, $USUARIO, $OPCION_APUESTA) {
        $em = $doctrine->getManager();
        $qb = $em->createQueryBuilder();
        $APUESTA_PRINCIPAL = $OPCION_APUESTA->getIdApuesta();
        $TODAS_OPCIONES = $doctrine->getRepository('AppBundle:ApuestaPosibilidad')->findByIdApuesta($APUESTA_PRINCIPAL);
        // Reviso todas mis apuestas en esta categoría
        $query = $qb->select('ua')
                ->from('\AppBundle\Entity\UsuarioApuesta', 'ua')
                ->where('ua.idApuestaPosibilidad IN (:TODAS) AND ua.idUsuario = :IdUsuario')
                ->setParameters(['TODAS' => array_values($TODAS_OPCIONES), 'IdUsuario' => $USUARIO]);
        $APUESTAS = $query->getQuery()->getResult();
        // Mi apuesta...
        $APUESTA = $doctrine->getRepository('AppBundle:UsuarioApuesta')->findOneBy([
            'idApuestaPosibilidad' => $OPCION_APUESTA, 'idUsuario' => $USUARIO
        ]);

        // Nunca he apostado en esta categoría
        if (!count($APUESTAS)) {
            return 0;
        }
        // Aquí he apostado sí o sí. Si la apuesta que voy a realizar no existe
        // significa que estoy votando otra opción
        if (null === $APUESTA) {
            return -1;
        }
        // La apuesta es para actualizar
        return 1;
    }

    /**
     * Devuelve una mina si está activa, 0 en otro caso
     * @param type $doctrine
     * @return MINA|0
     */
    static function minaActiva($doctrine) {
        $query = $doctrine->getRepository('AppBundle:Mina')->createQueryBuilder('a');
        $query->select('a');
        $query->orderBy('a.fecha', 'DESC');
        $MINA = $query->getQuery()->getResult();
        if (!count($MINA)) {
            return 0;
        }
        $HOY = new \DateTime('now');
        if ($MINA[0]->getFechaFinal() < $HOY) {
            return 0;
        }
        return $MINA[0];
    }

    /**
     * Obtiene la última mina desactivada, 0 si no hay minas
     * @param type $doctrine
     * @return MINA|0
     */
    static function ultimaMinaDesactivada($doctrine) {
        $AHORA = new \DateTime('now');
        $query = $doctrine->getRepository('AppBundle:Mina')->createQueryBuilder('a');
        $query->select('a');
        $query->orderBy('a.fechaFinal', 'DESC');
        $MINAS = $query->getQuery()->getResult();
        if (!count($MINAS)) {
            return 0;
        }
        foreach ($MINAS as $MINA) {
            if ($MINA->getFechaFinal() < $AHORA) {
                return $MINA;
            }
        }
        return 0;
    }

    /**
     * Inicializa un slot vacío de reto de Felicidad para que pueda consumir
     * un ciudadano
     * @param type $doctrine
     * @param <AppBundle/Entity/Usuario> $USUARIO
     */
    static function setEjerciciosFelicidadUsuario($doctrine, $USUARIO) {
        $em = $doctrine->getManager();
        for ($fase = 1; $fase < 5; $fase++) {
            $EJERCICIO_FELICIDAD = $doctrine->getRepository('AppBundle:EjercicioFelicidad')->findOneBy([
                'idUsuario' => $USUARIO, 'fase' => $fase
            ]);
            if ($EJERCICIO_FELICIDAD === null) {
                $EJERCICIO_FELICIDAD = new \AppBundle\Entity\EjercicioFelicidad();
                $EJERCICIO_FELICIDAD->setEnunciado('');
                $EJERCICIO_FELICIDAD->setFase($fase);
                $EJERCICIO_FELICIDAD->setFecha(new \DateTime('now'));
                $EJERCICIO_FELICIDAD->setIdEjercicioEntrega(null);
                $EJERCICIO_FELICIDAD->setIdEjercicioPropuesta(null);
                $EJERCICIO_FELICIDAD->setIdUsuario($USUARIO);
                $EJERCICIO_FELICIDAD->setPorcentaje(0);
                $em->persist($EJERCICIO_FELICIDAD);
            }
        }
        $em->flush();
    }

    /**
     * Elimina acentos y caracteres extraños de una cadena
     * @param string $string
     * @return string
     */
    static function replaceAccented($cadena) {
        $result = strtolower($cadena);

        $unwanted_array = array('Š' => 'S', 'š' => 's', 'Ž' => 'Z', 'ž' => 'z', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E',
            'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U',
            'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Þ' => 'B', 'ß' => 'Ss', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'a', 'ç' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o',
            'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ý' => 'y', 'þ' => 'b', 'ÿ' => 'y');
        $result = strtr($result, $unwanted_array);
        return $result;
    }

    /**
     * Indica si una cita es anterior al día actual
     * @param type $CITA
     * @return int
     */
    static function esCitaAntigua($CITA) {
        $hoy = new \DateTime('now');
        $dia_int = Utils::diaStringToInt($CITA->getDia());
        if (intval($dia_int) < intval($hoy->format('w'))) {
            return 1;
        }
        return 0;
    }

    /**
     * Indica si el día de una cita es el actual
     * @param type $CITA
     * @return int
     */
    static function esCitaDeHoy($CITA) {
        $hoy = new \DateTime('now');
        $dia_int = Utils::diaStringToInt($CITA->getDia());
        if (intval($dia_int) === intval($hoy->format('w'))) {
            return 1;
        }
        return 0;
    }

    /**
     * Convierte un día en String (Lunes, Martes, Miercoles, Jueves, Viernes)
     * a un día en int (1, 2, 3, 4, 5)
     * @param type $diaString
     * @return int
     */
    static function diaStringToInt($diaString) {
        $dia_str = strtolower($diaString);

        $conversores = array('lunes' => 1, 'martes' => 2, 'miercoles' => 3, 'jueves' => 4, 'viernes' => 5);
        $dia_int = strtr($dia_str, $conversores);

        return $dia_int;
    }

    public function cmp($a, $b) {
        if (inval($a['CANTIDAD']) == intval($b['CANTIDAD'])) {
            return 0;
        }
        return (intval($a['CANTIDAD']) < intval($b['CANTIDAD'])) ? -1 : 1;
    }

    /**
     * Recorta el texto añadiendo puntos suspensivos
     * @param string $texto
     * @param int $limite
     * @return string
     */
    static function recortar_texto($texto, $limite = 100) {
        $texto = trim($texto);
        $texto = strip_tags($texto);
        $tamano = strlen($texto);
        $resultado = '';
        if ($tamano <= $limite) {
            return $texto;
        } else {
            $texto = substr($texto, 0, $limite);
            $palabras = explode(' ', $texto);
            $resultado = implode(' ', $palabras);
            $resultado .= '...';
        }
        return $resultado;
    }

    /**
     * Obtiene el TdV acumulado de un usuario
     * @param type $doctrine
     * @param <AppBundle/Entity/Usuario> $CIUDADANO
     * @return int
     */
    static function getTdVAcumulado($doctrine, $CIUDADANO) {
        $query = $doctrine
                ->getRepository('AppBundle:UsuarioMovimiento')
                ->createQueryBuilder('um');
        $query->select('SUM(um.cantidad)');
        $query->where('um.idUsuario = :ID_USUARIO');
        $query->setParameter('ID_USUARIO', $CIUDADANO->getIdUsuario());
        $cant = $query->getQuery()->getSingleScalarResult();
        return $cant;
    }

}
