<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UsuarioXDistrito
 *
 * @ORM\Table(name="USUARIO_X_DISTRITO", indexes={@ORM\Index(name="id_distrito", columns={"id_distrito", "id_usuario"})})
 * @ORM\Entity
 */
class UsuarioXDistrito
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_distrito", type="integer", nullable=false)
     */
    private $idDistrito;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_usuario", type="integer", nullable=false)
     */
    private $idUsuario;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_usuario_x_distrito", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idUsuarioXDistrito;



    /**
     * Set idDistrito
     *
     * @param integer $idDistrito
     *
     * @return UsuarioXDistrito
     */
    public function setIdDistrito($idDistrito)
    {
        $this->idDistrito = $idDistrito;

        return $this;
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

    /**
     * Set idUsuario
     *
     * @param integer $idUsuario
     *
     * @return UsuarioXDistrito
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
     * Get idUsuarioXDistrito
     *
     * @return integer
     */
    public function getIdUsuarioXDistrito()
    {
        return $this->idUsuarioXDistrito;
    }
}
