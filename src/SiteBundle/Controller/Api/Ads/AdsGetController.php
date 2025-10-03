<?php

namespace SiteBundle\Controller\Api\Ads;


use SiteBundle\Controller\SiteController;
use SiteBundle\Entity\Ads;
use SiteBundle\Services\Ads\AdsDashboardService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class AdsGetController extends SiteController
{
    private AdsDashboardService $adsDashboardService;

    public function __construct(
        AdsDashboardService $adsDashboardService
    ) {
        $this->adsDashboardService = $adsDashboardService;
    }

    /**
     * @Route("/api/product/{alias}", name="get_ad_dashboard_edit", methods={"GET"})
     *
     * @param Ads $ads
     *
     * @return JsonResponse
     */
    public function getAdDashboard(Ads $ads)
    {
        $data = $this->adsDashboardService->getAdDashboard($ads);

        return new JsonResponse($data);
    }
}