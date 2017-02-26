<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EjercicioFelicidad
 *
 * @ORM\Table(name="EJERCICIO_FELICIDAD", indexes={@ORM\Index(name="id_ejercicio_propuesta", columns={"id_ejercicio_propuesta", "id_ejercicio_entrega"}), @ORM\Index(name="id_ejercicio_entrega", columns={"id_ejercicio_entrega"}), @ORM\Index(name="id_usuario", columns={"id_usuario"}), @ORM\Index(name="IDX_2DE39626D8F558A", columns={"id_ejercicio_propuesta"})})
 * @ORM\Entity
 */
class EjercicioFelicidad
{
    /**
     * @var string
     *
     * @ORM\Column(name="enunciado", type="string", length=1000, nullable=false)
     */
    private $enunciado;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha", type="datetime", nullable=true)
     */
    private $fecha;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_ejercicio_felicidad", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idEjercicioFelicidad;

    /**
     * @var \AppBundle\Entity\Ejercicio
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Ejercicio")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_ejercicio_propuesta", referencedColumnName="id_ejercicio")
     * })
     */
    private $idEjercicioPropuesta;

    /**
     * @var \AppBundle\Entity\Ejercicio
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Ejercicio")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_ejercicio_entrega", referencedColumnName="id_ejercicio")
     * })
     */
    private $idEjercicioEntrega;

    /**
     * @var \AppBundle\Entity\Usuario
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Usuario")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_usuario", referencedColumnName="id_usuario")
     * })
     */
    private $idUsuario;



    /**
     * Set enunciado
     *
     * @param string $enunciado
     * @return EjercicioFelicidad
     */
    public function setEnunciado($enunciado)
    {
        $this->enunciado = $enunciado;

        return $this;
    }

    /**
     * Get enunciado
     *
     * @return string 
     */
    public function getEnunciado()
    {
        return $this->enunciado;
    }

    /**
     * Set fecha
     *
     * @param \DateTime $fecha
     * @return EjercicioFelicidad
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
     * Get idEjercicioFelicidad
     *
     * @return integer 
     */
    public function getIdEjercicioFelicidad()
    {
        return $this->idEjercicioFelicidad;
    }

    /**
     * Set idEjercicioPropuesta
     *
     * @param \AppBundle\Entity\Ejercicio $idEjercicioPropuesta
     * @return EjercicioFelicidad
     */
    public function setIdEjercicioPropuesta(\AppBundle\Entity\Ejercicio $idEjercicioPropuesta = null)
    {
        $this->idEjercicioPropuesta = $idEjercicioPropuesta;

        return $this;
    }

    /**
     * Get idEjercicioPropuesta
     *
     * @return \AppBundle\Entity\Ejercicio 
     */
    public function getIdEjercicioPropuesta()
    {
        return $this->idEjercicioPropuesta;
    }

    /**
     * Set idEjercicioEntrega
     *
     * @param \AppBundle\Entity\Ejercicio $idEjercicioEntrega
     * @return EjercicioFelicidad
     */
    public function setIdEjercicioEntrega(\AppBundle\Entity\Ejercicio $idEjercicioEntrega = null)
    {
        $this->idEjercicioEntrega = $idEjercicioEntrega;

        return $this;
    }

    /**
     * Get idEjercicioEntrega
     *
     * @return \AppBundle\Entity\Ejercicio 
     */
    public function getIdEjercicioEntrega()
    {
        return $this->idEjercicioEntrega;
    }

    /**
     * Set idUsuario
     *
     * @param \AppBundle\Entity\Usuario $idUsuario
     * @return EjercicioFelicidad
     */
    public function setIdUsuario(\AppBundle\Entity\Usuario $idUsuario = null)
    {
        $this->idUsuario = $idUsuario;

        return $this;
    }

    /**
     * Get idUsuario
     *
     * @return \AppBundle\Entity\Usuario 
     */
    public function getIdUsuario()
    {
        return $this->idUsuario;
    }
}
