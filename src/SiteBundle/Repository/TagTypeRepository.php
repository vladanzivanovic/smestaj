<?php

namespace SiteBundle\Repository;

use Doctrine\Persistence\ManagerRegistry;
use SiteBundle\Entity\TagType;

/**
 * Class TagTypeRepository
 */
class TagTypeRepository extends ExtendedEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TagType::class);
    }
}
