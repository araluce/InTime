<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EjercicioTipo
 *
 * @ORM\Table(name="EJERCICIO_TIPO")
 * @ORM\Entity
 */
class EjercicioTipo
{
    /**
     * @var string
     *
     * @ORM\Column(name="tipo", type="string", length=100, nullable=false)
     */
    private $tipo;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_tipo_ejercicio", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idTipoEjercicio;



    /**
     * Set tipo
     *
     * @param string $tipo
     *
     * @return EjercicioTipo
     */
    public function setTipo($tipo)
    {
        $this->tipo = $tipo;

        return $this;
    }

    /**
     * Get tipo
     *
     * @return string
     */
    public function getTipo()
    {
        return $this->tipo;
    }

    /**
     * Get idTipoEjercicio
     *
     * @return integer
     */
    public function getIdTipoEjercicio()
    {
        return $this->idTipoEjercicio;
    }
}
