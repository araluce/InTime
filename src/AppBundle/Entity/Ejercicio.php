<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ejercicio
 *
 * @ORM\Table(name="EJERCICIO", indexes={@ORM\Index(name="id_tipo_ejercicio", columns={"id_tipo_ejercicio"}), @ORM\Index(name="id_ejercicio_seccion", columns={"id_ejercicio_seccion"}), @ORM\Index(name="icono", columns={"icono"})})
 * @ORM\Entity
 */
class Ejercicio
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
     * @ORM\Column(name="enunciado", type="string", length=1000, nullable=false)
     */
    private $enunciado;

    /**
     * @var integer
     *
     * @ORM\Column(name="coste", type="integer", nullable=false)
     */
    private $coste;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_ejercicio", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idEjercicio;

    /**
     * @var \AppBundle\Entity\EjercicioTipo
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\EjercicioTipo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_tipo_ejercicio", referencedColumnName="id_tipo_ejercicio")
     * })
     */
    private $idTipoEjercicio;

    /**
     * @var \AppBundle\Entity\EjercicioSeccion
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\EjercicioSeccion")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_ejercicio_seccion", referencedColumnName="id_ejercicio_seccion")
     * })
     */
    private $idEjercicioSeccion;

    /**
     * @var \AppBundle\Entity\EjercicioIcono
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\EjercicioIcono")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="icono", referencedColumnName="id_ejercicio_icono")
     * })
     */
    private $icono;



    /**
     * Set fecha
     *
     * @param \DateTime $fecha
     * @return Ejercicio
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
     * Set enunciado
     *
     * @param string $enunciado
     * @return Ejercicio
     */
    public function setEnunciado($enunciado)
    {
        $this->enunciado = $enunciado;

        return $this;
    }

    /**
     * Get enunciado
     *
     * @return string 
     */
    public function getEnunciado()
    {
        return $this->enunciado;
    }

    /**
     * Set coste
     *
     * @param integer $coste
     * @return Ejercicio
     */
    public function setCoste($coste)
    {
        $this->coste = $coste;

        return $this;
    }

    /**
     * Get coste
     *
     * @return integer 
     */
    public function getCoste()
    {
        return $this->coste;
    }

    /**
     * Get idEjercicio
     *
     * @return integer 
     */
    public function getIdEjercicio()
    {
        return $this->idEjercicio;
    }

    /**
     * Set idTipoEjercicio
     *
     * @param \AppBundle\Entity\EjercicioTipo $idTipoEjercicio
     * @return Ejercicio
     */
    public function setIdTipoEjercicio(\AppBundle\Entity\EjercicioTipo $idTipoEjercicio = null)
    {
        $this->idTipoEjercicio = $idTipoEjercicio;

        return $this;
    }

    /**
     * Get idTipoEjercicio
     *
     * @return \AppBundle\Entity\EjercicioTipo 
     */
    public function getIdTipoEjercicio()
    {
        return $this->idTipoEjercicio;
    }

    /**
     * Set idEjercicioSeccion
     *
     * @param \AppBundle\Entity\EjercicioSeccion $idEjercicioSeccion
     * @return Ejercicio
     */
    public function setIdEjercicioSeccion(\AppBundle\Entity\EjercicioSeccion $idEjercicioSeccion = null)
    {
        $this->idEjercicioSeccion = $idEjercicioSeccion;

        return $this;
    }

    /**
     * Get idEjercicioSeccion
     *
     * @return \AppBundle\Entity\EjercicioSeccion 
     */
    public function getIdEjercicioSeccion()
    {
        return $this->idEjercicioSeccion;
    }

    /**
     * Set icono
     *
     * @param \AppBundle\Entity\EjercicioIcono $icono
     * @return Ejercicio
     */
    public function setIcono(\AppBundle\Entity\EjercicioIcono $icono = null)
    {
        $this->icono = $icono;

        return $this;
    }

    /**
     * Get icono
     *
     * @return \AppBundle\Entity\EjercicioIcono 
     */
    public function getIcono()
    {
        return $this->icono;
    }
}
