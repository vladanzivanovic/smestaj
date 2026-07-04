<?php

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
use Symfony\Component\Routing\Attribute\Route;

final class AdsIndexController extends SiteController
{
    private AdsService $adsService;

    private AdsPageCollector $adsPageCollector;

    private AdsPageFormatter $adsPageFormatter;

    private SearchDataParser $searchDataParser;

    private LoggerInterface $logger;

    public function __construct(
        AdsService $adsService,
        AdsPageCollector $adsPageCollector,
        AdsPageFormatter $adsPageFormatter,
        SearchDataParser $searchDataParser,
        LoggerInterface $logger
    ) {
        $this->adsService = $adsService;
        $this->adsPageCollector = $adsPageCollector;
        $this->adsPageFormatter = $adsPageFormatter;
        $this->searchDataParser = $searchDataParser;
        $this->logger = $logger;
    }

    public function indexAction(
        #[MapEntity(mapping: ['category' => 'alias'])] Category $category,
        AdsListRequest $listRequest,
        ?string $extraParams
    ): JsonResponse {
        try {
            $searchCriteria = $this->searchDataParser->parseSearch($listRequest->query, $extraParams);

            $data = $this->adsPageCollector->collect($searchCriteria, $category);

            return new JsonResponse($this->adsPageFormatter->format($data));
        } catch (\Throwable $throwable) {
            $this->logger->error(
                'Failed getting ads from API',
                [
                    'category' => $category,
                    'query' => $listRequest->query->all(),
                    'extraParams' => $extraParams,
                ]
            );

            throw $throwable;
        }
    }

    public function listOrDetailByParamsExceptCategoryAction(
        AdsListRequest $listRequest,
        ?string $extraParams
    ): JsonResponse {
        try {
            $searchCriteria = $this->searchDataParser->parseSearch($listRequest->query, $extraParams);

            $data = $this->adsPageCollector->collect($searchCriteria);

            return new JsonResponse($this->adsPageFormatter->format($data));
        } catch (\Throwable $throwable) {
            $this->logger->error(
                'Failed getting ads from API',
                [
                    'query' => $listRequest->query->all(),
                    'extraParams' => $extraParams,
                ]
            );

            throw $throwable;
        }
    }

    #[Route('/api/product-pagination/{page}', name: 'site_ads_paginate', methods: ['GET'])]
    public function adsPagination($page)
    {
        $user = $this->getUser();

        $adsData = $this->adsService->getDashboardAdsList($user, $page);

        return $this->json($adsData);
    }
}
