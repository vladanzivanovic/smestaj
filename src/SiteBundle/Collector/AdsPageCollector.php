<?php

declare(strict_types=1);

namespace SiteBundle\Collector;

use SiteBundle\Entity\Category;
use SiteBundle\Entity\City;
use SiteBundle\Repository\AdshastagsRepository;
use SiteBundle\Repository\AdsRepository;
use SiteBundle\Repository\CategoryRepository;
use SiteBundle\Repository\CityRepository;
use SiteBundle\Repository\TagRepository;
use SiteBundle\Services\PaginationService;
use function Clue\StreamFilter\fun;

final class AdsPageCollector
{
    private CategoryRepository $categoryRepository;

    private TagRepository $tagRepository;

    private CityRepository $cityRepository;

    private AdsRepository $adsRepository;

    private PaginationService $paginationService;

    private AdshastagsRepository $adshastagsRepository;

    public function __construct(
        CategoryRepository $categoryRepository,
        TagRepository $tagRepository,
        CityRepository $cityRepository,
        AdsRepository $adsRepository,
        PaginationService $paginationService,
        AdshastagsRepository $adshastagsRepository
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->tagRepository = $tagRepository;
        $this->cityRepository = $cityRepository;
        $this->adsRepository = $adsRepository;
        $this->paginationService = $paginationService;
        $this->adshastagsRepository = $adshastagsRepository;
    }

    public function collect(string $selectedCategory, array $searchData): array
    {
        $searchCriteria = $searchData['searchData'];
        $data = [];
        $city = $searchData['city'];
        $currentPage = $searchData['page'];
        $category = null;

        if ($selectedCategory !== 'smestaj') {
            $category = $this->categoryRepository->findOneBy(['alias' => $selectedCategory]);
        }

        $selectedOptions = $this->collectSelectedOptions($searchCriteria);

        $filters = $this->getFilterOptions();

        $minPrice = $this->adsRepository->getAdMinPrice($category, $city);

        $data['ads'] = $this->collectAds($currentPage, $category, $city, $searchCriteria);

        $data['min_price'] = $minPrice !== null ? $minPrice : 10;
        $data['current_page'] = $currentPage;

//        dd($data);
//        $this->collectAdsTags($data['ads']['data']);

        unset($searchCriteria['orderBy']);

        $data['search_criteria'] = count($searchCriteria) > 0 ? $searchCriteria : null;
        $data['selected_category'] = $category;

        return $filters + $selectedOptions + $data;
    }

    private function getFilterOptions(): array
    {
        return [
            'tags' => $this->tagRepository->getTagsForFilter(),
            'cities' => $this->cityRepository->getAllCities(),
        ];
    }

    private function collectSelectedOptions(?array $searchData): array
    {
        $collection = [];

        if (null !== $searchData) {
            $collection['tags'] = $searchData;
        }

        return $collection;
    }

    private function collectAds(int $currentPage, ?Category $category, ?City $city, array $searchCriteria): array
    {
        $adsDql = $this->adsRepository->getPaginationQuery($category, $city, $searchCriteria);

        $data = $this->paginationService->pagination($adsDql, $currentPage, 12);

        $sort = null;

//        if (null !== $searchCriteria && $searchCriteria->has('sort')) {
//            $sort = $searchCriteria->get('sort');
//        }

//        $data['data'] = $this->adsRepository->getSortedList(array_column($data['data'], 'id'), $sort);

        return $data;
    }

    private function collectAdsTags(array &$ads): void
    {
        $ads = array_map(function ($ad) {
            $tags = $this->adshastagsRepository->getByAdsForGridPage($ad['id']);

            $ad['tags'] = $tags;

            return $ad;
        }, $ads);
    }
}
