<?php
/**
 * Created by PhpStorm.
 * User: vlada
 * Date: 6/15/2017
 * Time: 6:40 PM
 */

namespace SiteBundle\Services;


use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use SiteBundle\Entity\Genders;
use SiteBundle\Repository\AdsRepository;
use SiteBundle\Repository\CategoryRepository;
use SiteBundle\Repository\CityRepository;
use SiteBundle\Repository\GendersRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SearchService extends ServiceContainer
{
    private $cityRepository;

    /**
     * SearchService constructor.
     *
     * @param ObjectManager         $entity
     * @param TokenStorageInterface $tokenStorage
     * @param CityRepository        $cityRepository
     */
    public function __construct(
        ObjectManager $entity,
        TokenStorageInterface $tokenStorage,
        CityRepository $cityRepository
    ) {
        parent::__construct($entity, $tokenStorage);

        $this->cityRepository = $cityRepository;
    }

    public function getCityByCriteria($city)
    {
        $cities = $this->cityRepository->getByCriteria($city);

        $cities = array_map(function ($city) {
            $city['type'] = 'city';

            return $city;
        }, $cities);

        return $cities;
    }
}