<?php

declare(strict_types=1);

namespace AdminBundle\Formatter;

use SiteBundle\Entity\AdsInfoPage;
use SiteBundle\Repository\AdsInfoHasTagRepository;
use SiteBundle\Repository\TagRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class InfoPageEditResponseFormatter
{
    /**
     * Per Decision 3, info pages keep paired *_rs / *_en columns regardless of
     * the global `languages` parameter (which currently only lists `rs`).
     */
    private const INFO_PAGE_LANGUAGES = [
        'rs' => ['code' => 'rs', 'name' => 'Srpski', 'label' => 'Srpski'],
        'en' => ['code' => 'en', 'name' => 'English', 'label' => 'English'],
    ];

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly TagRepository $tagRepository,
        private readonly AdsInfoHasTagRepository $adsInfoHasTagRepository
    ) {
    }

    /**
     * @param array<string, string>|null $publishViolations
     *
     * @return array<string, mixed>
     */
    public function formatResponse(?AdsInfoPage $entity = null, ?array $publishViolations = null): array
    {
        $response = [
            'entity' => $entity,
            'languages' => self::INFO_PAGE_LANGUAGES,
            'tags' => $this->formatTags($this->tagRepository->getTags()),
            'linkedAds' => null,
            'publicUrls' => null,
            'qrPngUrl' => null,
            'publishViolations' => $publishViolations ?? [],
        ];

        if (null === $entity) {
            return $response;
        }

        $linkedAds = $entity->getLinkedAds();

        $response['linkedAds'] = [
            'id' => $linkedAds->getId(),
            'title' => $linkedAds->getTitle(),
            'alias' => method_exists($linkedAds, 'getAlias') ? $linkedAds->getAlias() : null,
        ];

        $response['publicUrls'] = [
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

        $response['qrPngUrl'] = $this->urlGenerator->generate(
            'admin.api.info_pages.qr',
            ['id' => $entity->getId()]
        );

        $response['selectedTags'] = $this->groupSelectedTags($this->adsInfoHasTagRepository->getByInfoPageGroupedByType($entity));

        return $response;
    }

    /**
     * @param array<int, array{type_label: string, type_name: string}> $tags
     *
     * @return array<string, array{name: string, tags: array<int, array<string, mixed>>}>
     */
    private function formatTags(array $tags): array
    {
        $out = [];

        foreach ($tags as $tag) {
            $out[$tag['type_label']]['name'] = $tag['type_name'];
            $out[$tag['type_label']]['tags'][] = $tag;
        }

        return $out;
    }

    /**
     * @param array<int, array{tag_id: int, tag_type_label: string}> $rows
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function groupSelectedTags(array $rows): array
    {
        $out = [];

        foreach ($rows as $row) {
            $out[$row['tag_type_label']][(int) $row['tag_id']] = $row;
        }

        return $out;
    }
}
