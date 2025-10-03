<?php
/**
 * Created by PhpStorm.
 * User: vlada
 * Date: 4/24/2017
 * Time: 5:59 PM
 */

namespace SiteBundle\Repository;


use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityRepository;
use SiteBundle\Constants\MainConstants;
use SiteBundle\Entity\Youtubeinfo;

class YouTubeInfoRepository extends ExtendedEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Youtubeinfo::class);
    }
    public function getByAdsId($id, $getFromCache = true)
    {
        $query = $this->getBasePart()
            ->where( 'y.adsid = :adsId' )
            ->setParameter('adsId', $id)
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
        $query = $this->createQueryBuilder('y')
            ->select('
                y.id as Id,
                IDENTITY(y.adsid) as AdsId,
                y.title as YouTubeTitle,
                y.title AS Title,
                y.youtubeid as YouTubeId,
                y.channelid as ChannelId,
                y.chaneltitle as ChanelTitle,
                y.thumbnails as Thumbnails
            ');

        return $query;
    }
}