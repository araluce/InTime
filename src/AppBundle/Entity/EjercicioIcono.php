<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EjercicioIcono
 *
 * @ORM\Table(name="EJERCICIO_ICONO", indexes={@ORM\Index(name="seccion", columns={"seccion"})})
 * @ORM\Entity
 */
class EjercicioIcono
{
    /**
     * @var string
     *
     * @ORM\Column(name="nombre", type="string", length=1000, nullable=false)
     */
    private $nombre;

    /**
     * @var string
     *
     * @ORM\Column(name="nombre_img", type="string", length=1000, nullable=false)
     */
    private $nombreImg;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_ejercicio_icono", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idEjercicioIcono;

    /**
     * @var \AppBundle\Entity\EjercicioSeccion
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\EjercicioSeccion")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="seccion", referencedColumnName="id_ejercicio_seccion")
     * })
     */
    private $seccion;



    /**
     * Set nombre
     *
     * @param string $nombre
     * @return EjercicioIcono
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * Get nombre
     *
     * @return string 
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set nombreImg
     *
     * @param string $nombreImg
     * @return EjercicioIcono
     */
    public function setNombreImg($nombreImg)
    {
        $this->nombreImg = $nombreImg;

        return $this;
    }

    /**
     * Get nombreImg
     *
     * @return string 
     */
    public function getNombreImg()
    {
        return $this->nombreImg;
    }

    /**
     * Get idEjercicioIcono
     *
     * @return integer 
     */
    public function getIdEjercicioIcono()
    {
        return $this->idEjercicioIcono;
    }

    /**
     * Set seccion
     *
     * @param \AppBundle\Entity\EjercicioSeccion $seccion
     * @return EjercicioIcono
     */
    public function setSeccion(\AppBundle\Entity\EjercicioSeccion $seccion = null)
    {
        $this->seccion = $seccion;

        return $this;
    }

    /**
     * Get seccion
     *
     * @return \AppBundle\Entity\EjercicioSeccion 
     */
    public function getSeccion()
    {
        return $this->seccion;
    }
}
