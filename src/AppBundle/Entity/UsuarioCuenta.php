<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UsuarioCuenta
 *
 * @ORM\Table(name="USUARIO_CUENTA")
 * @ORM\Entity
 */
class UsuarioCuenta
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="tdv", type="datetime", nullable=false)
     */
    private $tdv = 'CURRENT_TIMESTAMP';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="finbloqueo", type="datetime", nullable=true)
     */
    private $finbloqueo;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_cuenta", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idCuenta;



    /**
     * Set tdv
     *
     * @param \DateTime $tdv
     *
     * @return UsuarioCuenta
     */
    public function setTdv($tdv)
    {
        $this->tdv = $tdv;

        return $this;
    }

    /**
     * Get tdv
     *
     * @return \DateTime
     */
    public function getTdv()
    {
        return $this->tdv;
    }

    /**
     * Set finbloqueo
     *
     * @param \DateTime $finbloqueo
     *
     * @return UsuarioCuenta
     */
    public function setFinbloqueo($finbloqueo)
    {
        $this->finbloqueo = $finbloqueo;

        return $this;
    }

    /**
     * Get finbloqueo
     *
     * @return \DateTime
     */
    public function getFinbloqueo()
    {
        return $this->finbloqueo;
    }

    /**
     * Get idCuenta
     *
     * @return integer
     */
    public function getIdCuenta()
    {
        return $this->idCuenta;
    }
}
