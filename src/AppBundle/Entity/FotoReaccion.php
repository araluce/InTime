<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FotoReaccion
 *
 * @ORM\Table(name="FOTO_REACCION", indexes={@ORM\Index(name="id_album_foto_2", columns={"id_album_foto"}), @ORM\Index(name="id_usuario", columns={"id_usuario"})})
 * @ORM\Entity
 */
class FotoReaccion
{
    /**
     * @var boolean
     *
     * @ORM\Column(name="like_social", type="boolean", nullable=false)
     */
    private $likeSocial;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_foto_reaccion", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idFotoReaccion;

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
     * @var \AppBundle\Entity\AlbumFoto
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\AlbumFoto")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_album_foto", referencedColumnName="id_album_foto")
     * })
     */
    private $idAlbumFoto;



    /**
     * Set likeSocial
     *
     * @param boolean $likeSocial
     * @return FotoReaccion
     */
    public function setLikeSocial($likeSocial)
    {
        $this->likeSocial = $likeSocial;

        return $this;
    }

    /**
     * Get likeSocial
     *
     * @return boolean 
     */
    public function getLikeSocial()
    {
        return $this->likeSocial;
    }

    /**
     * Get idFotoReaccion
     *
     * @return integer 
     */
    public function getIdFotoReaccion()
    {
        return $this->idFotoReaccion;
    }

    /**
     * Set idUsuario
     *
     * @param \AppBundle\Entity\Usuario $idUsuario
     * @return FotoReaccion
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
     * Set idAlbumFoto
     *
     * @param \AppBundle\Entity\AlbumFoto $idAlbumFoto
     * @return FotoReaccion
     */
    public function setIdAlbumFoto(\AppBundle\Entity\AlbumFoto $idAlbumFoto = null)
    {
        $this->idAlbumFoto = $idAlbumFoto;

        return $this;
    }

    /**
     * Get idAlbumFoto
     *
     * @return \AppBundle\Entity\AlbumFoto 
     */
    public function getIdAlbumFoto()
    {
        return $this->idAlbumFoto;
    }
}
