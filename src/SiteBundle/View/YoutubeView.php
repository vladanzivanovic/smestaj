<?php

declare(strict_types=1);

namespace SiteBundle\View;


use SiteBundle\Entity\Youtubeinfo;

final class YoutubeView
{
    public function view(Youtubeinfo $youtube): array
    {
        return [
            'id' => $youtube->getId(),
            'ads_id' => $youtube->getAdsid()->getId(),
            'youtube_id' => $youtube->getYoutubeid(),
            'title' => $youtube->getTitle(),
            'thumbnails' => $youtube->getThumbnails(),
            '_link' => 'https://www.youtube.com/embed/'.$youtube->getYoutubeId()
        ];
    }
}
