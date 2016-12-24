<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Calendario
 *
 * @ORM\Table(name="calendario", indexes={@ORM\Index(name="id_usuario", columns={"id_usuario"}), @ORM\Index(name="id_usuario_2", columns={"id_usuario"})})
 * @ORM\Entity
 */
class Calendario
{
    /**
     * @var string
     *
     * @ORM\Column(name="contenido", type="string", length=1000, nullable=false)
     */
    private $contenido;

    /**
     * @var string
     *
     * @ORM\Column(name="semana_ini", type="string", length=100, nullable=true)
     */
    private $semanaIni;

    /**
     * @var string
     *
     * @ORM\Column(name="semana_fin", type="string", length=100, nullable=true)
     */
    private $semanaFin;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_calendario", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idCalendario;

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
     * Set contenido
     *
     * @param string $contenido
     * @return Calendario
     */
    public function setContenido($contenido)
    {
        $this->contenido = $contenido;

        return $this;
    }

    /**
     * Get contenido
     *
     * @return string 
     */
    public function getContenido()
    {
        return $this->contenido;
    }

    /**
     * Set semanaIni
     *
     * @param string $semanaIni
     * @return Calendario
     */
    public function setSemanaIni($semanaIni)
    {
        $this->semanaIni = $semanaIni;

        return $this;
    }

    /**
     * Get semanaIni
     *
     * @return string 
     */
    public function getSemanaIni()
    {
        return $this->semanaIni;
    }

    /**
     * Set semanaFin
     *
     * @param string $semanaFin
     * @return Calendario
     */
    public function setSemanaFin($semanaFin)
    {
        $this->semanaFin = $semanaFin;

        return $this;
    }

    /**
     * Get semanaFin
     *
     * @return string 
     */
    public function getSemanaFin()
    {
        return $this->semanaFin;
    }

    /**
     * Get idCalendario
     *
     * @return integer 
     */
    public function getIdCalendario()
    {
        return $this->idCalendario;
    }

    /**
     * Set idUsuario
     *
     * @param \AppBundle\Entity\Usuario $idUsuario
     * @return Calendario
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
