<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EjercicioEstado
 *
 * @ORM\Table(name="EJERCICIO_ESTADO", uniqueConstraints={@ORM\UniqueConstraint(name="estado", columns={"estado"})})
 * @ORM\Entity
 */
class EjercicioEstado
{
    /**
     * @var string
     *
     * @ORM\Column(name="estado", type="string", length=100, nullable=false)
     */
    private $estado;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_ejercicio_estado", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idEjercicioEstado;



    /**
     * Set estado
     *
     * @param string $estado
     *
     * @return EjercicioEstado
     */
    public function setEstado($estado)
    {
        $this->estado = $estado;

        return $this;
    }

    /**
     * Get estado
     *
     * @return string
     */
    public function getEstado()
    {
        return $this->estado;
    }

    /**
     * Get idEjercicioEstado
     *
     * @return integer
     */
    public function getIdEjercicioEstado()
    {
        return $this->idEjercicioEstado;
    }
}
