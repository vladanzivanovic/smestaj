<?php

declare(strict_types=1);

namespace SiteBundle\Controller\Api\Ads;


use SiteBundle\Controller\SiteController;
use SiteBundle\Entity\Ads;
use SiteBundle\Services\Ads\AdsDashboardService;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class AdsGetController extends SiteController
{
    private AdsDashboardService $adsDashboardService;

    public function __construct(
        AdsDashboardService $adsDashboardService
    ) {
        $this->adsDashboardService = $adsDashboardService;
    }

    #[Route('/api/product/{alias}', name: 'get_ad_dashboard_edit', methods: ['GET'])]
    public function getAdDashboard(#[MapEntity(mapping: ['alias' => 'alias'])] Ads $ads): JsonResponse
    {
        $data = $this->adsDashboardService->getAdDashboard($ads);

        return new JsonResponse($data);
    }
}
