<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EjercicioRespuesta
 *
 * @ORM\Table(name="EJERCICIO_RESPUESTA", indexes={@ORM\Index(name="id_ejercicio", columns={"id_ejercicio"})})
 * @ORM\Entity
 */
class EjercicioRespuesta
{
    /**
     * @var string
     *
     * @ORM\Column(name="respuesta", type="string", length=1000, nullable=false)
     */
    private $respuesta;

    /**
     * @var boolean
     *
     * @ORM\Column(name="correcta", type="boolean", nullable=true)
     */
    private $correcta;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_ejercicio_respuesta", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idEjercicioRespuesta;

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
     * Set respuesta
     *
     * @param string $respuesta
     * @return EjercicioRespuesta
     */
    public function setRespuesta($respuesta)
    {
        $this->respuesta = $respuesta;

        return $this;
    }

    /**
     * Get respuesta
     *
     * @return string 
     */
    public function getRespuesta()
    {
        return $this->respuesta;
    }

    /**
     * Set correcta
     *
     * @param boolean $correcta
     * @return EjercicioRespuesta
     */
    public function setCorrecta($correcta)
    {
        $this->correcta = $correcta;

        return $this;
    }

    /**
     * Get correcta
     *
     * @return boolean 
     */
    public function getCorrecta()
    {
        return $this->correcta;
    }

    /**
     * Get idEjercicioRespuesta
     *
     * @return integer 
     */
    public function getIdEjercicioRespuesta()
    {
        return $this->idEjercicioRespuesta;
    }

    /**
     * Set idEjercicio
     *
     * @param \AppBundle\Entity\Ejercicio $idEjercicio
     * @return EjercicioRespuesta
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
}
