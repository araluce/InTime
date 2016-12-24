<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EjercicioSeccion
 *
 * @ORM\Table(name="EJERCICIO_SECCION")
 * @ORM\Entity
 */
class EjercicioSeccion
{
    /**
     * @var string
     *
     * @ORM\Column(name="seccion", type="string", length=100, nullable=false)
     */
    private $seccion;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_ejercicio_seccion", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idEjercicioSeccion;



    /**
     * Set seccion
     *
     * @param string $seccion
     * @return EjercicioSeccion
     */
    public function setSeccion($seccion)
    {
        $this->seccion = $seccion;

        return $this;
    }

    /**
     * Get seccion
     *
     * @return string 
     */
    public function getSeccion()
    {
        return $this->seccion;
    }

    /**
     * Get idEjercicioSeccion
     *
     * @return integer 
     */
    public function getIdEjercicioSeccion()
    {
        return $this->idEjercicioSeccion;
    }
}
