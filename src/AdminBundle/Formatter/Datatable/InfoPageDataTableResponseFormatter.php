<?php

declare(strict_types=1);

namespace AdminBundle\Formatter\Datatable;

use AdminBundle\Model\DataTableModel;
use DateTimeInterface;
use SiteBundle\Entity\Ads;
use SiteBundle\Entity\AdsInfoPage;

final class InfoPageDataTableResponseFormatter
{
    use DataTableResponseTrait;

    /**
     * @param array<int, AdsInfoPage> $entities
     *
     * @return array<string, mixed>
     */
    public function formatResponse(DataTableModel $tableModel, array $entities, int $total): array
    {
        $rows = [];

        foreach ($entities as $entity) {
            $rows[] = $this->buildRow($entity);
        }

        return $this->response($tableModel, $rows, $total);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildRow(AdsInfoPage $entity): array
    {
        $linkedAds = $entity->getLinkedAds();

        return [
            'id' => $entity->getId(),
            'propertyName' => $entity->getPropertyName(),
            'host' => $this->buildHostName($entity),
            'addressCity' => $entity->getAddressCity(),
            'linkedAdsTitle' => $this->getLinkedAdsTitle($linkedAds),
            'createdAt' => $this->formatDate($entity->getCreatedAt()),
            'updatedAt' => $this->formatDate($entity->getUpdatedAt()),
            'published' => $entity->isPublished(),
            'slug' => $entity->getSlug(),
            'actions' => $this->buildActions($entity),
        ];
    }

    private function buildHostName(AdsInfoPage $entity): string
    {
        $first = $entity->getHostFirstName() ?? '';
        $last = $entity->getHostLastName() ?? '';

        return trim($first . ' ' . $last);
    }

    private function getLinkedAdsTitle(Ads $linkedAds): ?string
    {
        return $linkedAds->getTitle();
    }

    private function formatDate(?DateTimeInterface $date): ?string
    {
        if (null === $date) {
            return null;
        }

        return $date->format(DateTimeInterface::ATOM);
    }

    private function buildActions(AdsInfoPage $entity): string
    {
        $id = (int) $entity->getId();
        $slug = $entity->getSlug();
        $published = $entity->isPublished() ? '1' : '0';

        $editFragment = sprintf(
            '<button type="button" class="btn btn-primary btn-sm js-info-page-edit" data-id="%d" data-slug="%s">Izmeni</button>',
            $id,
            htmlspecialchars($slug, ENT_QUOTES | ENT_HTML5)
        );

        $publicFragment = sprintf(
            '<button type="button" class="btn btn-info btn-sm js-info-page-view" data-id="%d" data-slug="%s">Prikaz</button>',
            $id,
            htmlspecialchars($slug, ENT_QUOTES | ENT_HTML5)
        );

        $publishFragment = sprintf(
            '<button type="button" class="btn btn-warning btn-sm js-info-page-toggle-publish" data-id="%d" data-slug="%s" data-published="%s">%s</button>',
            $id,
            htmlspecialchars($slug, ENT_QUOTES | ENT_HTML5),
            $published,
            true === $entity->isPublished() ? 'Sakrij' : 'Objavi'
        );

        $deleteFragment = sprintf(
            '<button type="button" class="btn btn-danger btn-sm js-info-page-delete" data-id="%d" data-slug="%s">Obriši</button>',
            $id,
            htmlspecialchars($slug, ENT_QUOTES | ENT_HTML5)
        );

        return $editFragment . ' ' . $publicFragment . ' ' . $publishFragment . ' ' . $deleteFragment;
    }
}
