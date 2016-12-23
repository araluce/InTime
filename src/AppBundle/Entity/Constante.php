<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Constante
 *
 * @ORM\Table(name="CONSTANTE")
 * @ORM\Entity
 */
class Constante
{
    /**
     * @var string
     *
     * @ORM\Column(name="clave", type="string", length=100, nullable=false)
     */
    private $clave;

    /**
     * @var string
     *
     * @ORM\Column(name="valor", type="string", length=1000, nullable=false)
     */
    private $valor;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_constante", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idConstante;



    /**
     * Set clave
     *
     * @param string $clave
     *
     * @return Constante
     */
    public function setClave($clave)
    {
        $this->clave = $clave;

        return $this;
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

    /**
     * Set valor
     *
     * @param string $valor
     *
     * @return Constante
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
     * Get idConstante
     *
     * @return integer
     */
    public function getIdConstante()
    {
        return $this->idConstante;
    }
}
