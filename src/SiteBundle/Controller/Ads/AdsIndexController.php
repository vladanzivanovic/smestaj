<?php

declare(strict_types=1);

namespace SiteBundle\Controller\Ads;

use Psr\Log\LoggerInterface;
use SiteBundle\Collector\AdsPageCollector;
use SiteBundle\Controller\SiteController;
use SiteBundle\Dom\SingleAdsDom;
use SiteBundle\Dto\Ads\AdsListRequest;
use SiteBundle\Entity\Category;
use SiteBundle\Formatter\AdsPageFormatter;
use SiteBundle\Parser\SearchDataParser;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;

final class AdsIndexController extends SiteController
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly AdsPageCollector $adsPageCollector,
        private readonly AdsPageFormatter $adsPageFormatter,
        private readonly SearchDataParser $searchDataParser,
        private readonly SingleAdsDom $singleAdsDom,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function indexAction(
        #[MapEntity(mapping: ['category' => 'alias'])] Category $category,
        #[MapQueryString] ?AdsListRequest $listRequest = null,
        ?string $extraParams = null
    ): Response {
        try {
            $searchCriteria = $this->searchDataParser->parseSearch($listRequest, $extraParams);

            if (null !== $searchCriteria['ad']) {
                return $this->singleAdsDom->singleAdsAction($searchCriteria['ad']);
            }

            $data = $this->adsPageCollector->collect($searchCriteria, $category);
            $data['extra_params'] = $extraParams;
            $data['selected_city_name'] = null !== $searchCriteria['city'] ? $searchCriteria['city']->getName() : null;

            return $this->render(
                '@Site/Site/adsView.html.twig',
                $this->adsPageFormatter->format($data)
            );
        } catch (\Throwable $throwable) {
            $this->logger->error(
                'Failed render ads list page',
                [
                    'category' => $category->getAlias(),
                    'extraParams' => $extraParams,
                ]
            );

            throw $throwable;
        }
    }

    public function listOrDetailByParamsExceptCategoryAction(
        #[MapQueryString] ?AdsListRequest $listRequest = null,
        ?string $extraParams = null
    ): Response {
        try {
            $searchCriteria = $this->searchDataParser->parseSearch($listRequest, $extraParams);

            if (null !== $searchCriteria['ad']) {
                return $this->singleAdsDom->singleAdsAction($searchCriteria['ad']);
            }

            $data = $this->adsPageCollector->collect($searchCriteria);
            $data['extra_params'] = $extraParams;
            $data['selected_city_name'] = null !== $searchCriteria['city'] ? $searchCriteria['city']->getName() : null;

            return $this->render(
                '@Site/Site/adsView.html.twig',
                $this->adsPageFormatter->format($data)
            );
        } catch (\Throwable $throwable) {
            $this->logger->error(
                'Failed render ads list page',
                [
                    'extraParams' => $extraParams,
                ]
            );

            throw $throwable;
        }
    }

    /**
     * Set session for view ads, from list to grid and vice versa
     */
    public function changeAdsViewAction(string $view): Response
    {
        $this->requestStack->getSession()->set('view', $view);

        return new Response();
    }
}
