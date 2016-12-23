<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Peticion
 *
 * @ORM\Table(name="peticion", indexes={@ORM\Index(name="id_usuario", columns={"id_usuario"})})
 * @ORM\Entity
 */
class Peticion
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_peticion", type="datetime", nullable=false)
     */
    private $fechaPeticion = 'CURRENT_TIMESTAMP';

    /**
     * @var string
     *
     * @ORM\Column(name="finalidad", type="string", length=100, nullable=false)
     */
    private $finalidad;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="coste", type="time", nullable=true)
     */
    private $coste;

    /**
     * @var string
     *
     * @ORM\Column(name="motivo", type="string", length=100, nullable=true)
     */
    private $motivo;

    /**
     * @var boolean
     *
     * @ORM\Column(name="aceptada", type="boolean", nullable=true)
     */
    private $aceptada;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_fin", type="datetime", nullable=true)
     */
    private $fechaFin;

    /**
     * @var string
     *
     * @ORM\Column(name="tipo", type="string", length=100, nullable=false)
     */
    private $tipo;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_peticion", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idPeticion;

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
     * Set fechaPeticion
     *
     * @param \DateTime $fechaPeticion
     *
     * @return Peticion
     */
    public function setFechaPeticion($fechaPeticion)
    {
        $this->fechaPeticion = $fechaPeticion;

        return $this;
    }

    /**
     * Get fechaPeticion
     *
     * @return \DateTime
     */
    public function getFechaPeticion()
    {
        return $this->fechaPeticion;
    }

    /**
     * Set finalidad
     *
     * @param string $finalidad
     *
     * @return Peticion
     */
    public function setFinalidad($finalidad)
    {
        $this->finalidad = $finalidad;

        return $this;
    }

    /**
     * Get finalidad
     *
     * @return string
     */
    public function getFinalidad()
    {
        return $this->finalidad;
    }

    /**
     * Set coste
     *
     * @param \DateTime $coste
     *
     * @return Peticion
     */
    public function setCoste($coste)
    {
        $this->coste = $coste;

        return $this;
    }

    /**
     * Get coste
     *
     * @return \DateTime
     */
    public function getCoste()
    {
        return $this->coste;
    }

    /**
     * Set motivo
     *
     * @param string $motivo
     *
     * @return Peticion
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
     * Set aceptada
     *
     * @param boolean $aceptada
     *
     * @return Peticion
     */
    public function setAceptada($aceptada)
    {
        $this->aceptada = $aceptada;

        return $this;
    }

    /**
     * Get aceptada
     *
     * @return boolean
     */
    public function getAceptada()
    {
        return $this->aceptada;
    }

    /**
     * Set fechaFin
     *
     * @param \DateTime $fechaFin
     *
     * @return Peticion
     */
    public function setFechaFin($fechaFin)
    {
        $this->fechaFin = $fechaFin;

        return $this;
    }

    /**
     * Get fechaFin
     *
     * @return \DateTime
     */
    public function getFechaFin()
    {
        return $this->fechaFin;
    }

    /**
     * Set tipo
     *
     * @param string $tipo
     *
     * @return Peticion
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
     * Get idPeticion
     *
     * @return integer
     */
    public function getIdPeticion()
    {
        return $this->idPeticion;
    }

    /**
     * Set idUsuario
     *
     * @param \AppBundle\Entity\Usuario $idUsuario
     *
     * @return Peticion
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
