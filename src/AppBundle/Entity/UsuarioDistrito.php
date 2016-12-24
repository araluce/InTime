<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UsuarioDistrito
 *
 * @ORM\Table(name="USUARIO_DISTRITO")
 * @ORM\Entity
 */
class UsuarioDistrito
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
     * @ORM\Column(name="id_usuario_distrito", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idUsuarioDistrito;



    /**
     * Set nombre
     *
     * @param string $nombre
     * @return UsuarioDistrito
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
     * Get idUsuarioDistrito
     *
     * @return integer 
     */
    public function getIdUsuarioDistrito()
    {
        return $this->idUsuarioDistrito;
    }
}
