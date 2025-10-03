<?php

namespace SiteBundle\Repository;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityRepository;
use Gedmo\Mapping\ExtensionMetadataFactory;
use SiteBundle\Entity\TagType;

/**
 * Class TagTypeRepository
 */
class TagTypeRepository extends ExtensionMetadataFactory
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TagType::class);
    }
}
