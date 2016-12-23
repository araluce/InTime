<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tuits
 *
 * @ORM\Table(name="tuits")
 * @ORM\Entity
 */
class Tuits
{
    /**
     * @var string
     *
     * @ORM\Column(name="usu_twitter", type="string", length=100, nullable=true)
     */
    private $usuTwitter;

    /**
     * @var string
     *
     * @ORM\Column(name="descripcion", type="string", length=100, nullable=true)
     */
    private $descripcion;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_tuit", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idTuit;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Usuario", inversedBy="idTuit")
     * @ORM\JoinTable(name="tuits_usuario",
     *   joinColumns={
     *     @ORM\JoinColumn(name="id_tuit", referencedColumnName="id_tuit")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="id_usuario", referencedColumnName="id_usuario")
     *   }
     * )
     */
    private $idUsuario;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->idUsuario = new \Doctrine\Common\Collections\ArrayCollection();
    }


    /**
     * Set usuTwitter
     *
     * @param string $usuTwitter
     *
     * @return Tuits
     */
    public function setUsuTwitter($usuTwitter)
    {
        $this->usuTwitter = $usuTwitter;

        return $this;
    }

    /**
     * Get usuTwitter
     *
     * @return string
     */
    public function getUsuTwitter()
    {
        return $this->usuTwitter;
    }

    /**
     * Set descripcion
     *
     * @param string $descripcion
     *
     * @return Tuits
     */
    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    /**
     * Get descripcion
     *
     * @return string
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }

    /**
     * Get idTuit
     *
     * @return integer
     */
    public function getIdTuit()
    {
        return $this->idTuit;
    }

    /**
     * Add idUsuario
     *
     * @param \AppBundle\Entity\Usuario $idUsuario
     *
     * @return Tuits
     */
    public function addIdUsuario(\AppBundle\Entity\Usuario $idUsuario)
    {
        $this->idUsuario[] = $idUsuario;

        return $this;
    }

    /**
     * Remove idUsuario
     *
     * @param \AppBundle\Entity\Usuario $idUsuario
     */
    public function removeIdUsuario(\AppBundle\Entity\Usuario $idUsuario)
    {
        $this->idUsuario->removeElement($idUsuario);
    }

    /**
     * Get idUsuario
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getIdUsuario()
    {
        return $this->idUsuario;
    }
}
