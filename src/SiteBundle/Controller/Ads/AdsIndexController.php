<?php

namespace SiteBundle\Controller\Ads;

use Psr\Log\LoggerInterface;
use SiteBundle\Collector\AdsPageCollector;
use SiteBundle\Controller\SiteController;
use SiteBundle\Dom\SingleAdsDom;
use SiteBundle\Dto\Ads\AdsListRequest;
use SiteBundle\Entity\Ads;
use SiteBundle\Entity\Category;
use SiteBundle\Formatter\AdsPageFormatter;
use SiteBundle\Parser\SearchDataParser;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestStack;

final class AdsIndexController extends SiteController
{
    private RequestStack $requestStack;

    private AdsPageCollector $adsPageCollector;

    private AdsPageFormatter $adsPageFormatter;

    private SearchDataParser $searchDataParser;

    private SingleAdsDom $singleAdsDom;

    private LoggerInterface $logger;

    public function __construct(
        RequestStack $requestStack,
        AdsPageCollector $adsPageCollector,
        AdsPageFormatter $adsPageFormatter,
        SearchDataParser $searchDataParser,
        SingleAdsDom $singleAdsDom,
        LoggerInterface $logger
    ) {
        $this->requestStack = $requestStack;
        $this->adsPageCollector = $adsPageCollector;
        $this->adsPageFormatter = $adsPageFormatter;
        $this->searchDataParser = $searchDataParser;
        $this->singleAdsDom = $singleAdsDom;
        $this->logger = $logger;
    }

    public function indexAction(
        #[MapEntity(mapping: ['category' => 'alias'])] Category $category,
        AdsListRequest $listRequest,
        null|string $extraParams = null
    ): Response {
        try {
            $searchCriteria = $this->searchDataParser->parseSearch($listRequest->query, $extraParams);

            if (null !== $searchCriteria['ad']) {
                return $this->singleAdsDom->singleAdsAction($searchCriteria['ad']);
            }

            $data = $this->adsPageCollector->collect($searchCriteria, $category);
            $data['extra_params'] = $extraParams;
            $data['selected_city_name'] = $searchCriteria['city'] !== null ? $searchCriteria['city']->getName() : null;

            return $this->render('@Site/Site/adsView.html.twig',
                $this->adsPageFormatter->format($data)
            );
        } catch (\Throwable $throwable) {
            $this->logger->error(
                'Failed render ads list page',
                [
                    'category' => $category->getAlias(),
                    'query' => $listRequest->query->all(),
                    'extraParams' => $extraParams,
                ]
            );

            throw $throwable;
        }
    }

    public function listOrDetailByParamsExceptCategoryAction(
        AdsListRequest $listRequest,
        null|string $extraParams = null
    ): Response {
        try {
            $searchCriteria = $this->searchDataParser->parseSearch($listRequest->query, $extraParams);

            if (null !== $searchCriteria['ad']) {
                return $this->singleAdsDom->singleAdsAction($searchCriteria['ad']);
            }

            $data = $this->adsPageCollector->collect($searchCriteria);
            $data['extra_params'] = $extraParams;
            $data['selected_city_name'] = $searchCriteria['city'] !== null ? $searchCriteria['city']->getName() : null;

            return $this->render('@Site/Site/adsView.html.twig',
                $this->adsPageFormatter->format($data)
            );
        } catch (\Throwable $throwable) {
            $this->logger->error(
                'Failed render ads list page',
                [
                    'query' => $listRequest->query->all(),
                    'extraParams' => $extraParams,
                ]
            );

            throw $throwable;
        }
    }

    /**
     * Set session for view ads, from list to grid and vice versa
     * @param $view
     * @return Response
     * @throws \InvalidArgumentException
     */
    public function changeAdsViewAction($view)
    {
        $this->requestStack->getSession()->set('view', $view);

        return new Response();
    }
}
