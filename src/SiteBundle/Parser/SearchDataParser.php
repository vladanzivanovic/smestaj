<?php

declare(strict_types=1);

namespace SiteBundle\Parser;

use SiteBundle\Dto\Ads\AdsListRequest;
use SiteBundle\Dto\Ads\AdsSearchCriteria;
use SiteBundle\Entity\Ads;
use SiteBundle\Entity\City;
use SiteBundle\Repository\AdsRepository;
use SiteBundle\Repository\CityRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Contracts\Translation\TranslatorInterface;

final class SearchDataParser
{
    private TranslatorInterface $translator;

    private ParameterBagInterface $bag;

    private CityRepository $cityRepository;

    private AdsRepository $adsRepository;

    private AdsListRequestParser $adsListRequestParser;

    public function __construct(
        TranslatorInterface $translator,
        ParameterBagInterface $bag,
        CityRepository $cityRepository,
        AdsRepository $adsRepository,
        AdsListRequestParser $adsListRequestParser
    ) {
        $this->translator = $translator;
        $this->bag = $bag;
        $this->cityRepository = $cityRepository;
        $this->adsRepository = $adsRepository;
        $this->adsListRequestParser = $adsListRequestParser;
    }

    public function parse(string $data): ParameterBag
    {
        $searchArray = explode('/', $data);
        $filters = [];
        $criteria = [];
        $sortMapper = $this->bag->get('shop')['sort_mapping'];

        for ($i = 0; $i < count($searchArray); $i++) {
            if ($i % 2 == 0) {
                $filters[] = $this->translator->trans($searchArray[$i], [], 'messages', 'en');

                continue;
            }

            $value = explode('+', $searchArray[$i]);

            if (end($filters) === 'sort') {
                $value = $sortMapper[$this->translator->trans($value[0], [], 'messages', 'en')];
            }

            $criteria[] = $value;
        }

        return new ParameterBag(array_combine($filters, $criteria));
    }

    /**
     * @return array{page:int, searchData:array<string, mixed>, city:?City, ad:?Ads}
     */
    public function parseSearch(?AdsListRequest $dto, ?string $extraParams): array
    {
        $criteria = null === $dto ? new AdsSearchCriteria() : $this->adsListRequestParser->parse($dto);

        $currentPage = $criteria->page ?? 1;
        $city = null;
        $ad = null;
        $searchData = [];
        $sortMapper = $this->bag->get('shop')['sort_mapping'];

        if (null !== $extraParams) {
            $extraParamsArray = explode('/', $extraParams);

            $city = $this->cityRepository->findOneBy(['alias' => $extraParamsArray[0]]);

            if (isset($extraParamsArray[1])) {
                $ad = $this->adsRepository->findOneBy(['alias' => $extraParamsArray[1]]);
            }
        }

        if (count($criteria->tags) > 0) {
            $searchData['tags'] = $criteria->tags;
        }

        if (count($criteria->city) > 0) {
            $searchData['city'] = $criteria->city;
        }

        if (count($criteria->categories) > 0) {
            $searchData['categories'] = $criteria->categories;
        }

        if (count($criteria->size) > 0) {
            $searchData['size'] = $criteria->size;
        }

        if (count($criteria->color) > 0) {
            $searchData['color'] = $criteria->color;
        }

        if (count($criteria->price) > 0) {
            $searchData['price'] = $criteria->price;
        }

        if (null !== $criteria->sort) {
            $searchData['sort'] = $criteria->sort;
            $sortKey = $this->translator->trans($criteria->sort, [], 'messages', 'rs');

            if (true === isset($sortMapper[$sortKey])) {
                $searchData['orderBy'] = $sortMapper[$sortKey];
            }
        }

        return [
            'page' => $currentPage,
            'searchData' => $searchData,
            'city' => $city,
            'ad' => $ad,
        ];
    }
}
