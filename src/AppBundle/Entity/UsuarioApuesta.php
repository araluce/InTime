<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UsuarioApuesta
 *
 * @ORM\Table(name="USUARIO_APUESTA", indexes={@ORM\Index(name="id_usuario", columns={"id_usuario", "id_apuesta_posibilidad"}), @ORM\Index(name="id_apuesta_posibilidad", columns={"id_apuesta_posibilidad"}), @ORM\Index(name="IDX_42A94AE7FCF8192D", columns={"id_usuario"})})
 * @ORM\Entity
 */
class UsuarioApuesta
{
    /**
     * @var integer
     *
     * @ORM\Column(name="tdv_apostado", type="bigint", nullable=false)
     */
    private $tdvApostado;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_usuario_apuesta", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idUsuarioApuesta;

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
     * @var \AppBundle\Entity\ApuestaPosibilidad
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\ApuestaPosibilidad")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_apuesta_posibilidad", referencedColumnName="id_apuesta_posibilidad")
     * })
     */
    private $idApuestaPosibilidad;



    /**
     * Set tdvApostado
     *
     * @param integer $tdvApostado
     *
     * @return UsuarioApuesta
     */
    public function setTdvApostado($tdvApostado)
    {
        $this->tdvApostado = $tdvApostado;

        return $this;
    }

    /**
     * Get tdvApostado
     *
     * @return integer
     */
    public function getTdvApostado()
    {
        return $this->tdvApostado;
    }

    /**
     * Get idUsuarioApuesta
     *
     * @return integer
     */
    public function getIdUsuarioApuesta()
    {
        return $this->idUsuarioApuesta;
    }

    /**
     * Set idUsuario
     *
     * @param \AppBundle\Entity\Usuario $idUsuario
     *
     * @return UsuarioApuesta
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
     * Set idApuestaPosibilidad
     *
     * @param \AppBundle\Entity\ApuestaPosibilidad $idApuestaPosibilidad
     *
     * @return UsuarioApuesta
     */
    public function setIdApuestaPosibilidad(\AppBundle\Entity\ApuestaPosibilidad $idApuestaPosibilidad = null)
    {
        $this->idApuestaPosibilidad = $idApuestaPosibilidad;

        return $this;
    }

    /**
     * Get idApuestaPosibilidad
     *
     * @return \AppBundle\Entity\ApuestaPosibilidad
     */
    public function getIdApuestaPosibilidad()
    {
        return $this->idApuestaPosibilidad;
    }
}
