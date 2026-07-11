<?php

declare(strict_types=1);

namespace AdminBundle\Parser;

use SiteBundle\Entity\EntityInterface;

interface RequestParserInterface
{
    /**
     * Translates a typed DTO (bundle- and feature-specific) into a domain entity.
     * Concrete implementers narrow the accepted DTO type in their own docblocks;
     * the return type MAY be narrowed via LSP covariance.
     *
     * @param object               $dto    concrete DTO — implementer's docblock narrows the union
     * @param EntityInterface|null $entity when null, implementer instantiates a fresh entity
     */
    public function parse(object $dto, ?EntityInterface $entity = null): EntityInterface;

    public function create(): EntityInterface;
}
