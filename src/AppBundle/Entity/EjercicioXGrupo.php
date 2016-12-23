<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EjercicioXGrupo
 *
 * @ORM\Table(name="EJERCICIO_X_GRUPO", indexes={@ORM\Index(name="id_ejercicio", columns={"id_ejercicio"}), @ORM\Index(name="id_grupo_ejercicios", columns={"id_grupo_ejercicios"})})
 * @ORM\Entity
 */
class EjercicioXGrupo
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_ejercicio_x_grupo", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idEjercicioXGrupo;

    /**
     * @var \AppBundle\Entity\GrupoEjercicios
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\GrupoEjercicios")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_grupo_ejercicios", referencedColumnName="id_grupo_ejercicios")
     * })
     */
    private $idGrupoEjercicios;

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
     * Get idEjercicioXGrupo
     *
     * @return integer
     */
    public function getIdEjercicioXGrupo()
    {
        return $this->idEjercicioXGrupo;
    }

    /**
     * Set idGrupoEjercicios
     *
     * @param \AppBundle\Entity\GrupoEjercicios $idGrupoEjercicios
     *
     * @return EjercicioXGrupo
     */
    public function setIdGrupoEjercicios(\AppBundle\Entity\GrupoEjercicios $idGrupoEjercicios = null)
    {
        $this->idGrupoEjercicios = $idGrupoEjercicios;

        return $this;
    }

    /**
     * Get idGrupoEjercicios
     *
     * @return \AppBundle\Entity\GrupoEjercicios
     */
    public function getIdGrupoEjercicios()
    {
        return $this->idGrupoEjercicios;
    }

    /**
     * Set idEjercicio
     *
     * @param \AppBundle\Entity\Ejercicio $idEjercicio
     *
     * @return EjercicioXGrupo
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
}
