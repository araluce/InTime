<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Mensaje
 *
 * @ORM\Table(name="MENSAJE", indexes={@ORM\Index(name="id_tipo_mensaje", columns={"id_tipo_mensaje"})})
 * @ORM\Entity
 */
class Mensaje
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha", type="datetime", nullable=false)
     */
    private $fecha;

    /**
     * @var string
     *
     * @ORM\Column(name="titulo", type="string", length=1000, nullable=false)
     */
    private $titulo;

    /**
     * @var string
     *
     * @ORM\Column(name="mensaje", type="string", length=1000, nullable=false)
     */
    private $mensaje;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_mensaje", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idMensaje;

    /**
     * @var \AppBundle\Entity\TipoMensaje
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\TipoMensaje")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_tipo_mensaje", referencedColumnName="id_tipo_mensaje")
     * })
     */
    private $idTipoMensaje;



    /**
     * Set fecha
     *
     * @param \DateTime $fecha
     * @return Mensaje
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
     * Set titulo
     *
     * @param string $titulo
     * @return Mensaje
     */
    public function setTitulo($titulo)
    {
        $this->titulo = $titulo;

        return $this;
    }

    /**
     * Get titulo
     *
     * @return string 
     */
    public function getTitulo()
    {
        return $this->titulo;
    }

    /**
     * Set mensaje
     *
     * @param string $mensaje
     * @return Mensaje
     */
    public function setMensaje($mensaje)
    {
        $this->mensaje = $mensaje;

        return $this;
    }

    /**
     * Get mensaje
     *
     * @return string 
     */
    public function getMensaje()
    {
        return $this->mensaje;
    }

    /**
     * Get idMensaje
     *
     * @return integer 
     */
    public function getIdMensaje()
    {
        return $this->idMensaje;
    }

    /**
     * Set idTipoMensaje
     *
     * @param \AppBundle\Entity\TipoMensaje $idTipoMensaje
     * @return Mensaje
     */
    public function setIdTipoMensaje(\AppBundle\Entity\TipoMensaje $idTipoMensaje = null)
    {
        $this->idTipoMensaje = $idTipoMensaje;

        return $this;
    }

    /**
     * Get idTipoMensaje
     *
     * @return \AppBundle\Entity\TipoMensaje 
     */
    public function getIdTipoMensaje()
    {
        return $this->idTipoMensaje;
    }
}
