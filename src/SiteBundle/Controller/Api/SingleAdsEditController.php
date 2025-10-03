<?php

namespace SiteBundle\Controller\Api;

use SiteBundle\Controller\SiteController;
use SiteBundle\Entity\Ads;
use SiteBundle\Repository\AdsRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SingleAdsEditController
 * @package SiteBundle\Controller\Api
 */
final class SingleAdsEditController extends SiteController
{
    private AdsRepository $adsRepository;
    private SessionInterface $session;

    public function __construct(
        AdsRepository $adsRepository,
        SessionInterface $session
    ) {
        $this->adsRepository = $adsRepository;
        $this->session = $session;
    }

    /**
     * @Route("/api/set-ad-number-counter/{alias}", methods={"PUT"}, name="set_ad_number_counter")
     * @param Ads $ads
     *
     * @return JsonResponse
     */
    public function increasePhoneCount(Ads $ads): JsonResponse
    {
        if (false === $this->session->has(Ads::AD_NUMBER_CLICKED)) {
            $count = $ads->getPhoneNumberCounter();
            $ads->setPhoneNumberCounter($count + 1);
            $ads->setSendEmail(false);

            $this->adsRepository->flush();

            $this->session->set(Ads::AD_NUMBER_CLICKED, true);
        }

        return $this->json(null);
    }
}