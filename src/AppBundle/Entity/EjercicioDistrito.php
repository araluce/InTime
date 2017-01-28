<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EjercicioDistrito
 *
 * @ORM\Table(name="EJERCICIO_DISTRITO", indexes={@ORM\Index(name="id_ejercicio", columns={"id_ejercicio"})})
 * @ORM\Entity
 */
class EjercicioDistrito
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_ejercicio_distrito", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idEjercicioDistrito;

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
     * Get idEjercicioDistrito
     *
     * @return integer 
     */
    public function getIdEjercicioDistrito()
    {
        return $this->idEjercicioDistrito;
    }

    /**
     * Set idEjercicio
     *
     * @param \AppBundle\Entity\Ejercicio $idEjercicio
     * @return EjercicioDistrito
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
