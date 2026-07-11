<?php

declare(strict_types=1);

namespace AdminBundle\Controller\InfoPages\Api;

use AdminBundle\Dto\InfoPage\InfoPageCreateRequest;
use AdminBundle\Handler\InfoPageEditHandler;
use SiteBundle\Entity\Ads;
use SiteBundle\Repository\AdsInfoPageRepository;
use SiteBundle\Repository\AdsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class InfoPageCreateController extends AbstractController
{
    public function __construct(
        private readonly AdsRepository $adsRepository,
        private readonly AdsInfoPageRepository $adsInfoPageRepository,
        private readonly InfoPageEditHandler $editHandler,
        private readonly UrlGeneratorInterface $urlGenerator
    ) {
    }

    #[Route('/api/info-pages', name: 'admin.api.info_pages.create', methods: ['POST'], options: ['expose' => true])]
    public function create(#[MapRequestPayload(acceptFormat: 'form')] InfoPageCreateRequest $dto): JsonResponse
    {
        $copyData = $this->parseBool($dto->copyData);

        $ads = $this->adsRepository->find($dto->linkedAdsId);

        if (false === $ads instanceof Ads) {
            return new JsonResponse(['error' => 'ads_not_found'], Response::HTTP_NOT_FOUND);
        }

        $existing = $this->adsInfoPageRepository->findOneByLinkedAds($ads);

        if (null !== $existing) {
            return new JsonResponse([
                'error' => 'exists',
                'existingId' => $existing->getId(),
            ], Response::HTTP_CONFLICT);
        }

        $entity = $this->editHandler->createFromAds($ads, $copyData);

        return new JsonResponse([
            'id' => $entity->getId(),
            'editUrl' => $this->urlGenerator->generate('admin.info_pages.edit', ['id' => $entity->getId()]),
        ], Response::HTTP_CREATED);
    }

    private function parseBool(?string $value): bool
    {
        if (null === $value) {
            return false;
        }

        $normalised = strtolower(trim($value));

        return 'true' === $normalised || '1' === $normalised || 'on' === $normalised;
    }
}
