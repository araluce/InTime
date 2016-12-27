<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Apuesta
 *
 * @ORM\Table(name="APUESTA")
 * @ORM\Entity
 */
class Apuesta
{
    /**
     * @var string
     *
     * @ORM\Column(name="descripcion", type="string", length=1000, nullable=false)
     */
    private $descripcion;

    /**
     * @var boolean
     *
     * @ORM\Column(name="disponible", type="boolean", nullable=false)
     */
    private $disponible;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha", type="datetime", nullable=false)
     */
    private $fecha;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_apuesta", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idApuesta;



    /**
     * Set descripcion
     *
     * @param string $descripcion
     * @return Apuesta
     */
    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    /**
     * Get descripcion
     *
     * @return string 
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }

    /**
     * Set disponible
     *
     * @param boolean $disponible
     * @return Apuesta
     */
    public function setDisponible($disponible)
    {
        $this->disponible = $disponible;

        return $this;
    }

    /**
     * Get disponible
     *
     * @return boolean 
     */
    public function getDisponible()
    {
        return $this->disponible;
    }

    /**
     * Set fecha
     *
     * @param \DateTime $fecha
     * @return Apuesta
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
     * Get idApuesta
     *
     * @return integer 
     */
    public function getIdApuesta()
    {
        return $this->idApuesta;
    }
}
