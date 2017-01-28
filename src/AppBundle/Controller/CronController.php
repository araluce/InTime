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
use \Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Utils\Usuario;
use AppBundle\Utils\Utils;
use AppBundle\Utils\Trabajo;

/**
 * Description of CronController
 *
 * @author araluce
 */
class CronController extends Controller {

    /**
     * @Route("/cron/jornadaLaboral", name="jornadaLaboral")
     */
    public function cronJornadaLaboralAction(Request $request) {
        $doctrine = $this->getDoctrine();
        Utils::setError($doctrine, 3, 'CRON - Jornada Laboral');
        $ROL_CIUDADANO = $doctrine->getRepository('AppBundle:Rol')->findOneByNombre('Jugador');
        $CIUDADANOS = $doctrine->getRepository('AppBundle:Usuario')->findByIdRol($ROL_CIUDADANO);
        $n = 0;
        foreach ($CIUDADANOS as $CIUDADANO) {
            $RES = Trabajo::comprobarJornadaLaboral($doctrine, $CIUDADANO);
            if ($RES) {
                $TDV_JORNADA_LABORAL = Utils::getConstante($doctrine, 'jornada_laboral');
                Usuario::operacionSobreTdV($doctrine, $CIUDADANO, $TDV_JORNADA_LABORAL, 'Ingreso - Jornada Laboral');
                $n++;
            }
        }
        return new JsonResponse(json_encode(array(
            'estado' => 'OK',
            'message' => $n . ' ciudadano/s han cobrado su Jornada Laboral'
                )), 200);
    }
    
    /**
     * @Route("/cron/checkTdV", name="checkTdV")
     */
    public function checkTdVAction(Request $request) {
        $doctrine = $this->getDoctrine();
        Utils::setError($doctrine, 3, 'CRON - Tiempo de vida');
        $ROL_CIUDADANO = $doctrine->getRepository('AppBundle:Rol')->findOneByNombre('Jugador');
        $CIUDADANOS = $doctrine->getRepository('AppBundle:Usuario')->findByIdRol($ROL_CIUDADANO);
        $Fecha = new \DateTime('now');
        $n = 0;
        foreach ($CIUDADANOS as $CIUDADANO) {
            if($CIUDADANO->getIdCuenta()->getTdv() < $Fecha){
                Usuario::setDefuncion($doctrine, $CIUDADANO);
                $n++;
            }
        }
        return new JsonResponse(json_encode(array(
            'estado' => 'OK',
            'message' => $n . ' ciudadano/s declarados fallecidos'
                )), 200);
    }
    
    /**
     * @Route("/cron/pagarFinDeSemana", name="pagarFinDeSemana")
     */
    public function pagarFinDeSemanaAction(Request $request) {
        $doctrine = $this->getDoctrine();
        Utils::setError($doctrine, 3, 'CRON - Pago fines de semana');
        $ROL_CIUDADANO = $doctrine->getRepository('AppBundle:Rol')->findOneByNombre('Jugador');
        $CIUDADANOS = $doctrine->getRepository('AppBundle:Usuario')->findByIdRol($ROL_CIUDADANO);
        foreach ($CIUDADANOS as $CIUDADANO) {
            Usuario::operacionSobreTdV($doctrine, $CIUDADANO, 172800, 'Ajuste - Fin de semana');
        }
        return new JsonResponse(json_encode(array('estado' => 'OK')), 200);
    }
    
    /**
     * @Route("/cron/cobrarCuotaPrestamo", name="cobrarCuotaPrestamo")
     */
    public function cobrarCuotaPrestamoAction(Request $request) {
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $query = $doctrine->getRepository('AppBundle:UsuarioPrestamo')->createQueryBuilder('a');
        $query->select('a');
        $query->where('a.restante > 0');
        $PRESTAMOS = $query->getQuery()->getResult();
        $recaudado = 0;
        foreach($PRESTAMOS as $PRESTAMO){
            $interes = $PRESTAMO->getInteres();
            $cuota = ($PRESTAMO->getCantidad() + ($PRESTAMO->getCantidad() * $interes))/4;
            $recaudado += $cuota;
            $restante = $PRESTAMO->getRestante() - $cuota;
            $CIUDADANO = $PRESTAMO->getIdUsuario();
            Usuario::operacionSobreTdV($doctrine, $CIUDADANO, (-1)*$cuota, 'Cobro - Cuota semanal por préstamo pendiente');
            $PRESTAMO->setRestante($restante);
            $em->persist($PRESTAMO);
        }
        $em->flush();
        Utils::setError($doctrine, 3, 'CRON - Cobrar la cuota de préstamo ('.$recaudado.')');
        
        return new JsonResponse(json_encode(array('estado' => 'OK','message' => 'Se ha recaudado un total de ' . $recaudado . ' segundos')), 200);
    }

}
