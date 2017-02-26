<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BonificacionXUsuario
 *
 * @ORM\Table(name="BONIFICACION_X_USUARIO", indexes={@ORM\Index(name="id_bonificacion_extra", columns={"id_bonificacion_extra", "id_usuario"}), @ORM\Index(name="id_usuario", columns={"id_usuario"}), @ORM\Index(name="IDX_FBE9D154C3B63D1A", columns={"id_bonificacion_extra"})})
 * @ORM\Entity
 */
class BonificacionXUsuario
{
    /**
     * @var boolean
     *
     * @ORM\Column(name="usado", type="boolean", nullable=false)
     */
    private $usado;

    /**
     * @var integer
     *
     * @ORM\Column(name="contador", type="integer", nullable=false)
     */
    private $contador;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha", type="datetime", nullable=true)
     */
    private $fecha;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_bonificacion_x_usuario", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idBonificacionXUsuario;

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
     * @var \AppBundle\Entity\BonificacionExtra
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\BonificacionExtra")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_bonificacion_extra", referencedColumnName="id_bonificacion_extra")
     * })
     */
    private $idBonificacionExtra;



    /**
     * Set usado
     *
     * @param boolean $usado
     * @return BonificacionXUsuario
     */
    public function setUsado($usado)
    {
        $this->usado = $usado;

        return $this;
    }

    /**
     * Get usado
     *
     * @return boolean 
     */
    public function getUsado()
    {
        return $this->usado;
    }

    /**
     * Set contador
     *
     * @param integer $contador
     * @return BonificacionXUsuario
     */
    public function setContador($contador)
    {
        $this->contador = $contador;

        return $this;
    }

    /**
     * Get contador
     *
     * @return integer 
     */
    public function getContador()
    {
        return $this->contador;
    }

    /**
     * Set fecha
     *
     * @param \DateTime $fecha
     * @return BonificacionXUsuario
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
     * Get idBonificacionXUsuario
     *
     * @return integer 
     */
    public function getIdBonificacionXUsuario()
    {
        return $this->idBonificacionXUsuario;
    }

    /**
     * Set idUsuario
     *
     * @param \AppBundle\Entity\Usuario $idUsuario
     * @return BonificacionXUsuario
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
     * Set idBonificacionExtra
     *
     * @param \AppBundle\Entity\BonificacionExtra $idBonificacionExtra
     * @return BonificacionXUsuario
     */
    public function setIdBonificacionExtra(\AppBundle\Entity\BonificacionExtra $idBonificacionExtra = null)
    {
        $this->idBonificacionExtra = $idBonificacionExtra;

        return $this;
    }

    /**
     * Get idBonificacionExtra
     *
     * @return \AppBundle\Entity\BonificacionExtra 
     */
    public function getIdBonificacionExtra()
    {
        return $this->idBonificacionExtra;
    }
}
