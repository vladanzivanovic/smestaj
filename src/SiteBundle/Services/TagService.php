<?php

namespace SiteBundle\Services;

use SiteBundle\Repository\CategoryRepository;
use SiteBundle\Repository\TagRepository;
use SiteBundle\Services\ServiceContainer;

/**
 * Class TagService
 */
class TagService
{
    private TagRepository $tagRepository;

    /**
     * @param TagRepository $tagRepository
     */
    public function __construct(
        TagRepository $tagRepository
    ) {
        $this->tagRepository = $tagRepository;
    }

    /**
     * @return array
     */
    public function getTags()
    {
        return $this->tagRepository->getTags();
    }

    /**
     * @param $tags
     *
     * @return array
     */
    public function formatTags(array $tags): array
    {
        $formattedTags = [];

        foreach ($tags as $tag) {
            $formattedTags[$tag['type_label']]['name'] = $tag['type_name'];
            $formattedTags[$tag['type_label']]['tags'][] = $tag;
        }

        return $formattedTags;
    }
}