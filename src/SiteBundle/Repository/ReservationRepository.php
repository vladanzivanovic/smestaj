<?php
/**
 * Created by PhpStorm.
 * User: vlada
 * Date: 6/8/2017
 * Time: 3:02 PM
 */

namespace SiteBundle\Repository;


use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityRepository;
use SiteBundle\Constants\MainConstants;
use SiteBundle\Constants\MessageConstants;
use SiteBundle\DbFunctions\GroupConcat;
use SiteBundle\Entity\Ads;
use SiteBundle\Entity\Reservation;
use SiteBundle\Exceptions\ApplicationException;

class ReservationRepository extends ExtendedEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
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
    public function getReservationPaginationAdmin(array $data)
    {
        if(empty(array_filter($data)))
            return false;

        $column = array('Id', 'EventDate', 'ContactType', 'GuestNumber', 'TimeFrom', 'TimeTo', 'EventPlace', 'AdsTitle', 'CityName', 'EventTypeName');
        $direction = array('ASC', 'DESC');

        $query = $this->getBasePart()
            ->addSelect('r.isrealized AS IsRealized');

        if(isset($data['Search']) && !empty($data['Search'])){
            $query->where('
                    r.id LIKE :Search,
                    r.eventdata LIKE :Search,
                    r.contacttype LIKE :Search,
                    r.guestnumber LIKE :Search,
                    r.timefrom LIKE :Search,
                    r.timeto LIKE :Search,
                    r.eventplace LIKE :Search,
                    a.title LIKE :Search,
                    c.name LIKE :Search,
                    cl.firstname LIKE :Search,
                    cl.lastname LIKE :Search,
                    cl.id LIKE :Search,
                    et.name LIKE :Search
                ');
            $query->setParameter('Search', "%{$data['Search']}%");
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
     * Get Reservation by Id
     * @param $id
     * @param bool $getFromCache
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws ApplicationException
     */
    public function getById($id, $getFromCache = true)
    {
        $id = (int)$id;

        if($id > 0){
            $query = $this->getBasePart()
                ->addSelect(
                    'r.note AS Note',
                    'r.interests AS Interests',
                    'a.id AS AdsId',
                    'et.id AS EventTypeId',
                    'c.id AS EventCityId',
                    'cl.firstname AS FirstName',
                    'cl.lastname AS LastName',
                    'cl.email AS Email',
                    'cl.telephone AS Telephone',
                    'cl.mobilephone AS MobilePhone',
                    'cl.address AS Address',
                    'IDENTITY(cl.userid) AS UserId',
                    'IDENTITY(cl.cityid) AS CityId',
                    'r.status AS Status'
                )
                ->where('r.id = :Id')
                ->setParameter('Id', $id)
                ->getQuery();

            if(true === $getFromCache)
                $query->useQueryCache(true)
                    ->useResultCache(true, MainConstants::HOUR_CACHE);

            return $query->getOneOrNullResult();
        }

        throw new ApplicationException(MessageConstants::EMPTY_REQUEST);
    }

    /**
     * Format DateTime Object to yyyy-mm format
     * and perform search LIKE date
     *
     * @param \DateTime $date
     * @param Ads       $ads
     *
     * @return array
     */
    public function getByMonthAndYear(\DateTime $date, Ads $ads)
    {
        return $this->getBasePart()
            ->addSelect( 'GROUP_CONCAT(DISTINCT DAY(r.eventdate) ORDER BY r.eventdate ASC) as ReservedDays' )
            ->where(' 
                r.eventdate LIKE :date AND
                r.adsid = :adsId
            ')
            ->setParameter('date', $date->format('Y-m') .'%')
            ->setParameter('adsId', $ads)
            ->getQuery()
            ->getResult();
    }

    private function getBasePart()
    {
        $query = $this->createQueryBuilder('r')
            ->select(
                'r.id as Id',
                'DATEFORMAT(r.eventdate, \'%d-%m-%Y\') AS EventDate',
                'r.contacttype AS ContactType',
                'r.guestnumber AS GuestNumber',
                'DATEFORMAT(r.timefrom, \'%H:%i\') AS TimeFrom',
                'DATEFORMAT(r.timeto, \'%H:%i\') AS TimeTo',
                'r.eventplace AS EventPlace',
                'a.title AS AdsTitle',
                'c.name AS CityName',
                'CONCAT(cl.firstname, \' \', cl.lastname) AS FullName',
                'cl.id AS ClientId',
                'et.name AS EventTypeName',
                'r.budget AS Budget',
                'et.name AS EventName'
            )
            ->join('r.eventcityid', 'c')
            ->join('r.adsid', 'a')
            ->join('r.clientid', 'cl')
            ->join('r.eventtypeid', 'et');

        return $query;
    }
}