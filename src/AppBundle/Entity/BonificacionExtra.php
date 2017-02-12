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
     * @ORM\Column(name="imagen", type="string", length=100, nullable=false)
     */
    private $imagen;

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
     * Get idBonificacionExtra
     *
     * @return integer 
     */
    public function getIdBonificacionExtra()
    {
        return $this->idBonificacionExtra;
    }
}
