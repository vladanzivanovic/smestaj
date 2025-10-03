<?php

declare(strict_types=1);

namespace SiteBundle\Formatter;

final class DashboardFormatter
{
    public function format(array $data): array
    {
        $data['tags'] = $this->formatTags($data['tags']);

        return $data;
    }

    private function formatTags(array $tags): array
    {
        $formattedTags = [];

        foreach ($tags as $tag) {
            $formattedTags[$tag['type_label']]['name'] = $tag['type_name'];
            $formattedTags[$tag['type_label']]['tags'][] = $tag;
        }

        return $formattedTags;
    }
}