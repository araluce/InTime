<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MinaPista
 *
 * @ORM\Table(name="MINA_PISTA", indexes={@ORM\Index(name="id_mina", columns={"id_mina"})})
 * @ORM\Entity
 */
class MinaPista
{
    /**
     * @var string
     *
     * @ORM\Column(name="pista", type="string", length=1000, nullable=false)
     */
    private $pista;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_mina_pista", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idMinaPista;

    /**
     * @var \AppBundle\Entity\Mina
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Mina")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_mina", referencedColumnName="id_mina")
     * })
     */
    private $idMina;



    /**
     * Set pista
     *
     * @param string $pista
     * @return MinaPista
     */
    public function setPista($pista)
    {
        $this->pista = $pista;

        return $this;
    }

    /**
     * Get pista
     *
     * @return string 
     */
    public function getPista()
    {
        return $this->pista;
    }

    /**
     * Get idMinaPista
     *
     * @return integer 
     */
    public function getIdMinaPista()
    {
        return $this->idMinaPista;
    }

    /**
     * Set idMina
     *
     * @param \AppBundle\Entity\Mina $idMina
     * @return MinaPista
     */
    public function setIdMina(\AppBundle\Entity\Mina $idMina = null)
    {
        $this->idMina = $idMina;

        return $this;
    }

    /**
     * Get idMina
     *
     * @return \AppBundle\Entity\Mina 
     */
    public function getIdMina()
    {
        return $this->idMina;
    }
}
