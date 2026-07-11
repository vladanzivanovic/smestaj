<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: vlada
 * Date: 5/5/2017
 * Time: 12:14 AM
 */

namespace SiteBundle\Repository;


use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityRepository;
use SiteBundle\Entity\Userreservation;

final class UserReservationRepository extends ExtendedEntityRepository
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
