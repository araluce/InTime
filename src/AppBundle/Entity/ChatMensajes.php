<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ChatMensajes
 *
 * @ORM\Table(name="CHAT_MENSAJES", indexes={@ORM\Index(name="id_chat", columns={"id_chat"}), @ORM\Index(name="id_usuario", columns={"id_usuario"})})
 * @ORM\Entity
 */
class ChatMensajes
{
    /**
     * @var string
     *
     * @ORM\Column(name="mensaje", type="string", length=1000, nullable=false)
     */
    private $mensaje;

    /**
     * @var boolean
     *
     * @ORM\Column(name="visto", type="boolean", nullable=false)
     */
    private $visto = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha", type="datetime", nullable=false)
     */
    private $fecha = 'CURRENT_TIMESTAMP';

    /**
     * @var integer
     *
     * @ORM\Column(name="id_chat_mensajes", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idChatMensajes;

    /**
     * @var \AppBundle\Entity\Chat
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Chat")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_chat", referencedColumnName="id_chat")
     * })
     */
    private $idChat;

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
     * Set mensaje
     *
     * @param string $mensaje
     *
     * @return ChatMensajes
     */
    public function setMensaje($mensaje)
    {
        $this->mensaje = $mensaje;

        return $this;
    }

    /**
     * Get mensaje
     *
     * @return string
     */
    public function getMensaje()
    {
        return $this->mensaje;
    }

    /**
     * Set visto
     *
     * @param boolean $visto
     *
     * @return ChatMensajes
     */
    public function setVisto($visto)
    {
        $this->visto = $visto;

        return $this;
    }

    /**
     * Get visto
     *
     * @return boolean
     */
    public function getVisto()
    {
        return $this->visto;
    }

    /**
     * Set fecha
     *
     * @param \DateTime $fecha
     *
     * @return ChatMensajes
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
     * Get idChatMensajes
     *
     * @return integer
     */
    public function getIdChatMensajes()
    {
        return $this->idChatMensajes;
    }

    /**
     * Set idChat
     *
     * @param \AppBundle\Entity\Chat $idChat
     *
     * @return ChatMensajes
     */
    public function setIdChat(\AppBundle\Entity\Chat $idChat = null)
    {
        $this->idChat = $idChat;

        return $this;
    }

    /**
     * Get idChat
     *
     * @return \AppBundle\Entity\Chat
     */
    public function getIdChat()
    {
        return $this->idChat;
    }

    /**
     * Set idUsuario
     *
     * @param \AppBundle\Entity\Usuario $idUsuario
     *
     * @return ChatMensajes
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
