<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Usuario
 *
 * @ORM\Table(name="USUARIO", indexes={@ORM\Index(name="id_rol", columns={"id_rol", "id_estado"}), @ORM\Index(name="id_estado", columns={"id_estado"}), @ORM\Index(name="id_cuenta", columns={"id_cuenta"}), @ORM\Index(name="id_distrito", columns={"id_distrito"}), @ORM\Index(name="IDX_1D204E4790F1D76D", columns={"id_rol"})})
 * @ORM\Entity
 */
class Usuario
{
    /**
     * @var string
     *
     * @ORM\Column(name="nombre", type="string", length=30, nullable=true)
     */
    private $nombre;

    /**
     * @var string
     *
     * @ORM\Column(name="apellidos", type="string", length=40, nullable=true)
     */
    private $apellidos;

    /**
     * @var string
     *
     * @ORM\Column(name="clave", type="string", length=40, nullable=true)
     */
    private $clave;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=120, nullable=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="seudonimo", type="string", length=40, nullable=true)
     */
    private $seudonimo;

    /**
     * @var string
     *
     * @ORM\Column(name="dni", type="string", length=9, nullable=false)
     */
    private $dni;

    /**
     * @var string
     *
     * @ORM\Column(name="usu_runtastic", type="string", length=30, nullable=true)
     */
    private $usuRuntastic;

    /**
     * @var string
     *
     * @ORM\Column(name="certificado", type="string", length=40, nullable=false)
     */
    private $certificado;

    /**
     * @var string
     *
     * @ORM\Column(name="imagen", type="string", length=1000, nullable=true)
     */
    private $imagen;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="tiempo_sin_comer", type="datetime", nullable=true)
     */
    private $tiempoSinComer;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="tiempo_sin_comer_distrito", type="datetime", nullable=true)
     */
    private $tiempoSinComerDistrito;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="tiempo_sin_beber", type="datetime", nullable=true)
     */
    private $tiempoSinBeber;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="tiempo_sin_beber_distrito", type="datetime", nullable=true)
     */
    private $tiempoSinBeberDistrito;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_usuario", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idUsuario;

    /**
     * @var \AppBundle\Entity\UsuarioDistrito
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\UsuarioDistrito")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_distrito", referencedColumnName="id_usuario_distrito")
     * })
     */
    private $idDistrito;

    /**
     * @var \AppBundle\Entity\Rol
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Rol")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_rol", referencedColumnName="id_rol")
     * })
     */
    private $idRol;

    /**
     * @var \AppBundle\Entity\UsuarioEstado
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\UsuarioEstado")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_estado", referencedColumnName="id_estado")
     * })
     */
    private $idEstado;

    /**
     * @var \AppBundle\Entity\UsuarioCuenta
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\UsuarioCuenta")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_cuenta", referencedColumnName="id_cuenta")
     * })
     */
    private $idCuenta;



    /**
     * Set nombre
     *
     * @param string $nombre
     * @return Usuario
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
     * Set apellidos
     *
     * @param string $apellidos
     * @return Usuario
     */
    public function setApellidos($apellidos)
    {
        $this->apellidos = $apellidos;

        return $this;
    }

    /**
     * Get apellidos
     *
     * @return string 
     */
    public function getApellidos()
    {
        return $this->apellidos;
    }

    /**
     * Set clave
     *
     * @param string $clave
     * @return Usuario
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
     * Set email
     *
     * @param string $email
     * @return Usuario
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set seudonimo
     *
     * @param string $seudonimo
     * @return Usuario
     */
    public function setSeudonimo($seudonimo)
    {
        $this->seudonimo = $seudonimo;

        return $this;
    }

    /**
     * Get seudonimo
     *
     * @return string 
     */
    public function getSeudonimo()
    {
        return $this->seudonimo;
    }

    /**
     * Set dni
     *
     * @param string $dni
     * @return Usuario
     */
    public function setDni($dni)
    {
        $this->dni = $dni;

        return $this;
    }

    /**
     * Get dni
     *
     * @return string 
     */
    public function getDni()
    {
        return $this->dni;
    }

    /**
     * Set usuRuntastic
     *
     * @param string $usuRuntastic
     * @return Usuario
     */
    public function setUsuRuntastic($usuRuntastic)
    {
        $this->usuRuntastic = $usuRuntastic;

        return $this;
    }

    /**
     * Get usuRuntastic
     *
     * @return string 
     */
    public function getUsuRuntastic()
    {
        return $this->usuRuntastic;
    }

    /**
     * Set certificado
     *
     * @param string $certificado
     * @return Usuario
     */
    public function setCertificado($certificado)
    {
        $this->certificado = $certificado;

        return $this;
    }

    /**
     * Get certificado
     *
     * @return string 
     */
    public function getCertificado()
    {
        return $this->certificado;
    }

    /**
     * Set imagen
     *
     * @param string $imagen
     * @return Usuario
     */
    public function setImagen($imagen)
    {
        $this->imagen = $imagen;

        return $this;
    }

    /**
     * Get imagen
     *
     * @return string 
     */
    public function getImagen()
    {
        return $this->imagen;
    }

    /**
     * Set tiempoSinComer
     *
     * @param \DateTime $tiempoSinComer
     * @return Usuario
     */
    public function setTiempoSinComer($tiempoSinComer)
    {
        $this->tiempoSinComer = $tiempoSinComer;

        return $this;
    }

    /**
     * Get tiempoSinComer
     *
     * @return \DateTime 
     */
    public function getTiempoSinComer()
    {
        return $this->tiempoSinComer;
    }

    /**
     * Set tiempoSinComerDistrito
     *
     * @param \DateTime $tiempoSinComerDistrito
     * @return Usuario
     */
    public function setTiempoSinComerDistrito($tiempoSinComerDistrito)
    {
        $this->tiempoSinComerDistrito = $tiempoSinComerDistrito;

        return $this;
    }

    /**
     * Get tiempoSinComerDistrito
     *
     * @return \DateTime 
     */
    public function getTiempoSinComerDistrito()
    {
        return $this->tiempoSinComerDistrito;
    }

    /**
     * Set tiempoSinBeber
     *
     * @param \DateTime $tiempoSinBeber
     * @return Usuario
     */
    public function setTiempoSinBeber($tiempoSinBeber)
    {
        $this->tiempoSinBeber = $tiempoSinBeber;

        return $this;
    }

    /**
     * Get tiempoSinBeber
     *
     * @return \DateTime 
     */
    public function getTiempoSinBeber()
    {
        return $this->tiempoSinBeber;
    }

    /**
     * Set tiempoSinBeberDistrito
     *
     * @param \DateTime $tiempoSinBeberDistrito
     * @return Usuario
     */
    public function setTiempoSinBeberDistrito($tiempoSinBeberDistrito)
    {
        $this->tiempoSinBeberDistrito = $tiempoSinBeberDistrito;

        return $this;
    }

    /**
     * Get tiempoSinBeberDistrito
     *
     * @return \DateTime 
     */
    public function getTiempoSinBeberDistrito()
    {
        return $this->tiempoSinBeberDistrito;
    }

    /**
     * Get idUsuario
     *
     * @return integer 
     */
    public function getIdUsuario()
    {
        return $this->idUsuario;
    }

    /**
     * Set idDistrito
     *
     * @param \AppBundle\Entity\UsuarioDistrito $idDistrito
     * @return Usuario
     */
    public function setIdDistrito(\AppBundle\Entity\UsuarioDistrito $idDistrito = null)
    {
        $this->idDistrito = $idDistrito;

        return $this;
    }

    /**
     * Get idDistrito
     *
     * @return \AppBundle\Entity\UsuarioDistrito 
     */
    public function getIdDistrito()
    {
        return $this->idDistrito;
    }

    /**
     * Set idRol
     *
     * @param \AppBundle\Entity\Rol $idRol
     * @return Usuario
     */
    public function setIdRol(\AppBundle\Entity\Rol $idRol = null)
    {
        $this->idRol = $idRol;

        return $this;
    }

    /**
     * Get idRol
     *
     * @return \AppBundle\Entity\Rol 
     */
    public function getIdRol()
    {
        return $this->idRol;
    }

    /**
     * Set idEstado
     *
     * @param \AppBundle\Entity\UsuarioEstado $idEstado
     * @return Usuario
     */
    public function setIdEstado(\AppBundle\Entity\UsuarioEstado $idEstado = null)
    {
        $this->idEstado = $idEstado;

        return $this;
    }

    /**
     * Get idEstado
     *
     * @return \AppBundle\Entity\UsuarioEstado 
     */
    public function getIdEstado()
    {
        return $this->idEstado;
    }

    /**
     * Set idCuenta
     *
     * @param \AppBundle\Entity\UsuarioCuenta $idCuenta
     * @return Usuario
     */
    public function setIdCuenta(\AppBundle\Entity\UsuarioCuenta $idCuenta = null)
    {
        $this->idCuenta = $idCuenta;

        return $this;
    }

    /**
     * Get idCuenta
     *
     * @return \AppBundle\Entity\UsuarioCuenta 
     */
    public function getIdCuenta()
    {
        return $this->idCuenta;
    }
}
