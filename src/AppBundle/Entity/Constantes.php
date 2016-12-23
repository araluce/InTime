<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Constantes
 *
 * @ORM\Table(name="CONSTANTES")
 * @ORM\Entity
 */
class Constantes
{
    /**
     * @var string
     *
     * @ORM\Column(name="valor", type="string", length=100, nullable=false)
     */
    private $valor;

    /**
     * @var string
     *
     * @ORM\Column(name="clave", type="string", length=100)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $clave;



    /**
     * Set valor
     *
     * @param string $valor
     *
     * @return Constantes
     */
    public function setValor($valor)
    {
        $this->valor = $valor;

        return $this;
    }

    /**
     * Get valor
     *
     * @return string
     */
    public function getValor()
    {
        return $this->valor;
    }

    /**
     * Get clave
     *
     * @return string
     */
    public function getClave()
    {
        return $this->clave;
    }
}
