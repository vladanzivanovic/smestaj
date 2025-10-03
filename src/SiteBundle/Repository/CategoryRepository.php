<?php
/**
 * Created by PhpStorm.
 * User: vlada
 * Date: 4/11/2017
 * Time: 12:03 AM
 */

namespace SiteBundle\Repository;


use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;
use SiteBundle\Constants\MainConstants;
use SiteBundle\Entity\Category;
use SiteBundle\Entity\EntityStatusInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;

class CategoryRepository extends ExtendedEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /**
     * @return array
     */
    public function getAllActive(): array
    {
        $query = $this->getBasePart()
            ->addSelect('
                c.id AS categoryId
            ')
            ->where('c.status = :status')
            ->setParameter('status', EntityStatusInterface::STATUS_ACTIVE)
            ->getQuery()
            ->useQueryCache(true);

        return $query->getResult();
    }

    /**
     * @return array
     */
    public function getActiveForOptions(): array
    {
        $query = $this->createQueryBuilder('c')
            ->select(
                'c.id AS value',
                'c.name as title'
            )
            ->where('c.status = :status')
            ->setParameter('status', EntityStatusInterface::STATUS_ACTIVE)
            ->getQuery()
            ->useQueryCache(true);

        return $query->getResult();
    }

    /**
     * Get All Category with there images
     * @param bool $getFromCache
     * @return array
     */
    public function getOnlyParents($getFromCache = true)
    {
        $query = $this->getBasePart()
            ->addSelect('
                c.id AS CategoryId
            ')
            ->where('c.parent IS NULL')
            ->andWhere('c.status = :status')
            ->setParameter('status', EntityStatusInterface::STATUS_ACTIVE)
            ->getQuery()
            ->useQueryCache(true);

        if(true === $getFromCache)
            $query->useResultCache(true, MainConstants::DAY_CACHE, 'category_all_id');

        return $query->getResult();
    }

    /**
     * Check if record exist by name and id (optional)
     * @param string $alias
     * @param null $id
     * @return null|mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getByAlias($alias, $id = null)
    {
        try {
            $query = $this->createQueryBuilder('c')
                    ->select('
                    c.id as CategoryId,
                    c.name as CategoryName,
                    c.alias as CategoryAlias,
                    c.image as CategoryImage,
                    cp.name AS ParentName,
                    cp.id AS ParentId,
                    cp.alias AS ParentAlias
                ')
                ->leftJoin('c.parent', 'cp')
                ->where('c.alias = :alias')
                ->setParameter('alias', $alias);

            if ($id > 0)
                $query->andWhere(' c.id != :id')
                    ->setParameter('id', $id);

            $result = $query->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $exception){
            $result = null;
        }

        return $result;
    }

    public function getLikeCriteria($criteria)
    {
        return $this->getBasePart()
            ->addSelect('c.id AS CategoryId')
            ->where('
                c.name LIKE :Search OR
                c.alias LIKE :Search
            ')
            ->setParameter('Search', "%$criteria%")
            ->getQuery()
            ->getResult();
    }

    /**
     * Fetch data from Db by given criterias
     *
     * @example $data = [
     *      "search" => value to search against table columns,
     *      "sortColumn" => sorting column in table
     *      "sortDirection" => ASC or DESC
     *      "offset" => number from which to start to fetch data from table
     *      "limit" => number of records to retrieve from table
     * ]
     *
     * @param array $data
     * @return array|bool
     */
    public function getCategoryPaginationAdmin(array $data)
    {
        if(empty(array_filter($data)))
            return false;

        $column = array('CategoryId', 'CategoryName');
        $direction = array('ASC', 'DESC');

        $query = $this->getBasePart()
            ->where('c.isdeleted = 0');

        if(isset($data['Search']) && !empty($data['Search'])){
            $query->andWhere('
                    c.id LIKE :Search OR
                    c.name LIKE :Search OR
                ');
            $query->setParameter("Search", "%{$data['Search']}%");
        }

        if( isset($data['SortColumn'], $data['SortDirection']) && !empty($data['SortColumn']) && !empty($data['SortDirection'])){
            if(!in_array($data['SortColumn'], $column, false) && !in_array($data['SortDirection'], $direction, false)){
                return false;
            }
            $query->orderBy($data['SortColumn'], $data['SortDirection']);
        }

        $query->setFirstResult($data['Offset'])
            ->setMaxResults($data['Limit']);

        return $query->getQuery()->getArrayResult();
    }

    /**
     * Default query part for media
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getBasePart()
    {
        $query = $this->createQueryBuilder('c')
            ->select('
                c.id AS id,
                c.name AS name,
                c.alias AS alias,
                c.image AS image,
                cp.name AS parentName,
                cp.id AS parentId,
                cp.alias AS parentAlias
            ')
            ->leftJoin('c.parent', 'cp');

        return $query;
    }
}
