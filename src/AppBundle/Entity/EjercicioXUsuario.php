<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EjercicioXUsuario
 *
 * @ORM\Table(name="EJERCICIO_X_USUARIO", indexes={@ORM\Index(name="id_ejercicio", columns={"id_ejercicio", "id_usu"}), @ORM\Index(name="id_seccion", columns={"id_seccion"}), @ORM\Index(name="id_usu", columns={"id_usu"}), @ORM\Index(name="id_grupo", columns={"id_grupo"}), @ORM\Index(name="IDX_B618B30D159873D3", columns={"id_ejercicio"})})
 * @ORM\Entity
 */
class EjercicioXUsuario
{
    /**
     * @var boolean
     *
     * @ORM\Column(name="visto", type="boolean", nullable=false)
     */
    private $visto = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="id_ejercicio_x_usuario", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idEjercicioXUsuario;

    /**
     * @var \AppBundle\Entity\Usuario
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Usuario")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_usu", referencedColumnName="id_usuario")
     * })
     */
    private $idUsu;

    /**
     * @var \AppBundle\Entity\EjercicioSeccion
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\EjercicioSeccion")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_seccion", referencedColumnName="id_ejercicio_seccion")
     * })
     */
    private $idSeccion;

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
     * @var \AppBundle\Entity\Ejercicio
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Ejercicio")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_ejercicio", referencedColumnName="id_ejercicio")
     * })
     */
    private $idEjercicio;



    /**
     * Set visto
     *
     * @param boolean $visto
     *
     * @return EjercicioXUsuario
     */
    public function setVisto($visto)
    {
        $this->visto = $visto;

        return $this;
    }

    /**
     * Get visto
     *
     * @return boolean
     */
    public function getVisto()
    {
        return $this->visto;
    }

    /**
     * Get idEjercicioXUsuario
     *
     * @return integer
     */
    public function getIdEjercicioXUsuario()
    {
        return $this->idEjercicioXUsuario;
    }

    /**
     * Set idUsu
     *
     * @param \AppBundle\Entity\Usuario $idUsu
     *
     * @return EjercicioXUsuario
     */
    public function setIdUsu(\AppBundle\Entity\Usuario $idUsu = null)
    {
        $this->idUsu = $idUsu;

        return $this;
    }

    /**
     * Get idUsu
     *
     * @return \AppBundle\Entity\Usuario
     */
    public function getIdUsu()
    {
        return $this->idUsu;
    }

    /**
     * Set idSeccion
     *
     * @param \AppBundle\Entity\EjercicioSeccion $idSeccion
     *
     * @return EjercicioXUsuario
     */
    public function setIdSeccion(\AppBundle\Entity\EjercicioSeccion $idSeccion = null)
    {
        $this->idSeccion = $idSeccion;

        return $this;
    }

    /**
     * Get idSeccion
     *
     * @return \AppBundle\Entity\EjercicioSeccion
     */
    public function getIdSeccion()
    {
        return $this->idSeccion;
    }

    /**
     * Set idGrupo
     *
     * @param \AppBundle\Entity\GrupoEjercicios $idGrupo
     *
     * @return EjercicioXUsuario
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
     * Set idEjercicio
     *
     * @param \AppBundle\Entity\Ejercicio $idEjercicio
     *
     * @return EjercicioXUsuario
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
