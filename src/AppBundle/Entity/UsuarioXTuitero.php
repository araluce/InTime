<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UsuarioXTuitero
 *
 * @ORM\Table(name="USUARIO_X_TUITERO", indexes={@ORM\Index(name="IDX_F9BA3672FCF8192D", columns={"id_usuario"})})
 * @ORM\Entity
 */
class UsuarioXTuitero
{
    /**
     * @var string
     *
     * @ORM\Column(name="id_tuitero", type="string", length=30)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idTuitero;

    /**
     * @var \AppBundle\Entity\Usuario
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Usuario")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_usuario", referencedColumnName="id_usuario")
     * })
     */
    private $idUsuario;



    /**
     * Set idTuitero
     *
     * @param string $idTuitero
     * @return UsuarioXTuitero
     */
    public function setIdTuitero($idTuitero)
    {
        $this->idTuitero = $idTuitero;

        return $this;
    }

    /**
     * Get idTuitero
     *
     * @return string 
     */
    public function getIdTuitero()
    {
        return $this->idTuitero;
    }

    /**
     * Set idUsuario
     *
     * @param \AppBundle\Entity\Usuario $idUsuario
     * @return UsuarioXTuitero
     */
    public function setIdUsuario(\AppBundle\Entity\Usuario $idUsuario)
    {
        $this->idUsuario = $idUsuario;

        return $this;
    }

    /**
     * Get idUsuario
     *
     * @return \AppBundle\Entity\Usuario 
     */
    public function getIdUsuario()
    {
        return $this->idUsuario;
    }
}
