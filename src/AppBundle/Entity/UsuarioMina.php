<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UsuarioMina
 *
 * @ORM\Table(name="USUARIO_MINA", indexes={@ORM\Index(name="id_mina", columns={"id_mina", "id_usuario"}), @ORM\Index(name="id_usuario", columns={"id_usuario"}), @ORM\Index(name="IDX_534C0B8EE5E50B88", columns={"id_mina"})})
 * @ORM\Entity
 */
class UsuarioMina
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha", type="datetime", nullable=false)
     */
    private $fecha;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_usuario_mina", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idUsuarioMina;

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
     * @var \AppBundle\Entity\Mina
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Mina")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_mina", referencedColumnName="id_mina")
     * })
     */
    private $idMina;



    /**
     * Set fecha
     *
     * @param \DateTime $fecha
     * @return UsuarioMina
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
     * Get idUsuarioMina
     *
     * @return integer 
     */
    public function getIdUsuarioMina()
    {
        return $this->idUsuarioMina;
    }

    /**
     * Set idUsuario
     *
     * @param \AppBundle\Entity\Usuario $idUsuario
     * @return UsuarioMina
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
     * Set idMina
     *
     * @param \AppBundle\Entity\Mina $idMina
     * @return UsuarioMina
     */
    public function setIdMina(\AppBundle\Entity\Mina $idMina = null)
    {
        $this->idMina = $idMina;

        return $this;
    }

    /**
     * Get idMina
     *
     * @return \AppBundle\Entity\Mina 
     */
    public function getIdMina()
    {
        return $this->idMina;
    }
}
