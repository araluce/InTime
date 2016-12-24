<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MochilaTweets
 *
 * @ORM\Table(name="MOCHILA_TWEETS", indexes={@ORM\Index(name="id_tweet", columns={"id_tweet"}), @ORM\Index(name="id_usuario", columns={"id_usuario"}), @ORM\Index(name="id_usuario_destino", columns={"id_usuario_destino"}), @ORM\Index(name="id_tipo_tweet", columns={"id_tipo_tweet"}), @ORM\Index(name="id_tweet_2", columns={"id_tweet"})})
 * @ORM\Entity
 */
class MochilaTweets
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_tweet", type="bigint", nullable=false)
     */
    private $idTweet;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha", type="datetime", nullable=true)
     */
    private $fecha;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_mochila", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idMochila;

    /**
     * @var \AppBundle\Entity\TipoTweet
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\TipoTweet")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_tipo_tweet", referencedColumnName="id")
     * })
     */
    private $idTipoTweet;

    /**
     * @var \AppBundle\Entity\Usuario
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Usuario")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_usuario_destino", referencedColumnName="id_usuario")
     * })
     */
    private $idUsuarioDestino;

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
     * Set idTweet
     *
     * @param integer $idTweet
     * @return MochilaTweets
     */
    public function setIdTweet($idTweet)
    {
        $this->idTweet = $idTweet;

        return $this;
    }

    /**
     * Get idTweet
     *
     * @return integer 
     */
    public function getIdTweet()
    {
        return $this->idTweet;
    }

    /**
     * Set fecha
     *
     * @param \DateTime $fecha
     * @return MochilaTweets
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
     * Get idMochila
     *
     * @return integer 
     */
    public function getIdMochila()
    {
        return $this->idMochila;
    }

    /**
     * Set idTipoTweet
     *
     * @param \AppBundle\Entity\TipoTweet $idTipoTweet
     * @return MochilaTweets
     */
    public function setIdTipoTweet(\AppBundle\Entity\TipoTweet $idTipoTweet = null)
    {
        $this->idTipoTweet = $idTipoTweet;

        return $this;
    }

    /**
     * Get idTipoTweet
     *
     * @return \AppBundle\Entity\TipoTweet 
     */
    public function getIdTipoTweet()
    {
        return $this->idTipoTweet;
    }

    /**
     * Set idUsuarioDestino
     *
     * @param \AppBundle\Entity\Usuario $idUsuarioDestino
     * @return MochilaTweets
     */
    public function setIdUsuarioDestino(\AppBundle\Entity\Usuario $idUsuarioDestino = null)
    {
        $this->idUsuarioDestino = $idUsuarioDestino;

        return $this;
    }

    /**
     * Get idUsuarioDestino
     *
     * @return \AppBundle\Entity\Usuario 
     */
    public function getIdUsuarioDestino()
    {
        return $this->idUsuarioDestino;
    }

    /**
     * Set idUsuario
     *
     * @param \AppBundle\Entity\Usuario $idUsuario
     * @return MochilaTweets
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
