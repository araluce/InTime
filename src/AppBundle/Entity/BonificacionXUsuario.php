<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BonificacionXUsuario
 *
 * @ORM\Table(name="BONIFICACION_X_USUARIO", indexes={@ORM\Index(name="id_bonificacion_extra", columns={"id_bonificacion_extra", "id_usuario"})})
 * @ORM\Entity
 */
class BonificacionXUsuario
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_bonificacion_extra", type="integer", nullable=false)
     */
    private $idBonificacionExtra;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_usuario", type="integer", nullable=false)
     */
    private $idUsuario;

    /**
     * @var boolean
     *
     * @ORM\Column(name="usado", type="boolean", nullable=false)
     */
    private $usado;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_bonificacion_x_usuario", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idBonificacionXUsuario;



    /**
     * Set idBonificacionExtra
     *
     * @param integer $idBonificacionExtra
     * @return BonificacionXUsuario
     */
    public function setIdBonificacionExtra($idBonificacionExtra)
    {
        $this->idBonificacionExtra = $idBonificacionExtra;

        return $this;
    }

    /**
     * Get idBonificacionExtra
     *
     * @return integer 
     */
    public function getIdBonificacionExtra()
    {
        return $this->idBonificacionExtra;
    }

    /**
     * Set idUsuario
     *
     * @param integer $idUsuario
     * @return BonificacionXUsuario
     */
    public function setIdUsuario($idUsuario)
    {
        $this->idUsuario = $idUsuario;

        return $this;
    }

    /**
     * Get idUsuario
     *
     * @return integer 
     */
    public function getIdUsuario()
    {
        return $this->idUsuario;
    }

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
     * Get idBonificacionXUsuario
     *
     * @return integer 
     */
    public function getIdBonificacionXUsuario()
    {
        return $this->idBonificacionXUsuario;
    }
}
