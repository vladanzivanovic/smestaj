<?php

declare(strict_types=1);

namespace SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

trait StatusTrait
{
    /**
     * @ORM\Column(name="status", type="smallint", options={"default": 0})
     */
    private int $status = EntityStatusInterface::STATUS_PENDING;

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }
}
