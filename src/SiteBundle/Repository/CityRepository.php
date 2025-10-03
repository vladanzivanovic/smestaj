<?php
/**
 * Created by PhpStorm.
 * User: vlada
 * Date: 3/1/2017
 * Time: 3:24 PM
 */

namespace SiteBundle\Repository;


use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use SiteBundle\Entity\Ads;
use SiteBundle\Entity\City;
use SiteBundle\Entity\EntityStatusInterface;
use SiteBundle\Entity\Media;

class CityRepository extends ExtendedEntityRepository
{
    private LoggerInterface $logger;

    public function __construct(
        ManagerRegistry $registry,
        LoggerInterface $logger
    ) {
        parent::__construct($registry, City::class);
        $this->logger = $logger;
    }

    public function getAllCities()
    {
        return $this->defaultQuery()
            ->getQuery()
            ->getResult();
    }

    public function getCitiesWithHavingAds()
    {
        return $this->defaultQuery()
            ->innerJoin(Ads::class, 'ads', 'WITH', 'ads.cityId = c.id')
            ->groupBy('ads.cityId')
            ->setMaxResults(100)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int $type
     *
     * @return array
     */
    public function getForOptions(): array
    {
        $query = $this->createQueryBuilder('c')
            ->select(
                'c.alias as value',
                'c.name as title'
            );

        return $query->getQuery()->getArrayResult();
    }

    public function getByCriteria($criteria)
    {
        $query = $this->defaultQuery()
            ->where('c.alias LIKE :criteria')
            ->orWhere('c.name LIKE :criteria')
            ->setParameter('criteria', '%'. $criteria .'%')
            ->orderBy('LENGTH(c.name)');

        return $query->getQuery()->getResult();
    }

    public function get()
    {
        return $this->createQueryBuilder('city')
            ->select('city')
            ->where('city.id > 57000 and city.id < 60000')
            ->getQuery()
            ->getResult();
    }

    public function getForIndex()
    {
        $query = $this->createQueryBuilder('city')
            ->select(
                'city.name',
                'city.alias',
                'COUNT(ads.id) as total_ads'
            )
            ->leftJoin(Ads::class, 'ads', 'WITH', 'city.id = ads.cityId')
            ->leftJoin(Media::class, 'media', 'WITH', 'media.adsid = ads.id AND media.ismain = :mainImage')
            ->where('city.showInHome = :showInHome')
            ->andWhere('ads.status = :activeAds')
            ->andWhere('media.id IS NOT NULL')
            ->setParameter('showInHome', true)
            ->setParameter('activeAds', EntityStatusInterface::STATUS_ACTIVE)
            ->setParameter('mainImage', true)
            ->groupBy('city.id');

        return $query->getQuery()->getResult();
    }

    private function defaultQuery()
    {
        return $this->createQueryBuilder('c')
            ->select('
                c.id AS id,
                c.name AS name,
                c.alias AS alias,
                c.zipcode AS zip_code
            ');
    }
}
