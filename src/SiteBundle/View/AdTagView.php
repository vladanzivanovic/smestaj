<?php

declare(strict_types=1);

namespace SiteBundle\View;

use SiteBundle\Entity\Ads;
use SiteBundle\Entity\Adshastags;
use SiteBundle\Entity\Tag;
use SiteBundle\Repository\AdshastagsRepository;

final class AdTagView
{
    private AdshastagsRepository $adshastagsRepository;

    private TagView $tagView;

    /**
     * @param AdshastagsRepository $adshastagsRepository
     */
    public function __construct(
        AdshastagsRepository $adshastagsRepository,
        TagView $tagView
    ) {
        $this->adshastagsRepository = $adshastagsRepository;
        $this->tagView = $tagView;
    }

    /**
     * @param Adshastags[] $adsHasTags
     *
     */
    public function viewGroupedByType(array $adsHasTags): array
    {
        $formattedArray = [];

        foreach ($adsHasTags as $adsHasTag) {
            $tag = $adsHasTag->getTag();

            $tagView = $this->tagView->view($tag);

            $typeSlug = $tag->getTagType()->getLabel();

            $tagView['value'] = $adsHasTag->getValue();

            $formattedArray[$typeSlug][$tag->getId()] = $tagView;
        }

        return $formattedArray;
    }

    private function formatTagsByType(array $tags):array
    {
        $formattedArray = [];

        foreach ($tags as $tag) {
            $formattedArray[$tag['tag_type_label']][$tag['tag_id']] = $tag;
        }

        return $formattedArray;
    }
}
