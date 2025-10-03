<?php

declare(strict_types=1);

namespace SiteBundle\View;

use SiteBundle\Entity\Tag;

final class TagView
{
    public function view(Tag $tag): array
    {
        $type = $tag->getTagType();

        $view = [
            'id' => $tag->getId(),
            'slug' => $tag->getSlug(),
            'icon' => $tag->getIcon(),
            'title' => $tag->getName(),
            'type' => [
                'id' => $type->getId(),
                'title' => $type->getName(),
                'slug' => $type->getLabel(),
            ],
        ];

        return $view;
    }
}
