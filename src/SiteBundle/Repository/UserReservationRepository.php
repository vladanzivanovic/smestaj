<?php
/**
 * Created by PhpStorm.
 * User: vlada
 * Date: 5/5/2017
 * Time: 12:14 AM
 */

namespace SiteBundle\Repository;


use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityRepository;
use SiteBundle\Entity\Userreservation;

class UserReservationRepository extends ExtendedEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Userreservation::class);
    }

    public function getByAdsId(array $ids)
    {

    }

    private function basePart()
    {
        
    }

}