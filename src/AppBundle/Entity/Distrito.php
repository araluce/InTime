<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Distrito
 *
 * @ORM\Table(name="DISTRITO")
 * @ORM\Entity
 */
class Distrito
{
    /**
     * @var string
     *
     * @ORM\Column(name="nombre", type="string", length=1000, nullable=false)
     */
    private $nombre;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_distrito", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idDistrito;



    /**
     * Set nombre
     *
     * @param string $nombre
     * @return Distrito
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
     * Get idDistrito
     *
     * @return integer 
     */
    public function getIdDistrito()
    {
        return $this->idDistrito;
    }
}
