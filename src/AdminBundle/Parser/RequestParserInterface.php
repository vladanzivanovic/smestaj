<?php

namespace AdminBundle\Parser;

use SiteBundle\Entity\EntityInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

interface RequestParserInterface
{
    /**
     * @param ParameterBag         $bag
     * @param EntityInterface|null $entity
     *
     * @return EntityInterface
     */
    public function parse(ParameterBag $bag, EntityInterface $entity = null): EntityInterface;

    /**
     * @return EntityInterface
     */
    public function create(): EntityInterface;
}