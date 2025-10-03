<?php

namespace SiteBundle\Entity;

interface EntityStatusInterface
{
    public const STATUS_PENDING = 1;
    public const STATUS_ACTIVE = 2;
    public const STATUS_ARCHIVED = 3;
    
    public function getStatus(): ?int;

    public function setStatus(int $status): self;
}
