<?php

namespace SiteBundle\Repository;


use AdminBundle\Model\DataTableModel;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use SiteBundle\Entity\Ads;
use SiteBundle\Entity\Adshastags;
use SiteBundle\Entity\AdsPayedDate;
use SiteBundle\Entity\Category;
use SiteBundle\Entity\City;
use SiteBundle\Entity\EntityInterface;
use SiteBundle\Entity\EntityStatusInterface;
use SiteBundle\Entity\Media;
use SiteBundle\Entity\Tag;
use SiteBundle\Entity\User;
use Symfony\Component\HttpFoundation\ParameterBag;

class AdsRepository extends ExtendedEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ads::class);
    }

    /**
     * Prepare QueryBuilder which will be sent
     * to pagination service to create pagination
     *
     * @param Category  $category
     * @param City|null $city
     * @param array     $searchData
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getPaginationQuery(?Category $category, ?City $city, array $searchData)
    {
        $query = $this->createQueryBuilder('a')
            ->innerJoin(AdsPayedDate::class, 'apd', 'WITH', 'apd.ads = a.id AND apd.status = :activeStatus')
            ->where('a.status = :activeStatus')
            ->setParameter('activeStatus', EntityStatusInterface::STATUS_ACTIVE)
            ->orderBy('apd.type', 'DESC')
            ->groupBy('a.title');

        if (null !== $category) {
            $query->andWhere('a.categoryId = :category')
                ->setParameter('category', $category);
        }

        if (isset($searchData['orderBy'])) {
            $sort = $searchData['orderBy'];

            $query->addOrderBy($sort[0], $sort[1]);
        } else {
            $query->addOrderBy('a.sysCreatedTime', 'DESC');
        }

        if (count($searchData) > 0) {

            if (isset($searchData['tags'])) {
                $tagsQuery = $this->_em->createQueryBuilder()
                    ->select('1')
                    ->from(Adshastags::class, 'aht')
                    ->leftJoin(Tag::class, 't', 'WITH', 'aht.tag = t')
                    ->where('t.slug IN (:tagsSlug)')
                    ->andWhere('aht.ads = a');

                $query->andWhere('EXISTS ('.$tagsQuery->getDQL().')')
                    ->setParameter('tagsSlug', $searchData['tags']);
            }
        }

        if (null !== $city) {
            $query->andWhere('a.cityId = :city')
                ->setParameter('city', $city);
        }

        return $query;
    }

    /**
     * Prepare QueryBuilder which will be sent
     * to pagination service to create pagination
     *
     * @param Category  $category
     * @param City|null $city
     * @param array     $searchData
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAdsForSiteMapByCategory(Category $category)
    {
        $query = $this->createQueryBuilder('a')
            ->innerJoin(AdsPayedDate::class, 'apd', 'WITH', 'apd.ads = a.id AND apd.status = :activeStatus')
            ->where('a.status = :activeStatus')
            ->andWhere('a.categoryId = :category')
            ->setParameter('activeStatus', EntityStatusInterface::STATUS_ACTIVE)
            ->setParameter('category', $category)
            ->orderBy('apd.type', 'DESC')
            ->groupBy('a.title');

        return $query->getQuery()->getResult();
    }

    /**
     * @param Category|null $category
     * @param City|null $city
     * @return float|int|mixed|string
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getAdMinPrice(?Category $category, ?City $city)
    {
        $query = $this->createQueryBuilder('a')
            ->select('MIN(a.prepricefrom)')
            ->innerJoin(AdsPayedDate::class, 'apd', 'WITH', 'apd.ads = a.id AND apd.status = :activeStatus')
            ->where('a.status = :activeStatus')
            ->andWhere('a.prepricefrom > 10')
            ->setParameter('activeStatus', EntityStatusInterface::STATUS_ACTIVE);

        if (null !== $category) {
            $query->andWhere('a.categoryId = :category')
                ->setParameter('category', $category);
        }

        if (null !== $city) {
            $query->andWhere('a.cityId = :city')
                ->setParameter('city', $city);
        }

        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * Prepare QueryBuilder which will be sent
     * to pagination service to create pagination
     *
     * @param array      $adsIds
     * @param array|null $sort
     *
     * @return array
     */
    public function getSortedList(array $adsIds, ?array $sort): array
    {
        $query = $this->createQueryBuilder('a')
            ->select(
                'a.id as id',
                'a.status',
                'a.title as title',
                'a.alias as alias',
                'a.shortDescription as short_description',
                'a.prepricefrom as pre_price_from',
                'cat.alias as category_alias',
                'cat.name as category',
                'cat.id as category_id',
                'c.name as city',
                'c.alias as city_slug',
                'c.id as city_id',
                'media.name as image',
                'IF(apd.id IS NOT NULL, 1, 0) as isPayed'
            )
            ->innerJoin(Media::class, 'media', 'WITH', 'media.adsid = a.id and media.ismain = :isMain')
            ->innerJoin('a.categoryId', 'cat')
            ->innerJoin('a.cityId', 'c')
            ->leftJoin(AdsPayedDate::class, 'apd', 'WITH', 'apd.ads = a.id')
            ->where('a.id IN (:adsIds)')
            ->setParameter('adsIds', $adsIds)
            ->setParameter('isMain', true)
            ->groupBy('a.id')
            ->orderBy('apd.date', 'DESC');

        if (null !== $sort) {
            $query->orderBy($sort[0], $sort[1]);
        }

        return $query->getQuery()->getArrayResult();
    }

    public function getQueryForDashboard(User $user)
    {
        $query = $this->createQueryBuilder('a')
            ->select(
                'a.id',
                'a.status',
                'a.title as title',
                'a.alias as alias',
                'a.shortDescription as short_description',
                'a.prepricefrom as pre_price_from',
                'cat.alias as category_alias',
                'cat.name as category',
                'cat.id as category_id',
                'c.name as city',
                'c.alias as city_slug',
                'c.id as city_id',
                'media.slug as image_slug'
            )
            ->leftJoin(Media::class, 'media', 'WITH', 'media.adsid = a.id and media.ismain = :isMain')
            ->join('a.categoryId', 'cat')
            ->join('a.cityId', 'c')
            ->where('a.owner = :user')
            ->andWhere('a.status != :notArchived')
            ->setParameter('user', $user)
            ->setParameter('isMain', true)
            ->setParameter('notArchived', EntityStatusInterface::STATUS_ARCHIVED)
            ->groupBy('a.id')
            ->orderBy('a.sysCreatedTime', 'DESC');

        return $query;
    }

    /**
     * @param DataTableModel $tableModel
     *
     * @return mixed
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function countData(DataTableModel $tableModel)
    {
        $query = $this->createQueryBuilder('a')
            ->select('COUNT(a.id) as total')
        ;

        if (null !== $tableModel->getSearch()) {
            $categoryQuery = $this->_em->createQueryBuilder()
                ->select('1')
                ->from(Category::class, 'cs1')
                ->leftJoin(Category::class, 'pcs1', 'WITH', 'pcs1.id = cs1.parent')
                ->where('REGEXP(cs1.alias, :regex) = true OR (pcs1 IS NOT NULL AND REGEXP(pcs1.alias, :regex) = true)')
                ->andWhere('a.categoryId = cs1');

            $tagsQuery = $this->_em->createQueryBuilder()
                ->select('1')
                ->from(Adshastags::class, 'aht')
                ->leftJoin(Tag::class, 't', 'WITH', 'aht.tag = t')
                ->where('REGEXP(t.slug, :regex) = true')
                ->andWhere('aht.ads = a');

            $cityQuery = $this->_em->createQueryBuilder()
                ->select('1')
                ->from(City::class, 'csq')
                ->where('REGEXP(csq.alias, :regex) = true')
                ->andWhere('a.cityId = csq');

            $query
                ->andWhere('
                a.id LIKE :search or
                a.title LIKE :search or
                EXISTS ('.$categoryQuery->getDQL().') or
                EXISTS ('.$tagsQuery->getDQL().') or 
                EXISTS ('.$cityQuery->getDQL().')
            ')
                ->setParameter('search', '%'.$tableModel->getSearch().'%')
                ->setParameter('regex', $tableModel->getSearch());
        }

        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @param DataTableModel $tableModel
     *
     * @return array
     */
    public function getAdminList(DataTableModel $tableModel): array
    {
        $query = $this->createQueryBuilder('a')
            ->select(
                'a.id as id',
                'a.status',
                'a.title as title',
                'a.alias as slug',
                'cat.name as category',
                'cat.alias as category_slug',
                'c.name as city',
                'c.alias as city_slug',
                'IF(apd.id IS NOT NULL, 1, 0) as isPayed',
                'apd.date as payedDate'
            )
            ->innerJoin('a.categoryId', 'cat')
            ->innerJoin('a.cityId', 'c')
            ->leftJoin(AdsPayedDate::class, 'apd', 'WITH', 'apd.ads = a.id')
            ->setFirstResult($tableModel->getOffset())
            ->setMaxResults($tableModel->getLimit())
            ->groupBy('a.id')
            ->orderBy($tableModel->getOrderColumn(), $tableModel->getOrderDirection());

        if (!empty($tableModel->getSearch())) {
            $categoryQuery = $this->_em->createQueryBuilder()
                ->select('1')
                ->from(Category::class, 'cs1')
                ->leftJoin(Category::class, 'pcs1', 'WITH', 'pcs1.id = cs1.parent')
                ->where('REGEXP(cs1.alias, :regex) = true OR (pcs1 IS NOT NULL AND REGEXP(pcs1.alias, :regex) = true)')
                ->andWhere('a.categoryId = cs1');

            $tagsQuery = $this->_em->createQueryBuilder()
                ->select('1')
                ->from(Adshastags::class, 'aht')
                ->leftJoin(Tag::class, 't', 'WITH', 'aht.tag = t')
                ->where('REGEXP(t.slug, :regex) = true')
                ->andWhere('aht.ads = a');

            $cityQuery = $this->_em->createQueryBuilder()
                ->select('1')
                ->from(City::class, 'csq')
                ->where('REGEXP(csq.alias, :regex) = true')
                ->andWhere('a.cityId = csq');

            $query
                ->andWhere('
                a.id LIKE :search or
                a.title LIKE :search or
                EXISTS ('.$categoryQuery->getDQL().') or
                EXISTS ('.$tagsQuery->getDQL().') or 
                EXISTS ('.$cityQuery->getDQL().')
            ')
                ->setParameter('search', '%'.$tableModel->getSearch().'%')
                ->setParameter('regex', $tableModel->getSearch());
        }

        return $query->getQuery()->getArrayResult();
    }

    /**
     * @param $category
     *
     * @return array
     */
    public function getSuggestions($category)
    {
        $query = $this->createQueryBuilder('a')
            ->select(
                'a.alias',
                'a.title',
                'a.prepricefrom as pre_price_from',
                'city.alias as city_slug',
                'category.alias as category_slug',
                'media.slug as media_slug'
            )
            ->innerJoin('a.cityId', 'city')
            ->innerJoin('a.categoryId', 'category')
            ->innerJoin(Media::class, 'media', 'WITH', 'a.id = media.adsid and media.ismain = 1')
            ->leftJoin('SiteBundle:Category', 'pc', 'WITH', 'pc.id = category.parent')
            ->leftJoin(AdsPayedDate::class, 'payed', 'WITH', 'payed.ads = a AND DATEDIFF(DATE_ADD(payed.date, 1, \'YEAR\'), NOW()) > :dateDiff')
            ->where('a.status = :status')
            ->andWhere('(a.categoryId != :category OR pc.id != :category)')
            ->setParameter('status', EntityStatusInterface::STATUS_ACTIVE)
            ->setParameter('category', $category)
            ->setParameter('dateDiff', -1)
            ->groupBy('a.alias')
            ->addOrderBy('RAND()')
            ->setMaxResults('6');

        return $query->getQuery()->getArrayResult();
    }

    /**
     * @param int $limit
     *
     * @return mixed
     */
    public function getPayed(int $limit)
    {
        $query = $this->createQueryBuilder('ads')
            ->select(
                'ads.alias',
                'ads.prepricefrom as pre_price_from',
                'ads.title',
                'cat.alias as category_alias',
                'media.slug as image',
                'c.name as city',
                'c.alias as city_alias'
            )
            ->join(Media::class, 'media', 'WITH', 'ads.id = media.adsid and media.ismain = 1')
            ->join('ads.categoryId', 'cat')
            ->join('ads.payedDate', 'payed')
            ->join('ads.cityId', 'c')
            ->where('ads.status = :status')
            ->andWhere('DATEDIFF(DATE_SUB(payed.date, -1, \'YEAR\'), NOW()) > :date')
            ->andWhere('payed.status != :basicStatus')
            ->setParameter('status', EntityStatusInterface::STATUS_ACTIVE)
            ->setParameter('date', -1)
            ->setParameter('basicStatus', AdsPayedDate::PAYMENT_PLAN_BASIC)
            ->groupBy('ads.alias')
            ->orderBy('RAND()')
            ->setMaxResults($limit);

        return $query->getQuery()->getArrayResult();
    }

    /**
     * @param array $categories
     *
     * @return mixed
     */
    public function countByCategories(array $categories)
    {
        $query = $this->createQueryBuilder('a')
            ->select(
                'COUNT (a.id) as total',
                'IDENTITY(a.categoryId) as category_id'
            )
            ->where('a.categoryId IN (:categories)')
            ->andWhere('a.status = :statusActive')
            ->setParameter('categories', $categories)
            ->setParameter('statusActive', EntityStatusInterface::STATUS_ACTIVE)
            ->groupBy('a.categoryId');

        return $query->getQuery()->getResult();
    }

    /**
     * Default query part for ads
     * @param bool $mainImg
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function selectWithJoinPartQuery($mainImg = true)
    {
        $query = $this->createQueryBuilder('a')
            ->select(
                    'a.id as adsId',
                    'a.title as title',
                    'a.alias as alias',
                    'a.status as status',
                    'a.shortDescription as shortDescription',
                    'a.prepricefrom as prePriceFrom',
                    'a.prepriceto as prePriceTo',
                    'a.pricefrom as priceFrom',
                    'a.priceto as priceTo',
                    'a.postpricefrom as postPriceFrom',
                    'a.postpriceto as postPriceTo',
                    'cat.alias as catAlias',
                    'cat.name as category',
                    'cat.id as categoryId',
                    'c.name as city',
                    'c.id as cityId'
                )
            ->join('a.categoryId', 'cat')
            ->join('a.cityId', 'c')
            ->where('a.status != '.EntityStatusInterface::STATUS_ARCHIVED);

        if(true === $mainImg) {
            $query
                ->addSelect('media.name As mediaName')
                ->join('SiteBundle:Media', 'media', 'WITH', 'a.id = media.adsid and media.ismain = 1');

            return $query;
        }

        $query->addSelect('media.name AS mediaName')
            ->leftJoin('SiteBundle:Media', 'media', 'WITH', 'a.id = media.adsid and media.ismain = 1');

        return $query;
    }
}
