<?php

declare(strict_types=1);

namespace SiteBundle\Controller\InfoPage;

use SiteBundle\Entity\AdsInfoPage;
use SiteBundle\Repository\AdsInfoPageRepository;
use SiteBundle\Services\InfoPage\GoogleReviewLinkBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class InfoPageController extends AbstractController
{
    public function __construct(
        private readonly AdsInfoPageRepository $infoPageRepository,
        private readonly GoogleReviewLinkBuilder $googleReviewLinkBuilder,
        private readonly UrlGeneratorInterface $urlGenerator
    ) {
    }

    #[Route(
        path: '/apartman-info/{slug}',
        name: 'site.info_page.view',
        requirements: ['slug' => '[a-z0-9-]+'],
        defaults: ['_locale' => 'rs'],
        methods: ['GET']
    )]
    #[Route(
        path: '/en/apartment-info/{slug}',
        name: 'site.info_page.view_en',
        requirements: ['slug' => '[a-z0-9-]+'],
        defaults: ['_locale' => 'en'],
        methods: ['GET']
    )]
    public function view(string $slug): Response
    {
        $entity = $this->infoPageRepository->findOneBySlug($slug);

        if (null === $entity || false === $entity->isPublished()) {
            return $this->render('@Site/InfoPage/notAvailable.html.twig', [
                'slug' => $slug,
            ]);
        }

        return $this->render('@Site/InfoPage/view.html.twig', [
            'entity' => $entity,
            'googleReviewUrl' => $this->googleReviewLinkBuilder->build($entity->getGoogleReviewInput()),
            'publicUrls' => $this->buildPublicUrls($entity),
        ]);
    }

    /**
     * @return array{rs: string, en: string}
     */
    private function buildPublicUrls(AdsInfoPage $entity): array
    {
        return [
            'rs' => $this->urlGenerator->generate(
                'site.info_page.view',
                ['slug' => $entity->getSlug()],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
            'en' => $this->urlGenerator->generate(
                'site.info_page.view_en',
                ['slug' => $entity->getSlug()],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
        ];
    }
}
