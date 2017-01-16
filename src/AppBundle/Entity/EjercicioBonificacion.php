<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EjercicioBonificacion
 *
 * @ORM\Table(name="EJERCICIO_BONIFICACION", indexes={@ORM\Index(name="id_ejercicio", columns={"id_ejercicio", "id_calificacion"}), @ORM\Index(name="id_calificacion", columns={"id_calificacion"}), @ORM\Index(name="IDX_6F5D8886159873D3", columns={"id_ejercicio"})})
 * @ORM\Entity
 */
class EjercicioBonificacion
{
    /**
     * @var integer
     *
     * @ORM\Column(name="bonificacion", type="integer", nullable=false)
     */
    private $bonificacion;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_ejercicio_bonificacion", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idEjercicioBonificacion;

    /**
     * @var \AppBundle\Entity\Ejercicio
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Ejercicio")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_ejercicio", referencedColumnName="id_ejercicio")
     * })
     */
    private $idEjercicio;

    /**
     * @var \AppBundle\Entity\Calificaciones
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Calificaciones")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_calificacion", referencedColumnName="id_calificaciones")
     * })
     */
    private $idCalificacion;



    /**
     * Set bonificacion
     *
     * @param integer $bonificacion
     * @return EjercicioBonificacion
     */
    public function setBonificacion($bonificacion)
    {
        $this->bonificacion = $bonificacion;

        return $this;
    }

    /**
     * Get bonificacion
     *
     * @return integer 
     */
    public function getBonificacion()
    {
        return $this->bonificacion;
    }

    /**
     * Get idEjercicioBonificacion
     *
     * @return integer 
     */
    public function getIdEjercicioBonificacion()
    {
        return $this->idEjercicioBonificacion;
    }

    /**
     * Set idEjercicio
     *
     * @param \AppBundle\Entity\Ejercicio $idEjercicio
     * @return EjercicioBonificacion
     */
    public function setIdEjercicio(\AppBundle\Entity\Ejercicio $idEjercicio = null)
    {
        $this->idEjercicio = $idEjercicio;

        return $this;
    }

    /**
     * Get idEjercicio
     *
     * @return \AppBundle\Entity\Ejercicio 
     */
    public function getIdEjercicio()
    {
        return $this->idEjercicio;
    }

    /**
     * Set idCalificacion
     *
     * @param \AppBundle\Entity\Calificaciones $idCalificacion
     * @return EjercicioBonificacion
     */
    public function setIdCalificacion(\AppBundle\Entity\Calificaciones $idCalificacion = null)
    {
        $this->idCalificacion = $idCalificacion;

        return $this;
    }

    /**
     * Get idCalificacion
     *
     * @return \AppBundle\Entity\Calificaciones 
     */
    public function getIdCalificacion()
    {
        return $this->idCalificacion;
    }
}
