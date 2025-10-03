<?php

declare(strict_types=1);

namespace SiteBundle\Formatter;

use SiteBundle\Entity\Ads;
use SiteBundle\View\AdView;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

final class AdsPageFormatter
{
    private TagFormatter $tagFormatter;

    private ParameterBagInterface $bag;

    private AdView $adView;

    public function __construct(
        TagFormatter $tagFormatter,
        ParameterBagInterface $bag,
        AdView $adView
    ) {
        $this->tagFormatter = $tagFormatter;
        $this->bag = $bag;
        $this->adView = $adView;
    }

    public function format(array $data): array
    {
        $sortMapping = $this->bag->get('shop')['sort_mapping'];
        $data['tags'] = $this->tagFormatter->formatPerType($data['tags']);


//        if (null !== $data['search_criteria'] && $data['search_criteria']->has('sort')) {
//            $data['search_criteria']->set('sort', [array_search($data['search_criteria']->get('sort'), $sortMapping)]);
//        }

        $data['json_ld_data'] = $this->getLdJsonData($data['ads']['data']);
        $data['ads']['data'] = $this->reorderAds($data['ads']['data']);



//        dd($data);
//        dd($data['ads']['data']);

        return $data;
    }

    /**
     * @param Ads[] $ads
     * @return array
     */
    private function reorderAds(array $ads): array
    {
        $payedAds = [];
        $basicAds = [];

        foreach ($ads as $ad) {
            $view = $this->adView->view($ad);
            $view['tags'] = $this->extractTags($view['tags']);
//            try {
//                $view['tags'] = $this->tagFormatter->formatPerType($view['tags']);
//            } catch (\Throwable $throwable) {
//                dd($view['tags']);
//            }

            if (null !== $ad->getLastPayment()) {
                $payedAds[$ad->getId()] = $view;

                continue;
            }

            $basicAds[$ad->getId()] = $view;
        }

        return array_values($payedAds + $basicAds);
    }

    private function extractTags(array $tags): array
    {
        $filteredTags = [];

        foreach ($tags as $tagType) {
            foreach ($tagType as $tag) {
                if (in_array($tag['slug'], ['parking', 'bazen', 'wifi-internet', 'sopstveno-kupatilo', 'kuhinja'])) {
                    $filteredTags[] = $tag;
                }
            }
        }

        return $filteredTags;
    }

    private function getLdJsonData(array $ads): array
    {
        $ldData = [];

        foreach ($ads as $ad) {
            $ldData[] = $this->adView->getLdView($ad);
        }

        return $ldData;
    }
}
