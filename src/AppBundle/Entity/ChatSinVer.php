<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ChatSinVer
 *
 * @ORM\Table(name="CHAT_SIN_VER", indexes={@ORM\Index(name="id_chat", columns={"id_chat", "id_usuario"}), @ORM\Index(name="id_usuario", columns={"id_usuario"}), @ORM\Index(name="IDX_ACFE4E83EEBDEEA8", columns={"id_chat"})})
 * @ORM\Entity
 */
class ChatSinVer
{
    /**
     * @var integer
     *
     * @ORM\Column(name="cantidad", type="integer", nullable=false)
     */
    private $cantidad;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_chat_sin_ver", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idChatSinVer;

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
     * @var \AppBundle\Entity\Chat
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Chat")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_chat", referencedColumnName="id_chat")
     * })
     */
    private $idChat;



    /**
     * Set cantidad
     *
     * @param integer $cantidad
     * @return ChatSinVer
     */
    public function setCantidad($cantidad)
    {
        $this->cantidad = $cantidad;

        return $this;
    }

    /**
     * Get cantidad
     *
     * @return integer 
     */
    public function getCantidad()
    {
        return $this->cantidad;
    }

    /**
     * Get idChatSinVer
     *
     * @return integer 
     */
    public function getIdChatSinVer()
    {
        return $this->idChatSinVer;
    }

    /**
     * Set idUsuario
     *
     * @param \AppBundle\Entity\Usuario $idUsuario
     * @return ChatSinVer
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
     * Set idChat
     *
     * @param \AppBundle\Entity\Chat $idChat
     * @return ChatSinVer
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
}
