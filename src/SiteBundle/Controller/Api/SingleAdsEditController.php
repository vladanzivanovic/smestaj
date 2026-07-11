<?php

declare(strict_types=1);

namespace SiteBundle\Controller\Api;

use SiteBundle\Controller\SiteController;
use SiteBundle\Entity\Ads;
use SiteBundle\Repository\AdsRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Class SingleAdsEditController
 * @package SiteBundle\Controller\Api
 */
final class SingleAdsEditController extends SiteController
{
    private AdsRepository $adsRepository;
    private RequestStack $requestStack;

    public function __construct(
        AdsRepository $adsRepository,
        RequestStack $requestStack
    ) {
        $this->adsRepository = $adsRepository;
        $this->requestStack = $requestStack;
    }

    #[Route('/api/set-ad-number-counter/{alias}', methods: ['PUT'], name: 'set_ad_number_counter')]
    public function increasePhoneCount(#[MapEntity(mapping: ['alias' => 'alias'])] Ads $ads): JsonResponse
    {
        if (false === $this->requestStack->getSession()->has(Ads::AD_NUMBER_CLICKED)) {
            $count = $ads->getPhoneNumberCounter();
            $ads->setPhoneNumberCounter($count + 1);
            $ads->setSendEmail(false);

            $this->adsRepository->flush();

            $this->requestStack->getSession()->set(Ads::AD_NUMBER_CLICKED, true);
        }

        return $this->json(null);
    }
}
