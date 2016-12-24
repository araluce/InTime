<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EjercicioEntrega
 *
 * @ORM\Table(name="EJERCICIO_ENTREGA", indexes={@ORM\Index(name="id_ejercicio", columns={"id_ejercicio"}), @ORM\Index(name="id_usuario", columns={"id_usuario"})})
 * @ORM\Entity
 */
class EjercicioEntrega
{
    /**
     * @var string
     *
     * @ORM\Column(name="nombre", type="string", length=1000, nullable=false)
     */
    private $nombre;

    /**
     * @var string
     *
     * @ORM\Column(name="mime", type="string", length=1000, nullable=false)
     */
    private $mime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha", type="datetime", nullable=false)
     */
    private $fecha;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_ejercicio_entrega", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
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
     * @var \AppBundle\Entity\Ejercicio
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Ejercicio")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_ejercicio", referencedColumnName="id_ejercicio")
     * })
     */
    private $idEjercicio;



    /**
     * Set nombre
     *
     * @param string $nombre
     * @return EjercicioEntrega
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * Get nombre
     *
     * @return string 
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set mime
     *
     * @param string $mime
     * @return EjercicioEntrega
     */
    public function setMime($mime)
    {
        $this->mime = $mime;

        return $this;
    }

    /**
     * Get mime
     *
     * @return string 
     */
    public function getMime()
    {
        return $this->mime;
    }

    /**
     * Set fecha
     *
     * @param \DateTime $fecha
     * @return EjercicioEntrega
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
     * Get idEjercicioEntrega
     *
     * @return integer 
     */
    public function getIdEjercicioEntrega()
    {
        return $this->idEjercicioEntrega;
    }

    /**
     * Set idUsuario
     *
     * @param \AppBundle\Entity\Usuario $idUsuario
     * @return EjercicioEntrega
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

    /**
     * Set idEjercicio
     *
     * @param \AppBundle\Entity\Ejercicio $idEjercicio
     * @return EjercicioEntrega
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
