<?php

declare(strict_types=1);

namespace SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

trait ResourceTrait
{
    /**
     * @ORM\Column(name="Id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}