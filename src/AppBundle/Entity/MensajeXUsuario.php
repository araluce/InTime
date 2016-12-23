<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MensajeXUsuario
 *
 * @ORM\Table(name="MENSAJE_X_USUARIO", indexes={@ORM\Index(name="id_usuario", columns={"id_usuario", "id_mensaje"}), @ORM\Index(name="id_mensaje", columns={"id_mensaje"}), @ORM\Index(name="IDX_3E5D7D13FCF8192D", columns={"id_usuario"})})
 * @ORM\Entity
 */
class MensajeXUsuario
{
    /**
     * @var boolean
     *
     * @ORM\Column(name="visto", type="boolean", nullable=false)
     */
    private $visto = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\Mensaje
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Mensaje")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_mensaje", referencedColumnName="id_mensaje")
     * })
     */
    private $idMensaje;

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
     * Set visto
     *
     * @param boolean $visto
     *
     * @return MensajeXUsuario
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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set idMensaje
     *
     * @param \AppBundle\Entity\Mensaje $idMensaje
     *
     * @return MensajeXUsuario
     */
    public function setIdMensaje(\AppBundle\Entity\Mensaje $idMensaje = null)
    {
        $this->idMensaje = $idMensaje;

        return $this;
    }

    /**
     * Get idMensaje
     *
     * @return \AppBundle\Entity\Mensaje
     */
    public function getIdMensaje()
    {
        return $this->idMensaje;
    }

    /**
     * Set idUsuario
     *
     * @param \AppBundle\Entity\Usuario $idUsuario
     *
     * @return MensajeXUsuario
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
