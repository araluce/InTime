<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TipoTransaccion
 *
 * @ORM\Table(name="tipo_transaccion")
 * @ORM\Entity
 */
class TipoTransaccion
{
    /**
     * @var string
     *
     * @ORM\Column(name="nombre", type="string", length=30, nullable=false)
     */
    private $nombre;

    /**
     * @var string
     *
     * @ORM\Column(name="descripcion", type="string", length=120, nullable=true)
     */
    private $descripcion;

    /**
     * @var string
     *
     * @ORM\Column(name="origen", type="string", length=30, nullable=true)
     */
    private $origen;

    /**
     * @var string
     *
     * @ORM\Column(name="destino", type="string", length=30, nullable=true)
     */
    private $destino;

    /**
     * @var boolean
     *
     * @ORM\Column(name="devuelve", type="boolean", nullable=true)
     */
    private $devuelve;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="tiempo_devolucion", type="time", nullable=true)
     */
    private $tiempoDevolucion;

    /**
     * @var integer
     *
     * @ORM\Column(name="veces", type="integer", nullable=true)
     */
    private $veces;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="limite_max_tdv", type="datetime", nullable=false)
     */
    private $limiteMaxTdv = 'CURRENT_TIMESTAMP';

    /**
     * @var integer
     *
     * @ORM\Column(name="id_tipo_transaccion", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idTipoTransaccion;



    /**
     * Set nombre
     *
     * @param string $nombre
     *
     * @return TipoTransaccion
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * Get nombre
     *
     * @return string
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set descripcion
     *
     * @param string $descripcion
     *
     * @return TipoTransaccion
     */
    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    /**
     * Get descripcion
     *
     * @return string
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }

    /**
     * Set origen
     *
     * @param string $origen
     *
     * @return TipoTransaccion
     */
    public function setOrigen($origen)
    {
        $this->origen = $origen;

        return $this;
    }

    /**
     * Get origen
     *
     * @return string
     */
    public function getOrigen()
    {
        return $this->origen;
    }

    /**
     * Set destino
     *
     * @param string $destino
     *
     * @return TipoTransaccion
     */
    public function setDestino($destino)
    {
        $this->destino = $destino;

        return $this;
    }

    /**
     * Get destino
     *
     * @return string
     */
    public function getDestino()
    {
        return $this->destino;
    }

    /**
     * Set devuelve
     *
     * @param boolean $devuelve
     *
     * @return TipoTransaccion
     */
    public function setDevuelve($devuelve)
    {
        $this->devuelve = $devuelve;

        return $this;
    }

    /**
     * Get devuelve
     *
     * @return boolean
     */
    public function getDevuelve()
    {
        return $this->devuelve;
    }

    /**
     * Set tiempoDevolucion
     *
     * @param \DateTime $tiempoDevolucion
     *
     * @return TipoTransaccion
     */
    public function setTiempoDevolucion($tiempoDevolucion)
    {
        $this->tiempoDevolucion = $tiempoDevolucion;

        return $this;
    }

    /**
     * Get tiempoDevolucion
     *
     * @return \DateTime
     */
    public function getTiempoDevolucion()
    {
        return $this->tiempoDevolucion;
    }

    /**
     * Set veces
     *
     * @param integer $veces
     *
     * @return TipoTransaccion
     */
    public function setVeces($veces)
    {
        $this->veces = $veces;

        return $this;
    }

    /**
     * Get veces
     *
     * @return integer
     */
    public function getVeces()
    {
        return $this->veces;
    }

    /**
     * Set limiteMaxTdv
     *
     * @param \DateTime $limiteMaxTdv
     *
     * @return TipoTransaccion
     */
    public function setLimiteMaxTdv($limiteMaxTdv)
    {
        $this->limiteMaxTdv = $limiteMaxTdv;

        return $this;
    }

    /**
     * Get limiteMaxTdv
     *
     * @return \DateTime
     */
    public function getLimiteMaxTdv()
    {
        return $this->limiteMaxTdv;
    }

    /**
     * Get idTipoTransaccion
     *
     * @return integer
     */
    public function getIdTipoTransaccion()
    {
        return $this->idTipoTransaccion;
    }
}
