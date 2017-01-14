<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EjercicioRuntastic
 *
 * @ORM\Table(name="EJERCICIO_RUNTASTIC")
 * @ORM\Entity
 */
class EjercicioRuntastic
{
    /**
     * @var string
     *
     * @ORM\Column(name="tipo", type="string", length=1000, nullable=false)
     */
    private $tipo;

    /**
     * @var float
     *
     * @ORM\Column(name="velocidad", type="float", precision=10, scale=0, nullable=false)
     */
    private $velocidad;

    /**
     * @var integer
     *
     * @ORM\Column(name="duracion", type="integer", nullable=false)
     */
    private $duracion;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha", type="datetime", nullable=false)
     */
    private $fecha;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_ejercicio_runtastic", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idEjercicioRuntastic;



    /**
     * Set tipo
     *
     * @param string $tipo
     * @return EjercicioRuntastic
     */
    public function setTipo($tipo)
    {
        $this->tipo = $tipo;

        return $this;
    }

    /**
     * Get tipo
     *
     * @return string 
     */
    public function getTipo()
    {
        return $this->tipo;
    }

    /**
     * Set velocidad
     *
     * @param float $velocidad
     * @return EjercicioRuntastic
     */
    public function setVelocidad($velocidad)
    {
        $this->velocidad = $velocidad;

        return $this;
    }

    /**
     * Get velocidad
     *
     * @return float 
     */
    public function getVelocidad()
    {
        return $this->velocidad;
    }

    /**
     * Set duracion
     *
     * @param integer $duracion
     * @return EjercicioRuntastic
     */
    public function setDuracion($duracion)
    {
        $this->duracion = $duracion;

        return $this;
    }

    /**
     * Get duracion
     *
     * @return integer 
     */
    public function getDuracion()
    {
        return $this->duracion;
    }

    /**
     * Set fecha
     *
     * @param \DateTime $fecha
     * @return EjercicioRuntastic
     */
    public function setFecha($fecha)
    {
        $this->fecha = $fecha;

        return $this;
    }

    /**
     * Get fecha
     *
     * @return \DateTime 
     */
    public function getFecha()
    {
        return $this->fecha;
    }

    /**
     * Get idEjercicioRuntastic
     *
     * @return integer 
     */
    public function getIdEjercicioRuntastic()
    {
        return $this->idEjercicioRuntastic;
    }
}
