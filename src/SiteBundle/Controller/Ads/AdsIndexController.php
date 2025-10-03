<?php

namespace SiteBundle\Controller\Ads;

use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SiteBundle\Collector\AdsPageCollector;
use SiteBundle\Controller\SiteController;
use SiteBundle\Dom\SingleAdsDom;
use SiteBundle\Entity\Ads;
use SiteBundle\Formatter\AdsPageFormatter;
use SiteBundle\Parser\SearchDataParser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class AdsIndexController extends SiteController
{
    private SessionInterface $session;

    private AdsPageCollector $adsPageCollector;

    private AdsPageFormatter $adsPageFormatter;

    private SearchDataParser $searchDataParser;

    private SingleAdsDom $singleAdsDom;

    private LoggerInterface $logger;

    public function __construct(
        SessionInterface $session,
        AdsPageCollector $adsPageCollector,
        AdsPageFormatter $adsPageFormatter,
        SearchDataParser $searchDataParser,
        SingleAdsDom $singleAdsDom,
        LoggerInterface $logger
    ) {
        $this->session = $session;
        $this->adsPageCollector = $adsPageCollector;
        $this->adsPageFormatter = $adsPageFormatter;
        $this->searchDataParser = $searchDataParser;
        $this->singleAdsDom = $singleAdsDom;
        $this->logger = $logger;
    }

    /**
     * @param string      $category
     * @param Request     $request
     * @param string|null $extraParams
     *
     * @return Response
     */
    public function indexAction(string $category, Request $request, ?string $extraParams): Response
    {
        try {
            $searchCriteria = $this->searchDataParser->parseSearch($request->query, $extraParams);

            if (null !== $searchCriteria['ad']) {
                return $this->singleAdsDom->singleAdsAction($searchCriteria['ad']);
            }

            $data = $this->adsPageCollector->collect($category, $searchCriteria);
            $data['extra_params'] = $extraParams;
            $data['selected_city_name'] = $searchCriteria['city'] !== null ? $searchCriteria['city']->getName() : null;

//            dd($this->adsPageFormatter->format($data));
            return $this->render('@Site/Site/adsView.html.twig',
                $this->adsPageFormatter->format($data)
            );
        } catch (\Throwable $throwable) {
            $this->logger->error(
                'Failed render ads list page',
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
     * Set session for view ads, from list to grid and vice versa
     * @param $view
     * @return Response
     * @throws \InvalidArgumentException
     */
    public function changeAdsViewAction($view)
    {
        $this->session->set('view', $view);

        return new Response();
    }
}
