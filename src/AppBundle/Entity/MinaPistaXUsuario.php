<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MinaPistaXUsuario
 *
 * @ORM\Table(name="MINA_PISTA_X_USUARIO", indexes={@ORM\Index(name="id_mina_pista", columns={"id_mina_pista", "id_usuario"}), @ORM\Index(name="id_usuario", columns={"id_usuario"}), @ORM\Index(name="IDX_5096715B1F0C7C17", columns={"id_mina_pista"})})
 * @ORM\Entity
 */
class MinaPistaXUsuario
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_mina_pista_x_usuario", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idMinaPistaXUsuario;

    /**
     * @var \AppBundle\Entity\MinaPista
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\MinaPista")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_mina_pista", referencedColumnName="id_mina_pista")
     * })
     */
    private $idMinaPista;

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
     * Get idMinaPistaXUsuario
     *
     * @return integer 
     */
    public function getIdMinaPistaXUsuario()
    {
        return $this->idMinaPistaXUsuario;
    }

    /**
     * Set idMinaPista
     *
     * @param \AppBundle\Entity\MinaPista $idMinaPista
     * @return MinaPistaXUsuario
     */
    public function setIdMinaPista(\AppBundle\Entity\MinaPista $idMinaPista = null)
    {
        $this->idMinaPista = $idMinaPista;

        return $this;
    }

    /**
     * Get idMinaPista
     *
     * @return \AppBundle\Entity\MinaPista 
     */
    public function getIdMinaPista()
    {
        return $this->idMinaPista;
    }

    /**
     * Set idUsuario
     *
     * @param \AppBundle\Entity\Usuario $idUsuario
     * @return MinaPistaXUsuario
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
