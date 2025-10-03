<?php

declare(strict_types=1);

namespace SiteBundle\Parser;

use SiteBundle\Entity\Ads;
use SiteBundle\Entity\Youtubeinfo;

class YouTubeParser
{
    public function parse(Ads $ads, array $youtubeData): void
    {
        $collection = $ads->getYoutube();
        $collection->clear();

        foreach ($youtubeData as $youtube) {
            if(isset($youtube['isDeleted']) && true === $youtube['isDeleted']) {
                continue;
            }

            $entity = $this->create();
            $entity->setYoutubeid($youtube['YouTubeId']);
            $entity->setTitle($youtube['Title']);
            $entity->setChaneltitle($youtube['ChanelTitle']);
            $entity->setChannelid($youtube['ChannelId']);
            $entity->setSyscreatedtime($ads->getSysCreatedTime());
            $entity->setSyscreatorid($ads->getSysCreatedUserId());
            $entity->setThumbnails($youtube['Thumbnails']);

            $ads->addYoutube($entity);
        }
    }

    private function create(): Youtubeinfo
    {
        return new Youtubeinfo();
    }
}