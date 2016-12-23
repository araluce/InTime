<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Chat
 *
 * @ORM\Table(name="CHAT", indexes={@ORM\Index(name="id_usuario_1", columns={"id_usuario_1", "id_usuario_2"}), @ORM\Index(name="id_usuario_2", columns={"id_usuario_2"}), @ORM\Index(name="id_distrito", columns={"id_distrito"}), @ORM\Index(name="IDX_53081F1E69655D89", columns={"id_usuario_1"})})
 * @ORM\Entity
 */
class Chat
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha", type="datetime", nullable=false)
     */
    private $fecha = 'CURRENT_TIMESTAMP';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_ultimo_mensaje", type="datetime", nullable=true)
     */
    private $fechaUltimoMensaje;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_chat", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idChat;

    /**
     * @var \AppBundle\Entity\UsuarioDistrito
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\UsuarioDistrito")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_distrito", referencedColumnName="id_usuario_distrito")
     * })
     */
    private $idDistrito;

    /**
     * @var \AppBundle\Entity\Usuario
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Usuario")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_usuario_2", referencedColumnName="id_usuario")
     * })
     */
    private $idUsuario2;

    /**
     * @var \AppBundle\Entity\Usuario
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Usuario")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_usuario_1", referencedColumnName="id_usuario")
     * })
     */
    private $idUsuario1;



    /**
     * Set fecha
     *
     * @param \DateTime $fecha
     *
     * @return Chat
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
     * Set fechaUltimoMensaje
     *
     * @param \DateTime $fechaUltimoMensaje
     *
     * @return Chat
     */
    public function setFechaUltimoMensaje($fechaUltimoMensaje)
    {
        $this->fechaUltimoMensaje = $fechaUltimoMensaje;

        return $this;
    }

    /**
     * Get fechaUltimoMensaje
     *
     * @return \DateTime
     */
    public function getFechaUltimoMensaje()
    {
        return $this->fechaUltimoMensaje;
    }

    /**
     * Get idChat
     *
     * @return integer
     */
    public function getIdChat()
    {
        return $this->idChat;
    }

    /**
     * Set idDistrito
     *
     * @param \AppBundle\Entity\UsuarioDistrito $idDistrito
     *
     * @return Chat
     */
    public function setIdDistrito(\AppBundle\Entity\UsuarioDistrito $idDistrito = null)
    {
        $this->idDistrito = $idDistrito;

        return $this;
    }

    /**
     * Get idDistrito
     *
     * @return \AppBundle\Entity\UsuarioDistrito
     */
    public function getIdDistrito()
    {
        return $this->idDistrito;
    }

    /**
     * Set idUsuario2
     *
     * @param \AppBundle\Entity\Usuario $idUsuario2
     *
     * @return Chat
     */
    public function setIdUsuario2(\AppBundle\Entity\Usuario $idUsuario2 = null)
    {
        $this->idUsuario2 = $idUsuario2;

        return $this;
    }

    /**
     * Get idUsuario2
     *
     * @return \AppBundle\Entity\Usuario
     */
    public function getIdUsuario2()
    {
        return $this->idUsuario2;
    }

    /**
     * Set idUsuario1
     *
     * @param \AppBundle\Entity\Usuario $idUsuario1
     *
     * @return Chat
     */
    public function setIdUsuario1(\AppBundle\Entity\Usuario $idUsuario1 = null)
    {
        $this->idUsuario1 = $idUsuario1;

        return $this;
    }

    /**
     * Get idUsuario1
     *
     * @return \AppBundle\Entity\Usuario
     */
    public function getIdUsuario1()
    {
        return $this->idUsuario1;
    }
}
