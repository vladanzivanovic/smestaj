<?php
/**
 * Created by PhpStorm.
 * User: vlada
 * Date: 4/11/2017
 * Time: 12:03 AM
 */

namespace SiteBundle\Repository;


use Doctrine\ORM\EntityRepository;
use SiteBundle\Constants\MainConstants;
use SiteBundle\Entity\Category;

class GendersRepository extends EntityRepository
{

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
    public function getGenderPaginationAdmin(array $data)
    {
        if(empty(array_filter($data)))
            return false;

        $column = array('id', 'name', 'syscreatedid');
        $direction = array('ASC', 'DESC');

        $query = $this->baseQueryPart();

        if(isset($data['Search']) && !empty($data['Search'])){
            $query->where('
                    g.id LIKE :Search OR
                    g.name LIKE :Search OR
                    g.syscreatedid LIKE :Search OR
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
     * Get All Genders
     * @param bool $getFromCache
     * @return array
     */
    public function getAll($getFromCache = true)
    {
        $query = $this->baseQueryPart()
            ->addSelect('category.alias AS CategoryAlias')
            ->getQuery();

        if(true === $getFromCache)
            $query->useQueryCache(true)
            ->useResultCache(true, MainConstants::DAY_CACHE, 'gender_all_id');

        return $query->getResult();
    }

    /**
     * Search genders by criteria on name column
     * @param $criteria
     * @return array
     */
    public function getLikeName($criteria)
    {
        return $this->baseQueryPart()
            ->addSelect('g.name AS Name')
            ->addSelect('category.alias AS CategoryAlias')
            ->where('g.name LIKE :Search')
            ->setParameter('Search', "%{$criteria}%")
            ->getQuery()
            ->getResult();
    }

    public function getByCategory(Category $category)
    {
        $query = $this->baseQueryPart()
            ->where('g.category = :category')
            ->setParameter('category', $category->getId());

        if (!empty($category->getParent())) {
            $query->orWhere('category.id = :parent')
                ->setParameter('parent', $category->getParent()->getId());
        }

        return $query->getQuery()
            ->useQueryCache(true)
            ->useResultCache(true, MainConstants::DAY_CACHE, 'gender_by_category')
            ->getResult();
    }

    private function baseQueryPart()
    {
        return $this->createQueryBuilder('g')
            ->select('
                g.id AS GenderId,
                g.name AS GenderName,
                g.alias AS GenderAlias,
                u.id AS SysCreatorId,
                u.username AS GenderSysUser,
                category.id AS CategoryId,
                category.name AS CategoryName
            ')
            ->join('g.syscreatorid', 'u')
            ->leftJoin('g.category', 'category');
    }
}