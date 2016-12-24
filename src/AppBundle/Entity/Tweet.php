<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tweet
 *
 * @ORM\Table(name="TWEET", indexes={@ORM\Index(name="id_twittero", columns={"id_tuitero"}), @ORM\Index(name="id_tweet", columns={"id_tweet"})})
 * @ORM\Entity
 */
class Tweet
{
    /**
     * @var string
     *
     * @ORM\Column(name="id_tuitero", type="string", length=30, nullable=false)
     */
    private $idTuitero;

    /**
     * @var string
     *
     * @ORM\Column(name="fecha", type="string", length=100, nullable=false)
     */
    private $fecha;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_tweet", type="bigint", nullable=false)
     */
    private $idTweet;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;



    /**
     * Set idTuitero
     *
     * @param string $idTuitero
     * @return Tweet
     */
    public function setIdTuitero($idTuitero)
    {
        $this->idTuitero = $idTuitero;

        return $this;
    }

    /**
     * Get idTuitero
     *
     * @return string 
     */
    public function getIdTuitero()
    {
        return $this->idTuitero;
    }

    /**
     * Set fecha
     *
     * @param string $fecha
     * @return Tweet
     */
    public function setFecha($fecha)
    {
        $this->fecha = $fecha;

        return $this;
    }

    /**
     * Get fecha
     *
     * @return string 
     */
    public function getFecha()
    {
        return $this->fecha;
    }

    /**
     * Set idTweet
     *
     * @param integer $idTweet
     * @return Tweet
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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
}
