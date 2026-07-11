<?php

declare(strict_types=1);

namespace SiteBundle\Controller\Api\Ads;

use Psr\Log\LoggerInterface;
use SiteBundle\Collector\AdsPageCollector;
use SiteBundle\Controller\SiteController;
use SiteBundle\Dto\Ads\AdsListRequest;
use SiteBundle\Entity\Category;
use SiteBundle\Formatter\AdsPageFormatter;
use SiteBundle\Parser\SearchDataParser;
use SiteBundle\Services\Ads\AdsService;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

final class AdsIndexController extends SiteController
{
    public function __construct(
        private readonly AdsService $adsService,
        private readonly AdsPageCollector $adsPageCollector,
        private readonly AdsPageFormatter $adsPageFormatter,
        private readonly SearchDataParser $searchDataParser,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function indexAction(
        #[MapEntity(mapping: ['category' => 'alias'])] Category $category,
        #[MapQueryString] ?AdsListRequest $listRequest = null,
        ?string $extraParams = null
    ): JsonResponse {
        try {
            $searchCriteria = $this->searchDataParser->parseSearch($listRequest, $extraParams);

            $data = $this->adsPageCollector->collect($searchCriteria, $category);

            return new JsonResponse($this->adsPageFormatter->format($data));
        } catch (\Throwable $throwable) {
            $this->logger->error(
                'Failed getting ads from API',
                [
                    'category' => $category,
                    'extraParams' => $extraParams,
                ]
            );

            throw $throwable;
        }
    }

    public function listOrDetailByParamsExceptCategoryAction(
        #[MapQueryString] ?AdsListRequest $listRequest = null,
        ?string $extraParams = null
    ): JsonResponse {
        try {
            $searchCriteria = $this->searchDataParser->parseSearch($listRequest, $extraParams);

            $data = $this->adsPageCollector->collect($searchCriteria);

            return new JsonResponse($this->adsPageFormatter->format($data));
        } catch (\Throwable $throwable) {
            $this->logger->error(
                'Failed getting ads from API',
                [
                    'extraParams' => $extraParams,
                ]
            );

            throw $throwable;
        }
    }

    #[Route('/api/product-pagination/{page}', name: 'site_ads_paginate', methods: ['GET'])]
    public function adsPagination(int $page): JsonResponse
    {
        $user = $this->getUser();

        $adsData = $this->adsService->getDashboardAdsList($user, $page);

        return $this->json($adsData);
    }
}
