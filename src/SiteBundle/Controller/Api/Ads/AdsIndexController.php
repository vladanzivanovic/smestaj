<?php

namespace SiteBundle\Controller\Api\Ads;

use Psr\Log\LoggerInterface;
use SiteBundle\Collector\AdsPageCollector;
use SiteBundle\Controller\SiteController;
use SiteBundle\Formatter\AdsPageFormatter;
use SiteBundle\Parser\SearchDataParser;
use SiteBundle\Services\Ads\AdsService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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

    /**
     * @param string      $category
     * @param Request     $request
     * @param string|null $extraParams
     *
     * @return JsonResponse
     */
    public function indexAction(string $category, Request $request, ?string $extraParams): JsonResponse
    {
        try {
            $searchCriteria = $this->searchDataParser->parseSearch($request->query, $extraParams);

            $data = $this->adsPageCollector->collect($category, $searchCriteria);

            return new JsonResponse($this->adsPageFormatter->format($data));
        } catch (\Throwable $throwable) {
            $this->logger->error(
                'Failed getting ads from API',
                [
                    'category' => $category,
                    'request' => $request,
                    'extraParams' => $extraParams,
                ]
            );

            throw $throwable;
        }
    }

    /**
     * @Route("/api/product-pagination/{page}", name="site_ads_paginate", methods={"GET"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \SiteBundle\Exceptions\ApplicationException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function adsPagination($page)
    {
        $user = $this->getUser();

        $adsData = $this->adsService->getDashboardAdsList($page, $user);

        return $this->json($adsData);
    }
}
