<?php
/**
 * Created by PhpStorm.
 * User: vlada
 * Date: 4/12/2017
 * Time: 2:27 PM
 */

namespace SiteBundle\Repository;


use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityRepository;
use SiteBundle\Constants\MainConstants;
use SiteBundle\Entity\Eventtype;

class EventTypeRepository extends ExtendedEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Eventtype::class);
    }
    /**
     * Get All Events type
     * @param bool $getFromCache
     * @return array
     */
    public function getAll($getFromCache = true)
    {
        $query = $this->getBasePart()
            ->getQuery();

        if(true === $getFromCache)
            $query->useQueryCache(true)
                ->useResultCache(true, MainConstants::DAY_CACHE, 'eventtype_all_id');

        return $query->getResult();
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
    public function getEventTypePaginationAdmin(array $data)
    {
        if(empty(array_filter($data)))
            return false;

        $column = array('id', 'name', 'syscreatedid');
        $direction = array('ASC', 'DESC');

        $query = $this->getBasePart();

        if(isset($data['Search']) && !empty($data['Search'])){
            $query->where('
                    a.id LIKE :Search OR
                    a.name LIKE :Search OR
                    a.syscreatedid LIKE :Search OR
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
        $query = $this->createQueryBuilder('et')
            ->select('
                et.id as Id,
                et.name as Name,
                et.alias as Alias
            ');

        return $query;
    }
}