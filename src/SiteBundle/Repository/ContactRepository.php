<?php

namespace SiteBundle\Repository;

use Doctrine\Common\Persistence\ManagerRegistry;
use SiteBundle\Entity\Contact;

class ContactRepository extends ExtendedEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contact::class);
    }

    public function getContactByAd(Contact $contact): array
    {
        $query = $this->createQueryBuilder('c')
            ->addSelect('
                c.firstname AS FirstName,
                c.lastname AS LastName,
                c.address AS Address,
                c.telephone AS Telephone,
                c.mobilePhone AS MobilePhone,
                c.viber AS Viber,
                c.contactEmail AS ContactEmail,
                city.name AS CityName
            ')
            ->leftJoin('c.city', 'city')
            ->where('c = :contact')
            ->setParameter('contact', $contact)
            ->getQuery();

        return $query->getOneOrNullResult();
    }
}
