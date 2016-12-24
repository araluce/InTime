<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UsuarioXTwittero
 *
 * @ORM\Table(name="USUARIO_X_TWITTERO", indexes={@ORM\Index(name="IDX_13ACE190FCF8192D", columns={"id_usuario"})})
 * @ORM\Entity
 */
class UsuarioXTwittero
{
    /**
     * @var string
     *
     * @ORM\Column(name="id_twittero", type="string", length=30)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idTwittero;

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
     * Set idTwittero
     *
     * @param string $idTwittero
     * @return UsuarioXTwittero
     */
    public function setIdTwittero($idTwittero)
    {
        $this->idTwittero = $idTwittero;

        return $this;
    }

    /**
     * Get idTwittero
     *
     * @return string 
     */
    public function getIdTwittero()
    {
        return $this->idTwittero;
    }

    /**
     * Set idUsuario
     *
     * @param \AppBundle\Entity\Usuario $idUsuario
     * @return UsuarioXTwittero
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
