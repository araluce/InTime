<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Twit
 *
 * @ORM\Table(name="TWIT", indexes={@ORM\Index(name="id_twittero", columns={"id_twittero"})})
 * @ORM\Entity
 */
class Twit
{
    /**
     * @var string
     *
     * @ORM\Column(name="id_twittero", type="string", length=30, nullable=false)
     */
    private $idTwittero;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha", type="datetime", nullable=false)
     */
    private $fecha;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_twit", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idTwit;



    /**
     * Set idTwittero
     *
     * @param string $idTwittero
     * @return Twit
     */
    public function setIdTwittero($idTwittero)
    {
        $this->idTwittero = $idTwittero;

        return $this;
    }

    /**
     * Get idTwittero
     *
     * @return string 
     */
    public function getIdTwittero()
    {
        return $this->idTwittero;
    }

    /**
     * Set fecha
     *
     * @param \DateTime $fecha
     * @return Twit
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
     * Get idTwit
     *
     * @return integer 
     */
    public function getIdTwit()
    {
        return $this->idTwit;
    }
}
