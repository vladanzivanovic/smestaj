<?php

namespace SiteBundle\Twig;

use SiteBundle\Entity\Category;
use SiteBundle\Repository\AdsRepository;
use SiteBundle\Repository\CityRepository;
use SiteBundle\Services\CategoryService;

class CityExtension extends \Twig_Extension
{
    private CityRepository $cityRepository;

    public function __construct(
        CityRepository $cityRepository
    ) {
        $this->cityRepository = $cityRepository;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('cities', [$this, 'getCities'])
        ];
    }

    public function getCities(): array
    {
        $cities = $this->cityRepository->getCitiesWithHavingAds();

        $chunk = \ceil(\round(count($cities)/6, PHP_ROUND_HALF_UP));

        return array_chunk($cities, $chunk);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'category_extension';
    }
}
