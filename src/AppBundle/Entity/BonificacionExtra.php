<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BonificacionExtra
 *
 * @ORM\Table(name="BONIFICACION_EXTRA")
 * @ORM\Entity
 */
class BonificacionExtra
{
    /**
     * @var string
     *
     * @ORM\Column(name="bonificacion", type="string", length=100, nullable=false)
     */
    private $bonificacion;

    /**
     * @var string
     *
     * @ORM\Column(name="descripcion", type="string", length=1000, nullable=false)
     */
    private $descripcion;

    /**
     * @var string
     *
     * @ORM\Column(name="imagen", type="string", length=100, nullable=false)
     */
    private $imagen;

    /**
     * @var integer
     *
     * @ORM\Column(name="coste_xp", type="integer", nullable=false)
     */
    private $costeXp;

    /**
     * @var boolean
     *
     * @ORM\Column(name="disponible", type="boolean", nullable=false)
     */
    private $disponible;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_bonificacion_extra", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idBonificacionExtra;



    /**
     * Set bonificacion
     *
     * @param string $bonificacion
     * @return BonificacionExtra
     */
    public function setBonificacion($bonificacion)
    {
        $this->bonificacion = $bonificacion;

        return $this;
    }

    /**
     * Get bonificacion
     *
     * @return string 
     */
    public function getBonificacion()
    {
        return $this->bonificacion;
    }

    /**
     * Set descripcion
     *
     * @param string $descripcion
     * @return BonificacionExtra
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
     * Set imagen
     *
     * @param string $imagen
     * @return BonificacionExtra
     */
    public function setImagen($imagen)
    {
        $this->imagen = $imagen;

        return $this;
    }

    /**
     * Get imagen
     *
     * @return string 
     */
    public function getImagen()
    {
        return $this->imagen;
    }

    /**
     * Set costeXp
     *
     * @param integer $costeXp
     * @return BonificacionExtra
     */
    public function setCosteXp($costeXp)
    {
        $this->costeXp = $costeXp;

        return $this;
    }

    /**
     * Get costeXp
     *
     * @return integer 
     */
    public function getCosteXp()
    {
        return $this->costeXp;
    }

    /**
     * Set disponible
     *
     * @param boolean $disponible
     * @return BonificacionExtra
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
     * Get idBonificacionExtra
     *
     * @return integer 
     */
    public function getIdBonificacionExtra()
    {
        return $this->idBonificacionExtra;
    }
}
