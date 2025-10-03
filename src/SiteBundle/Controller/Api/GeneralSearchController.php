<?php

namespace SiteBundle\Controller\Api;


use SiteBundle\Controller\SiteController;
use SiteBundle\Services\SearchService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class GeneralSearchController extends SiteController
{
    private $searchService;

    /**
     * GeneralSearchController constructor.
     *
     * @param SearchService $searchService
     */
    public function __construct(
        SearchService $searchService
    ) {
        $this->searchService = $searchService;
    }

    /**
     * @Route("/api/general-search/{city}", methods={"GET"})
     */
    public function getGeneralSearchDataAction($city)
    {
        $cities = $this->searchService->getCityByCriteria($city);

        return $this->json($cities);
    }

}