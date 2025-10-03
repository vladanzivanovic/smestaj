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
use Doctrine\ORM\NonUniqueResultException;
use SiteBundle\Constants\MainConstants;
use SiteBundle\Entity\Badges;

class BadgeRepository extends ExtendedEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Badges::class);
    }
    /**
     * Get All Badges
     * @param bool $getFromCache
     * @return array
     */
    public function getAll($getFromCache = true)
    {
        $query = $this->getBasePart()
            ->getQuery();

        if(true === $getFromCache)
            $query->useQueryCache(true)
                ->useResultCache(true, MainConstants::DAY_CACHE, 'badges_all_id');

        return $query->getResult();
    }

    /**
     * Check if record exist by name and id (optional)
     * @param $name
     * @param null $id
     * @return null|mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function checkExistanceByName($name, $id = null)
    {
        try {
            $query = $this->getBasePart()
                ->where('b.name = :name')
                ->setParameter('name', $name);

            if ($id > 0)
                $query->andWhere(' b.id != :id')
                    ->setParameter('id', $id);

            $result = $query->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $exception){
            $result = null;
        }

        return $result;
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
    public function getBadgesPaginationAdmin(array $data)
    {
        if(empty(array_filter($data)))
            return false;

        $column = array('id', 'name');
        $direction = array('ASC', 'DESC');

        $query = $this->getBasePart();

        if(isset($data['Search']) && !empty($data['Search'])){
            $query->where('
                    b.id LIKE :Search OR
                    b.name LIKE :Search OR
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
        $query = $this->createQueryBuilder('b')
            ->select('
                b.id as Id,
                b.name as Name,
                b.image as Image,
                b.description as Description
            ');

        return $query;
    }
}