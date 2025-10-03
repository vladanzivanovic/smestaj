<?php

namespace SiteBundle\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use SiteBundle\Entity\Ads;
use SiteBundle\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Class TagRepository
 */
final class TagRepository extends ExtendedEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }

    public function getTags()
    {
        $query = $this->createQueryBuilder('tag')
            ->select(
                'tag.id as id',
                'tag.name as name',
                'tag.slug as slug',
                'tagType.id AS type_id',
                'tagType.label AS type_label',
                'tagType.name AS type_name'
            )
            ->innerJoin('tag.tagType', 'tagType')
            ->orderBy('tagType.id');

        return $query->getQuery()->getResult();
    }

    public function getTagsForFilter()
    {
        $query = $this->createQueryBuilder('tag')
            ->select(
                'tag.id as id',
                'tag.name as name',
                'tag.slug as slug',
                'tagType.id AS type_id',
                'tagType.label AS type_label',
                'tagType.name AS type_name'
            )
            ->innerJoin('tag.tagType', 'tagType')
            ->where('tagType.label != \'range\'')
            ->orderBy('tagType.id');

        return $query->getQuery()->getResult();
    }


    /**
     * @param int $type
     *
     * @return array
     */
    public function getForOptions(): array
    {
        $query = $this->createQueryBuilder('t')
            ->select(
                't.id as value',
                't.name as title',
                'tagType.name as typeName',
                'tagType.label as typeLabel'
            )
            ->innerJoin('t.tagType', 'tagType')
            ->orderBy('tagType.id');

        return $query->getQuery()->getArrayResult();
    }

    /**
     * @param Ads $ads
     *
     * @return array
     */
    public function getByAd(Ads $ads): array
    {
        $query = $this->createQueryBuilder('t')
            ->join('t.hasTag', 'aht')
            ->where('aht.ads = :ads')
            ->setParameter('ads', $ads);

        return $query->getQuery()->getResult();
    }
}
