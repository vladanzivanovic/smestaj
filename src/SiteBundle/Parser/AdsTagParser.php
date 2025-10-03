<?php

declare(strict_types=1);

namespace SiteBundle\Parser;

use SiteBundle\Entity\Ads;
use SiteBundle\Entity\Adshastags;
use SiteBundle\Repository\TagRepository;

class AdsTagParser
{
    private TagRepository $tagRepository;

    public function __construct(
        TagRepository $tagRepository
    ) {

        $this->tagRepository = $tagRepository;
    }
    public function parse(Ads $ads, array $tags): void
    {
        $collection = $ads->getHasTags();

        $collection->clear();

        foreach ($tags as $tagArray) {
            foreach ($tagArray as $id => $value) {
                $tag = $this->tagRepository->find($id);
                $hasTag = $this->create();
                $hasTag->setAds($ads)
                    ->setTag($tag)
                    ->setValue($value);

                $ads->addHasTag($hasTag);
            }
        }
    }

    private function create(): Adshastags
    {
        return new Adshastags();
    }
}