<?php

declare(strict_types=1);

namespace SiteBundle\Formatter;

final class TagFormatter
{
    public function formatPerType(array $tags): array
    {
        $formattedTags = [];

        foreach ($tags as $tag) {
            $formattedTags[$tag['type_label']]['name'] = $tag['type_name'];
            $formattedTags[$tag['type_label']]['tags'][] = $tag;
        }

        return $formattedTags;
    }
}
