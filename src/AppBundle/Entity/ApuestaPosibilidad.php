<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ApuestaPosibilidad
 *
 * @ORM\Table(name="APUESTA_POSIBILIDAD", indexes={@ORM\Index(name="id_apuesta", columns={"id_apuesta"})})
 * @ORM\Entity
 */
class ApuestaPosibilidad
{
    /**
     * @var string
     *
     * @ORM\Column(name="posibilidad", type="string", length=1000, nullable=false)
     */
    private $posibilidad;

    /**
     * @var boolean
     *
     * @ORM\Column(name="resultado", type="boolean", nullable=true)
     */
    private $resultado;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_apuesta_posibilidad", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idApuestaPosibilidad;

    /**
     * @var \AppBundle\Entity\Apuesta
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Apuesta")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_apuesta", referencedColumnName="id_apuesta")
     * })
     */
    private $idApuesta;



    /**
     * Set posibilidad
     *
     * @param string $posibilidad
     *
     * @return ApuestaPosibilidad
     */
    public function setPosibilidad($posibilidad)
    {
        $this->posibilidad = $posibilidad;

        return $this;
    }

    /**
     * Get posibilidad
     *
     * @return string
     */
    public function getPosibilidad()
    {
        return $this->posibilidad;
    }

    /**
     * Set resultado
     *
     * @param boolean $resultado
     *
     * @return ApuestaPosibilidad
     */
    public function setResultado($resultado)
    {
        $this->resultado = $resultado;

        return $this;
    }

    /**
     * Get resultado
     *
     * @return boolean
     */
    public function getResultado()
    {
        return $this->resultado;
    }

    /**
     * Get idApuestaPosibilidad
     *
     * @return integer
     */
    public function getIdApuestaPosibilidad()
    {
        return $this->idApuestaPosibilidad;
    }

    /**
     * Set idApuesta
     *
     * @param \AppBundle\Entity\Apuesta $idApuesta
     *
     * @return ApuestaPosibilidad
     */
    public function setIdApuesta(\AppBundle\Entity\Apuesta $idApuesta = null)
    {
        $this->idApuesta = $idApuesta;

        return $this;
    }

    /**
     * Get idApuesta
     *
     * @return \AppBundle\Entity\Apuesta
     */
    public function getIdApuesta()
    {
        return $this->idApuesta;
    }
}
