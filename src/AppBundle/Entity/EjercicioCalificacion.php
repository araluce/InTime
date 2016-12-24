<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EjercicioCalificacion
 *
 * @ORM\Table(name="EJERCICIO_CALIFICACION", indexes={@ORM\Index(name="id_usuario", columns={"id_usuario"}), @ORM\Index(name="id_grupo", columns={"id_grupo"}), @ORM\Index(name="id_ejercicio", columns={"id_ejercicio"}), @ORM\Index(name="id_calificaciones", columns={"id_calificaciones"}), @ORM\Index(name="id_evaluador", columns={"id_evaluador"}), @ORM\Index(name="id_ejercicio_estado", columns={"id_ejercicio_estado"})})
 * @ORM\Entity
 */
class EjercicioCalificacion
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
     * @ORM\Column(name="id_ejercicio_calificacion", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idEjercicioCalificacion;

    /**
     * @var \AppBundle\Entity\GrupoEjercicios
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\GrupoEjercicios")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_grupo", referencedColumnName="id_grupo_ejercicios")
     * })
     */
    private $idGrupo;

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
     * @var \AppBundle\Entity\Usuario
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Usuario")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_evaluador", referencedColumnName="id_usuario")
     * })
     */
    private $idEvaluador;

    /**
     * @var \AppBundle\Entity\EjercicioEstado
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\EjercicioEstado")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_ejercicio_estado", referencedColumnName="id_ejercicio_estado")
     * })
     */
    private $idEjercicioEstado;

    /**
     * @var \AppBundle\Entity\Ejercicio
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Ejercicio")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_ejercicio", referencedColumnName="id_ejercicio")
     * })
     */
    private $idEjercicio;

    /**
     * @var \AppBundle\Entity\Calificaciones
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Calificaciones")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_calificaciones", referencedColumnName="id_calificaciones")
     * })
     */
    private $idCalificaciones;



    /**
     * Set fecha
     *
     * @param \DateTime $fecha
     * @return EjercicioCalificacion
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
     * Get idEjercicioCalificacion
     *
     * @return integer 
     */
    public function getIdEjercicioCalificacion()
    {
        return $this->idEjercicioCalificacion;
    }

    /**
     * Set idGrupo
     *
     * @param \AppBundle\Entity\GrupoEjercicios $idGrupo
     * @return EjercicioCalificacion
     */
    public function setIdGrupo(\AppBundle\Entity\GrupoEjercicios $idGrupo = null)
    {
        $this->idGrupo = $idGrupo;

        return $this;
    }

    /**
     * Get idGrupo
     *
     * @return \AppBundle\Entity\GrupoEjercicios 
     */
    public function getIdGrupo()
    {
        return $this->idGrupo;
    }

    /**
     * Set idUsuario
     *
     * @param \AppBundle\Entity\Usuario $idUsuario
     * @return EjercicioCalificacion
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
     * Set idEvaluador
     *
     * @param \AppBundle\Entity\Usuario $idEvaluador
     * @return EjercicioCalificacion
     */
    public function setIdEvaluador(\AppBundle\Entity\Usuario $idEvaluador = null)
    {
        $this->idEvaluador = $idEvaluador;

        return $this;
    }

    /**
     * Get idEvaluador
     *
     * @return \AppBundle\Entity\Usuario 
     */
    public function getIdEvaluador()
    {
        return $this->idEvaluador;
    }

    /**
     * Set idEjercicioEstado
     *
     * @param \AppBundle\Entity\EjercicioEstado $idEjercicioEstado
     * @return EjercicioCalificacion
     */
    public function setIdEjercicioEstado(\AppBundle\Entity\EjercicioEstado $idEjercicioEstado = null)
    {
        $this->idEjercicioEstado = $idEjercicioEstado;

        return $this;
    }

    /**
     * Get idEjercicioEstado
     *
     * @return \AppBundle\Entity\EjercicioEstado 
     */
    public function getIdEjercicioEstado()
    {
        return $this->idEjercicioEstado;
    }

    /**
     * Set idEjercicio
     *
     * @param \AppBundle\Entity\Ejercicio $idEjercicio
     * @return EjercicioCalificacion
     */
    public function setIdEjercicio(\AppBundle\Entity\Ejercicio $idEjercicio = null)
    {
        $this->idEjercicio = $idEjercicio;

        return $this;
    }

    /**
     * Get idEjercicio
     *
     * @return \AppBundle\Entity\Ejercicio 
     */
    public function getIdEjercicio()
    {
        return $this->idEjercicio;
    }

    /**
     * Set idCalificaciones
     *
     * @param \AppBundle\Entity\Calificaciones $idCalificaciones
     * @return EjercicioCalificacion
     */
    public function setIdCalificaciones(\AppBundle\Entity\Calificaciones $idCalificaciones = null)
    {
        $this->idCalificaciones = $idCalificaciones;

        return $this;
    }

    /**
     * Get idCalificaciones
     *
     * @return \AppBundle\Entity\Calificaciones 
     */
    public function getIdCalificaciones()
    {
        return $this->idCalificaciones;
    }
}
