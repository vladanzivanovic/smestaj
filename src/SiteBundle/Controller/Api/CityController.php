<?php

namespace SiteBundle\Controller\Api;

use SiteBundle\Controller\SiteController;
use SiteBundle\Repository\CityRepository;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CityController
 */
class CityController extends SiteController
{
    private $cityRepository;

    /**
     * CityController constructor.
     *
     * @param CityRepository $cityRepository
     */
    public function __construct(
        CityRepository $cityRepository
    ) {
        $this->cityRepository = $cityRepository;
    }

    /**
     * @Route("/api/all-cities", name="site_all_cities", methods={"GET"}, options={"expose": true})
     * @return mixed
     */
    public function getCitiesAction()
    {
        $cities = $this->cityRepository->getAllCities();

        return $this->json($cities);
    }

    /**
     * @Route("/api/cities/{criteria}", name="site_cities_by_criteria", methods={"GET"})
     * @param $criteria
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getCitiesByCriteriaAction($criteria)
    {
        $cities = $this->cityRepository->getByCriteria($criteria);

        return $this->json($cities);
    }
}
