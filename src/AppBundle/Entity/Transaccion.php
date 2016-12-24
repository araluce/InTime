<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Transaccion
 *
 * @ORM\Table(name="transaccion", indexes={@ORM\Index(name="id_usuario_origen", columns={"id_usuario_origen"}), @ORM\Index(name="id_usuario_destino", columns={"id_usuario_destino"}), @ORM\Index(name="id_tipo_transaccion", columns={"id_tipo_transaccion"}), @ORM\Index(name="id_transaccion_pagar", columns={"id_transaccion_pagar"})})
 * @ORM\Entity
 */
class Transaccion
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="tiempo_cedido", type="datetime", nullable=true)
     */
    private $tiempoCedido;

    /**
     * @var boolean
     *
     * @ORM\Column(name="pagado", type="boolean", nullable=true)
     */
    private $pagado;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_transaccion", type="datetime", nullable=true)
     */
    private $fechaTransaccion;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_transaccion", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idTransaccion;

    /**
     * @var \AppBundle\Entity\Usuario
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Usuario")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_usuario_origen", referencedColumnName="id_usuario")
     * })
     */
    private $idUsuarioOrigen;

    /**
     * @var \AppBundle\Entity\Usuario
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Usuario")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_usuario_destino", referencedColumnName="id_usuario")
     * })
     */
    private $idUsuarioDestino;

    /**
     * @var \AppBundle\Entity\TipoTransaccion
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\TipoTransaccion")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_tipo_transaccion", referencedColumnName="id_tipo_transaccion")
     * })
     */
    private $idTipoTransaccion;

    /**
     * @var \AppBundle\Entity\Transaccion
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Transaccion")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_transaccion_pagar", referencedColumnName="id_transaccion")
     * })
     */
    private $idTransaccionPagar;



    /**
     * Set tiempoCedido
     *
     * @param \DateTime $tiempoCedido
     * @return Transaccion
     */
    public function setTiempoCedido($tiempoCedido)
    {
        $this->tiempoCedido = $tiempoCedido;

        return $this;
    }

    /**
     * Get tiempoCedido
     *
     * @return \DateTime 
     */
    public function getTiempoCedido()
    {
        return $this->tiempoCedido;
    }

    /**
     * Set pagado
     *
     * @param boolean $pagado
     * @return Transaccion
     */
    public function setPagado($pagado)
    {
        $this->pagado = $pagado;

        return $this;
    }

    /**
     * Get pagado
     *
     * @return boolean 
     */
    public function getPagado()
    {
        return $this->pagado;
    }

    /**
     * Set fechaTransaccion
     *
     * @param \DateTime $fechaTransaccion
     * @return Transaccion
     */
    public function setFechaTransaccion($fechaTransaccion)
    {
        $this->fechaTransaccion = $fechaTransaccion;

        return $this;
    }

    /**
     * Get fechaTransaccion
     *
     * @return \DateTime 
     */
    public function getFechaTransaccion()
    {
        return $this->fechaTransaccion;
    }

    /**
     * Get idTransaccion
     *
     * @return integer 
     */
    public function getIdTransaccion()
    {
        return $this->idTransaccion;
    }

    /**
     * Set idUsuarioOrigen
     *
     * @param \AppBundle\Entity\Usuario $idUsuarioOrigen
     * @return Transaccion
     */
    public function setIdUsuarioOrigen(\AppBundle\Entity\Usuario $idUsuarioOrigen = null)
    {
        $this->idUsuarioOrigen = $idUsuarioOrigen;

        return $this;
    }

    /**
     * Get idUsuarioOrigen
     *
     * @return \AppBundle\Entity\Usuario 
     */
    public function getIdUsuarioOrigen()
    {
        return $this->idUsuarioOrigen;
    }

    /**
     * Set idUsuarioDestino
     *
     * @param \AppBundle\Entity\Usuario $idUsuarioDestino
     * @return Transaccion
     */
    public function setIdUsuarioDestino(\AppBundle\Entity\Usuario $idUsuarioDestino = null)
    {
        $this->idUsuarioDestino = $idUsuarioDestino;

        return $this;
    }

    /**
     * Get idUsuarioDestino
     *
     * @return \AppBundle\Entity\Usuario 
     */
    public function getIdUsuarioDestino()
    {
        return $this->idUsuarioDestino;
    }

    /**
     * Set idTipoTransaccion
     *
     * @param \AppBundle\Entity\TipoTransaccion $idTipoTransaccion
     * @return Transaccion
     */
    public function setIdTipoTransaccion(\AppBundle\Entity\TipoTransaccion $idTipoTransaccion = null)
    {
        $this->idTipoTransaccion = $idTipoTransaccion;

        return $this;
    }

    /**
     * Get idTipoTransaccion
     *
     * @return \AppBundle\Entity\TipoTransaccion 
     */
    public function getIdTipoTransaccion()
    {
        return $this->idTipoTransaccion;
    }

    /**
     * Set idTransaccionPagar
     *
     * @param \AppBundle\Entity\Transaccion $idTransaccionPagar
     * @return Transaccion
     */
    public function setIdTransaccionPagar(\AppBundle\Entity\Transaccion $idTransaccionPagar = null)
    {
        $this->idTransaccionPagar = $idTransaccionPagar;

        return $this;
    }

    /**
     * Get idTransaccionPagar
     *
     * @return \AppBundle\Entity\Transaccion 
     */
    public function getIdTransaccionPagar()
    {
        return $this->idTransaccionPagar;
    }
}
