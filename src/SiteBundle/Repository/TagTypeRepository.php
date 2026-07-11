<?php

declare(strict_types=1);

namespace SiteBundle\Repository;

use Doctrine\Persistence\ManagerRegistry;
use SiteBundle\Entity\TagType;

/**
 * Class TagTypeRepository
 */
final class TagTypeRepository extends ExtendedEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TagType::class);
    }
}
