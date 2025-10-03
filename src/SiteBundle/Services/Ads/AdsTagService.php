<?php

namespace SiteBundle\Services\Ads;

use Doctrine\ORM\EntityManager;
use SiteBundle\Entity\Ads;
use SiteBundle\Repository\AdshastagsRepository;
use SiteBundle\Services\ServiceContainer;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * @deprecated
 */
class AdsTagService
{
    private $adshastagsRepository;

    /**
     * @param AdshastagsRepository $adshastagsRepository
     */
    public function __construct(
        AdshastagsRepository $adshastagsRepository
    ) {
        $this->adshastagsRepository = $adshastagsRepository;
    }

    /**
     * @param Ads $ads
     *
     * @return array
     */
    public function getTagsByAdsId(Ads $ads)
    {
        $tags = $this->adshastagsRepository->getByAds($ads);

        return $this->formatTagsByType($tags);
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
