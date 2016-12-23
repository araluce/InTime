<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TipoMensaje
 *
 * @ORM\Table(name="TIPO_MENSAJE")
 * @ORM\Entity
 */
class TipoMensaje
{
    /**
     * @var string
     *
     * @ORM\Column(name="tipo", type="string", length=100, nullable=false)
     */
    private $tipo;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_tipo_mensaje", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idTipoMensaje;



    /**
     * Set tipo
     *
     * @param string $tipo
     *
     * @return TipoMensaje
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
     * Get idTipoMensaje
     *
     * @return integer
     */
    public function getIdTipoMensaje()
    {
        return $this->idTipoMensaje;
    }
}
