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
use SiteBundle\Entity\Reviews;

class ReviewsRepository extends ExtendedEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reviews::class);
    }
    public function getByAdsId($id)
    {
        return $this->getBasePart()
            ->addSelect('
                r.description as ReviewDescription,
                r.nickname as NickName
            ')
            ->where( 'r.adsid = :adsId and r.isactive = :IsActive' )
            ->setParameter( 'adsId', $id )
            ->setParameter( 'IsActive', 1 )
            ->getQuery()
            ->useQueryCache(true)
            ->useResultCache(true, MainConstants::HOUR_CACHE)
            ->getResult();
    }

    /**
     * Default query part for media
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getBasePart()
    {
        $query = $this->createQueryBuilder('r')
            ->select('
                r.id as Id,
                IDENTITY(r.adsid) as AdsId,
                r.title as ReviewTitle,
                r.syscreatedtime as ReviewDateTime,
                ROUND(( s.profesional + s.recommend + s.accomodation + s.talent )/4, 1) as Average,
                ROUND( ((( s.profesional + s.recommend + s.accomodation + s.talent )/4)/5)*100, 1) as AveragePercent
            ')
            ->leftJoin('SiteBundle:Stars', 's', 'WITH', 's.reviewid = r.id');

        return $query;
    }
}