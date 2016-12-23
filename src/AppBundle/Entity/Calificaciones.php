<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Calificaciones
 *
 * @ORM\Table(name="CALIFICACIONES")
 * @ORM\Entity
 */
class Calificaciones
{
    /**
     * @var float
     *
     * @ORM\Column(name="correspondencia_numerica", type="float", precision=10, scale=0, nullable=false)
     */
    private $correspondenciaNumerica;

    /**
     * @var string
     *
     * @ORM\Column(name="correspondencia_texto", type="string", length=100, nullable=false)
     */
    private $correspondenciaTexto;

    /**
     * @var string
     *
     * @ORM\Column(name="correspondencia_icono", type="string", length=100, nullable=false)
     */
    private $correspondenciaIcono;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_calificaciones", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idCalificaciones;



    /**
     * Set correspondenciaNumerica
     *
     * @param float $correspondenciaNumerica
     *
     * @return Calificaciones
     */
    public function setCorrespondenciaNumerica($correspondenciaNumerica)
    {
        $this->correspondenciaNumerica = $correspondenciaNumerica;

        return $this;
    }

    /**
     * Get correspondenciaNumerica
     *
     * @return float
     */
    public function getCorrespondenciaNumerica()
    {
        return $this->correspondenciaNumerica;
    }

    /**
     * Set correspondenciaTexto
     *
     * @param string $correspondenciaTexto
     *
     * @return Calificaciones
     */
    public function setCorrespondenciaTexto($correspondenciaTexto)
    {
        $this->correspondenciaTexto = $correspondenciaTexto;

        return $this;
    }

    /**
     * Get correspondenciaTexto
     *
     * @return string
     */
    public function getCorrespondenciaTexto()
    {
        return $this->correspondenciaTexto;
    }

    /**
     * Set correspondenciaIcono
     *
     * @param string $correspondenciaIcono
     *
     * @return Calificaciones
     */
    public function setCorrespondenciaIcono($correspondenciaIcono)
    {
        $this->correspondenciaIcono = $correspondenciaIcono;

        return $this;
    }

    /**
     * Get correspondenciaIcono
     *
     * @return string
     */
    public function getCorrespondenciaIcono()
    {
        return $this->correspondenciaIcono;
    }

    /**
     * Get idCalificaciones
     *
     * @return integer
     */
    public function getIdCalificaciones()
    {
        return $this->idCalificaciones;
    }
}
