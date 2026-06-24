<?php

declare(strict_types=1);

namespace SiteBundle\Parser;

use SiteBundle\Entity\AdsInfoHasTag;
use SiteBundle\Entity\AdsInfoPage;
use SiteBundle\Entity\Tag;
use SiteBundle\Repository\TagRepository;

final class InfoPageTagParser
{
    private TagRepository $tagRepository;

    public function __construct(TagRepository $tagRepository)
    {
        $this->tagRepository = $tagRepository;
    }

    /**
     * @param array<string, array<int, string>> $tags
     */
    public function parse(AdsInfoPage $infoPage, array $tags): void
    {
        $desired = $this->flattenDesired($tags);
        $existing = $this->indexExistingByTagId($infoPage);

        foreach ($existing as $tagId => $row) {
            if (false === array_key_exists($tagId, $desired)) {
                $infoPage->removeHasTag($row);
            }
        }

        foreach ($desired as $tagId => $value) {
            if (true === array_key_exists($tagId, $existing)) {
                $existing[$tagId]->setValue($value);

                continue;
            }

            $tag = $this->tagRepository->find($tagId);

            if (false === $tag instanceof Tag) {
                continue;
            }

            $row = new AdsInfoHasTag();
            $row->setInfoPage($infoPage)
                ->setTag($tag)
                ->setValue($value);

            $infoPage->addHasTag($row);
        }
    }

    /**
     * @param array<string, array<int, string>> $tags
     *
     * @return array<int, string>
     */
    private function flattenDesired(array $tags): array
    {
        $desired = [];

        foreach ($tags as $tagArray) {
            if (false === is_array($tagArray)) {
                continue;
            }

            foreach ($tagArray as $tagId => $value) {
                if (true === ('' === (string) $value || null === $value)) {
                    continue;
                }

                $desired[(int) $tagId] = (string) $value;
            }
        }

        return $desired;
    }

    /**
     * @return array<int, AdsInfoHasTag>
     */
    private function indexExistingByTagId(AdsInfoPage $infoPage): array
    {
        $existing = [];

        foreach ($infoPage->getHasTags() as $row) {
            $existing[(int) $row->getTag()->getId()] = $row;
        }

        return $existing;
    }
}
