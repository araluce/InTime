<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SesionRuntastic
 *
 * @ORM\Table(name="SESION_RUNTASTIC", indexes={@ORM\Index(name="id_usuario", columns={"id_usuario_runtastic"})})
 * @ORM\Entity
 */
class SesionRuntastic
{
    /**
     * @var boolean
     *
     * @ORM\Column(name="evaluado", type="boolean", nullable=false)
     */
    private $evaluado;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_runtastic", type="integer", nullable=false)
     */
    private $idRuntastic;

    /**
     * @var string
     *
     * @ORM\Column(name="tipo", type="string", length=1000, nullable=false)
     */
    private $tipo;

    /**
     * @var integer
     *
     * @ORM\Column(name="duracion", type="integer", nullable=false)
     */
    private $duracion;

    /**
     * @var integer
     *
     * @ORM\Column(name="distancia", type="integer", nullable=false)
     */
    private $distancia;

    /**
     * @var integer
     *
     * @ORM\Column(name="paso", type="integer", nullable=false)
     */
    private $paso;

    /**
     * @var float
     *
     * @ORM\Column(name="velocidad", type="float", precision=10, scale=0, nullable=false)
     */
    private $velocidad;

    /**
     * @var integer
     *
     * @ORM\Column(name="kcal", type="integer", nullable=false)
     */
    private $kcal;

    /**
     * @var integer
     *
     * @ORM\Column(name="ritmo_cardiaco_medio", type="integer", nullable=false)
     */
    private $ritmoCardiacoMedio;

    /**
     * @var integer
     *
     * @ORM\Column(name="ritmo_cardiaco_max", type="integer", nullable=false)
     */
    private $ritmoCardiacoMax;

    /**
     * @var integer
     *
     * @ORM\Column(name="desnivel", type="integer", nullable=false)
     */
    private $desnivel;

    /**
     * @var integer
     *
     * @ORM\Column(name="perdida_nivel", type="integer", nullable=false)
     */
    private $perdidaNivel;

    /**
     * @var string
     *
     * @ORM\Column(name="superficie", type="string", length=1000, nullable=true)
     */
    private $superficie;

    /**
     * @var string
     *
     * @ORM\Column(name="tiempo", type="string", length=1000, nullable=true)
     */
    private $tiempo;

    /**
     * @var string
     *
     * @ORM\Column(name="sensacion", type="string", length=1000, nullable=true)
     */
    private $sensacion;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha", type="datetime", nullable=false)
     */
    private $fecha;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_sesion_runtastic", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idSesionRuntastic;

    /**
     * @var \AppBundle\Entity\UsuarioRuntastic
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\UsuarioRuntastic")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_usuario_runtastic", referencedColumnName="id_usuario_runtastic")
     * })
     */
    private $idUsuarioRuntastic;



    /**
     * Set evaluado
     *
     * @param boolean $evaluado
     * @return SesionRuntastic
     */
    public function setEvaluado($evaluado)
    {
        $this->evaluado = $evaluado;

        return $this;
    }

    /**
     * Get evaluado
     *
     * @return boolean 
     */
    public function getEvaluado()
    {
        return $this->evaluado;
    }

    /**
     * Set idRuntastic
     *
     * @param integer $idRuntastic
     * @return SesionRuntastic
     */
    public function setIdRuntastic($idRuntastic)
    {
        $this->idRuntastic = $idRuntastic;

        return $this;
    }

    /**
     * Get idRuntastic
     *
     * @return integer 
     */
    public function getIdRuntastic()
    {
        return $this->idRuntastic;
    }

    /**
     * Set tipo
     *
     * @param string $tipo
     * @return SesionRuntastic
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
     * Set duracion
     *
     * @param integer $duracion
     * @return SesionRuntastic
     */
    public function setDuracion($duracion)
    {
        $this->duracion = $duracion;

        return $this;
    }

    /**
     * Get duracion
     *
     * @return integer 
     */
    public function getDuracion()
    {
        return $this->duracion;
    }

    /**
     * Set distancia
     *
     * @param integer $distancia
     * @return SesionRuntastic
     */
    public function setDistancia($distancia)
    {
        $this->distancia = $distancia;

        return $this;
    }

    /**
     * Get distancia
     *
     * @return integer 
     */
    public function getDistancia()
    {
        return $this->distancia;
    }

    /**
     * Set paso
     *
     * @param integer $paso
     * @return SesionRuntastic
     */
    public function setPaso($paso)
    {
        $this->paso = $paso;

        return $this;
    }

    /**
     * Get paso
     *
     * @return integer 
     */
    public function getPaso()
    {
        return $this->paso;
    }

    /**
     * Set velocidad
     *
     * @param float $velocidad
     * @return SesionRuntastic
     */
    public function setVelocidad($velocidad)
    {
        $this->velocidad = $velocidad;

        return $this;
    }

    /**
     * Get velocidad
     *
     * @return float 
     */
    public function getVelocidad()
    {
        return $this->velocidad;
    }

    /**
     * Set kcal
     *
     * @param integer $kcal
     * @return SesionRuntastic
     */
    public function setKcal($kcal)
    {
        $this->kcal = $kcal;

        return $this;
    }

    /**
     * Get kcal
     *
     * @return integer 
     */
    public function getKcal()
    {
        return $this->kcal;
    }

    /**
     * Set ritmoCardiacoMedio
     *
     * @param integer $ritmoCardiacoMedio
     * @return SesionRuntastic
     */
    public function setRitmoCardiacoMedio($ritmoCardiacoMedio)
    {
        $this->ritmoCardiacoMedio = $ritmoCardiacoMedio;

        return $this;
    }

    /**
     * Get ritmoCardiacoMedio
     *
     * @return integer 
     */
    public function getRitmoCardiacoMedio()
    {
        return $this->ritmoCardiacoMedio;
    }

    /**
     * Set ritmoCardiacoMax
     *
     * @param integer $ritmoCardiacoMax
     * @return SesionRuntastic
     */
    public function setRitmoCardiacoMax($ritmoCardiacoMax)
    {
        $this->ritmoCardiacoMax = $ritmoCardiacoMax;

        return $this;
    }

    /**
     * Get ritmoCardiacoMax
     *
     * @return integer 
     */
    public function getRitmoCardiacoMax()
    {
        return $this->ritmoCardiacoMax;
    }

    /**
     * Set desnivel
     *
     * @param integer $desnivel
     * @return SesionRuntastic
     */
    public function setDesnivel($desnivel)
    {
        $this->desnivel = $desnivel;

        return $this;
    }

    /**
     * Get desnivel
     *
     * @return integer 
     */
    public function getDesnivel()
    {
        return $this->desnivel;
    }

    /**
     * Set perdidaNivel
     *
     * @param integer $perdidaNivel
     * @return SesionRuntastic
     */
    public function setPerdidaNivel($perdidaNivel)
    {
        $this->perdidaNivel = $perdidaNivel;

        return $this;
    }

    /**
     * Get perdidaNivel
     *
     * @return integer 
     */
    public function getPerdidaNivel()
    {
        return $this->perdidaNivel;
    }

    /**
     * Set superficie
     *
     * @param string $superficie
     * @return SesionRuntastic
     */
    public function setSuperficie($superficie)
    {
        $this->superficie = $superficie;

        return $this;
    }

    /**
     * Get superficie
     *
     * @return string 
     */
    public function getSuperficie()
    {
        return $this->superficie;
    }

    /**
     * Set tiempo
     *
     * @param string $tiempo
     * @return SesionRuntastic
     */
    public function setTiempo($tiempo)
    {
        $this->tiempo = $tiempo;

        return $this;
    }

    /**
     * Get tiempo
     *
     * @return string 
     */
    public function getTiempo()
    {
        return $this->tiempo;
    }

    /**
     * Set sensacion
     *
     * @param string $sensacion
     * @return SesionRuntastic
     */
    public function setSensacion($sensacion)
    {
        $this->sensacion = $sensacion;

        return $this;
    }

    /**
     * Get sensacion
     *
     * @return string 
     */
    public function getSensacion()
    {
        return $this->sensacion;
    }

    /**
     * Set fecha
     *
     * @param \DateTime $fecha
     * @return SesionRuntastic
     */
    public function setFecha($fecha)
    {
        $this->fecha = $fecha;

        return $this;
    }

    /**
     * Get fecha
     *
     * @return \DateTime 
     */
    public function getFecha()
    {
        return $this->fecha;
    }

    /**
     * Get idSesionRuntastic
     *
     * @return integer 
     */
    public function getIdSesionRuntastic()
    {
        return $this->idSesionRuntastic;
    }

    /**
     * Set idUsuarioRuntastic
     *
     * @param \AppBundle\Entity\UsuarioRuntastic $idUsuarioRuntastic
     * @return SesionRuntastic
     */
    public function setIdUsuarioRuntastic(\AppBundle\Entity\UsuarioRuntastic $idUsuarioRuntastic = null)
    {
        $this->idUsuarioRuntastic = $idUsuarioRuntastic;

        return $this;
    }

    /**
     * Get idUsuarioRuntastic
     *
     * @return \AppBundle\Entity\UsuarioRuntastic 
     */
    public function getIdUsuarioRuntastic()
    {
        return $this->idUsuarioRuntastic;
    }
}
