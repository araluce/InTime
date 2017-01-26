<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UsuarioPrestamo
 *
 * @ORM\Table(name="USUARIO_PRESTAMO", indexes={@ORM\Index(name="id_usuario", columns={"id_usuario"})})
 * @ORM\Entity
 */
class UsuarioPrestamo
{
    /**
     * @var string
     *
     * @ORM\Column(name="motivo", type="string", length=100, nullable=false)
     */
    private $motivo;

    /**
     * @var integer
     *
     * @ORM\Column(name="cantidad", type="integer", nullable=false)
     */
    private $cantidad;

    /**
     * @var integer
     *
     * @ORM\Column(name="restante", type="integer", nullable=false)
     */
    private $restante;

    /**
     * @var float
     *
     * @ORM\Column(name="interes", type="float", precision=10, scale=0, nullable=false)
     */
    private $interes;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha", type="datetime", nullable=false)
     */
    private $fecha;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_usuario_prestamo", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idUsuarioPrestamo;

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
     * Set motivo
     *
     * @param string $motivo
     * @return UsuarioPrestamo
     */
    public function setMotivo($motivo)
    {
        $this->motivo = $motivo;

        return $this;
    }

    /**
     * Get motivo
     *
     * @return string 
     */
    public function getMotivo()
    {
        return $this->motivo;
    }

    /**
     * Set cantidad
     *
     * @param integer $cantidad
     * @return UsuarioPrestamo
     */
    public function setCantidad($cantidad)
    {
        $this->cantidad = $cantidad;

        return $this;
    }

    /**
     * Get cantidad
     *
     * @return integer 
     */
    public function getCantidad()
    {
        return $this->cantidad;
    }

    /**
     * Set restante
     *
     * @param integer $restante
     * @return UsuarioPrestamo
     */
    public function setRestante($restante)
    {
        $this->restante = $restante;

        return $this;
    }

    /**
     * Get restante
     *
     * @return integer 
     */
    public function getRestante()
    {
        return $this->restante;
    }

    /**
     * Set interes
     *
     * @param float $interes
     * @return UsuarioPrestamo
     */
    public function setInteres($interes)
    {
        $this->interes = $interes;

        return $this;
    }

    /**
     * Get interes
     *
     * @return float 
     */
    public function getInteres()
    {
        return $this->interes;
    }

    /**
     * Set fecha
     *
     * @param \DateTime $fecha
     * @return UsuarioPrestamo
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
     * Get idUsuarioPrestamo
     *
     * @return integer 
     */
    public function getIdUsuarioPrestamo()
    {
        return $this->idUsuarioPrestamo;
    }

    /**
     * Set idUsuario
     *
     * @param \AppBundle\Entity\Usuario $idUsuario
     * @return UsuarioPrestamo
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
