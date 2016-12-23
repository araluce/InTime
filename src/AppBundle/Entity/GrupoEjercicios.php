<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * GrupoEjercicios
 *
 * @ORM\Table(name="GRUPO_EJERCICIOS")
 * @ORM\Entity
 */
class GrupoEjercicios
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_grupo_ejercicios", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idGrupoEjercicios;



    /**
     * Get idGrupoEjercicios
     *
     * @return integer
     */
    public function getIdGrupoEjercicios()
    {
        return $this->idGrupoEjercicios;
    }
}
