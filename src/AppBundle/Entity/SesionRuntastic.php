<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SesionRuntastic
 *
 * @ORM\Table(name="SESION_RUNTASTIC", indexes={@ORM\Index(name="id_usuario", columns={"id_usuario_runtastic"})})
 * @ORM\Entity
 */
class SesionRuntastic
{
    /**
     * @var boolean
     *
     * @ORM\Column(name="evaluado", type="boolean", nullable=false)
     */
    private $evaluado;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_runtastic", type="integer", nullable=false)
     */
    private $idRuntastic;

    /**
     * @var string
     *
     * @ORM\Column(name="tipo", type="string", length=1000, nullable=false)
     */
    private $tipo;

    /**
     * @var integer
     *
     * @ORM\Column(name="duracion", type="integer", nullable=false)
     */
    private $duracion;

    /**
     * @var integer
     *
     * @ORM\Column(name="distancia", type="integer", nullable=false)
     */
    private $distancia;

    /**
     * @var float
     *
     * @ORM\Column(name="ritmo", type="float", precision=10, scale=0, nullable=false)
     */
    private $ritmo;

    /**
     * @var float
     *
     * @ORM\Column(name="velocidad", type="float", precision=10, scale=0, nullable=false)
     */
    private $velocidad;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha", type="datetime", nullable=false)
     */
    private $fecha;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_sesion_runtastic", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idSesionRuntastic;

    /**
     * @var \AppBundle\Entity\UsuarioRuntastic
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\UsuarioRuntastic")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_usuario_runtastic", referencedColumnName="id_usuario_runtastic")
     * })
     */
    private $idUsuarioRuntastic;



    /**
     * Set evaluado
     *
     * @param boolean $evaluado
     * @return SesionRuntastic
     */
    public function setEvaluado($evaluado)
    {
        $this->evaluado = $evaluado;

        return $this;
    }

    /**
     * Get evaluado
     *
     * @return boolean 
     */
    public function getEvaluado()
    {
        return $this->evaluado;
    }

    /**
     * Set idRuntastic
     *
     * @param integer $idRuntastic
     * @return SesionRuntastic
     */
    public function setIdRuntastic($idRuntastic)
    {
        $this->idRuntastic = $idRuntastic;

        return $this;
    }

    /**
     * Get idRuntastic
     *
     * @return integer 
     */
    public function getIdRuntastic()
    {
        return $this->idRuntastic;
    }

    /**
     * Set tipo
     *
     * @param string $tipo
     * @return SesionRuntastic
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
     * Set duracion
     *
     * @param integer $duracion
     * @return SesionRuntastic
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
     * Set distancia
     *
     * @param integer $distancia
     * @return SesionRuntastic
     */
    public function setDistancia($distancia)
    {
        $this->distancia = $distancia;

        return $this;
    }

    /**
     * Get distancia
     *
     * @return integer 
     */
    public function getDistancia()
    {
        return $this->distancia;
    }

    /**
     * Set ritmo
     *
     * @param float $ritmo
     * @return SesionRuntastic
     */
    public function setRitmo($ritmo)
    {
        $this->ritmo = $ritmo;

        return $this;
    }

    /**
     * Get ritmo
     *
     * @return float 
     */
    public function getRitmo()
    {
        return $this->ritmo;
    }

    /**
     * Set velocidad
     *
     * @param float $velocidad
     * @return SesionRuntastic
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
     * Set fecha
     *
     * @param \DateTime $fecha
     * @return SesionRuntastic
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
     * Get idSesionRuntastic
     *
     * @return integer 
     */
    public function getIdSesionRuntastic()
    {
        return $this->idSesionRuntastic;
    }

    /**
     * Set idUsuarioRuntastic
     *
     * @param \AppBundle\Entity\UsuarioRuntastic $idUsuarioRuntastic
     * @return SesionRuntastic
     */
    public function setIdUsuarioRuntastic(\AppBundle\Entity\UsuarioRuntastic $idUsuarioRuntastic = null)
    {
        $this->idUsuarioRuntastic = $idUsuarioRuntastic;

        return $this;
    }

    /**
     * Get idUsuarioRuntastic
     *
     * @return \AppBundle\Entity\UsuarioRuntastic 
     */
    public function getIdUsuarioRuntastic()
    {
        return $this->idUsuarioRuntastic;
    }
}
