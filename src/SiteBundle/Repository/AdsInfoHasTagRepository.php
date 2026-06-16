<?php

declare(strict_types=1);

namespace SiteBundle\Repository;

use Doctrine\Persistence\ManagerRegistry;
use SiteBundle\Entity\AdsInfoHasTag;
use SiteBundle\Entity\AdsInfoPage;

final class AdsInfoHasTagRepository extends ExtendedEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdsInfoHasTag::class);
    }

    /**
     * @return array<int, AdsInfoHasTag>
     */
    public function getByInfoPage(AdsInfoPage $infoPage): array
    {
        return $this->createQueryBuilder('aiht')
            ->where('aiht.infoPage = :ip')
            ->setParameter('ip', $infoPage)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<int, array{tag_id: int, tag_name: string, value: string, tag_type_id: int, tag_type_label: string, icon: string}>
     */
    public function getByInfoPageGroupedByType(AdsInfoPage $infoPage): array
    {
        return $this->createQueryBuilder('aiht')
            ->select(
                'tag.id as tag_id',
                'tag.name as tag_name',
                'aiht.value',
                'tt.id as tag_type_id',
                'tt.label as tag_type_label',
                'tag.icon'
            )
            ->join('aiht.tag', 'tag')
            ->join('tag.tagType', 'tt')
            ->where('aiht.infoPage = :ip')
            ->setParameter('ip', $infoPage)
            ->orderBy('tt.id')
            ->getQuery()
            ->getResult();
    }
}
