<?php

declare(strict_types=1);

namespace AdminBundle\Controller\InfoPages\Api;

use AdminBundle\Dto\InfoPage\AdsAutocompleteRequest;
use SiteBundle\Repository\AdsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

final class AdsAutocompleteController extends AbstractController
{
    private const DEFAULT_LIMIT = 30;

    public function __construct(
        private readonly AdsRepository $adsRepository
    ) {
    }

    #[Route('/api/info-pages/ads-autocomplete', name: 'admin.api.info_pages.ads_autocomplete', methods: ['GET'], options: ['expose' => true])]
    public function autocomplete(#[MapQueryString] ?AdsAutocompleteRequest $dto = null): JsonResponse
    {
        $q = $dto?->q ?? '';

        $rows = $this->adsRepository->searchForAutocomplete($q, self::DEFAULT_LIMIT);

        $results = [];

        foreach ($rows as $row) {
            $results[] = [
                'id' => $row['id'],
                'text' => $row['title'],
                'hasInfoPage' => null !== $row['infoPageId'],
                'infoPageId' => $row['infoPageId'],
            ];
        }

        return new JsonResponse(['results' => $results]);
    }
}
