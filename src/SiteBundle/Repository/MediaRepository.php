<?php

namespace SiteBundle\Repository;

use Doctrine\Common\Persistence\ManagerRegistry;
use SiteBundle\Constants\MainConstants;
use SiteBundle\Entity\Ads;
use SiteBundle\Entity\Media;

class MediaRepository extends ExtendedEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Media::class);
    }

    public function getByAds(Ads $ads, $url = null)
    {
        $query = $this->getBasePart()
            ->where( 'm.adsid = :ads' )
            ->andWhere('m.adsinfoid IS NULL')
            ->setParameter('ads', $ads);

//        if(null !== $url)
//            $query->addSelect('
//                CONCAT(\''. $url .'\', \''. $id .'/\', m.name) AS imageUrl
//            ')->addSelect('
//                CONCAT(\''. $url .'\', \''. $id .'/\', \'thumb/\', m.name) AS thumbUrl
//            ');

        return $query->getQuery()
            ->getResult();
    }

    public function getByAd(Ads $ads): array
    {
        $query = $this->createQueryBuilder('m')
            ->select(
                'm.id',
                'm.name as fileName',
                'm.ismain as isMain'
            )
            ->where('m.adsid = :ads')
            ->andWhere('m.adsinfoid IS NULL')
            ->setParameter('ads', $ads);

        return $query->getQuery()->getArrayResult();
    }

    public function getByAdsInfoId($id, $adsId, $url = null)
    {
        $query = $this->getBasePart()
            ->where( 'm.adsinfoid = :adsInfoId' )
            ->setParameter('adsInfoId', $id);

        if (null !== $url) {
            $query->addSelect('
                CONCAT(\'' . $url . '\', \'' . $adsId . '/additional/\', m.name) AS imageUrl
            ')->addSelect('
                CONCAT(\'' . $url . '\', \'' . $adsId . '/additional/\', \'thumb/\', m.name) AS thumbUrl
            ');
        }

        return $query->getQuery()
            ->useQueryCache(true)
            ->useResultCache(true, MainConstants::DAY_CACHE)
            ->getResult();
    }

    /**
     * @param Ads $ads
     *
     * @return mixed
     */
    public function getMainImage(Ads $ads)
    {
        $query = $this->createQueryBuilder('m')
            ->select('m')
            ->where('m.adsid = :ads')
            ->andWhere('m.ismain = :isMain')
            ->setParameter('ads', $ads)
            ->setParameter('isMain', true);

        return $query->getQuery()->getOneOrNullResult();
    }

    /**
     * Default query part for media
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getBasePart()
    {
        $query = $this->createQueryBuilder('m')
            ->select('
                m.id as id,
                IDENTITY(m.adsid) as adsId,
                m.name as name,
                m.ismain AS isMain,
                IDENTITY(m.adsinfoid) AS adsInfoId,
                m.slug
            ');

        return $query;
    }
}