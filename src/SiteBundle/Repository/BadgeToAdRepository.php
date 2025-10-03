<?php
/**
 * Created by PhpStorm.
 * User: vlada
 * Date: 4/25/2017
 * Time: 6:45 PM
 */

namespace SiteBundle\Repository;


use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityRepository;
use SiteBundle\Constants\MainConstants;
use SiteBundle\Entity\Badgetoad;

class BadgeToAdRepository extends ExtendedEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Badgetoad::class);
    }
    public function getByAdsId($id, $getFromCache = true)
    {
        $query = $this->getBasePart()
            ->where( 'bta.adsid = :adsId' )
            ->setParameter( 'adsId', $id )
            ->getQuery();

        if(true === $getFromCache)
            $query->useQueryCache(true)
            ->useResultCache(true, MainConstants::DAY_CACHE);

        return $query->getResult();
    }

    /**
     * Default query part for media
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getBasePart()
    {
        $query = $this->createQueryBuilder('bta')
            ->select('
                bta.id as Id,
                b.id as BadgeId,
                b.name as BadgeName,
                b.image as BadgeImage,
                b.description as BadgeDescription
            ')
            ->join('bta.badgeid', 'b');

        return $query;
    }
}