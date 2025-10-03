<?php

declare(strict_types=1);

namespace SiteBundle\Parser;

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

    public function __construct(
        TranslatorInterface $translator,
        ParameterBagInterface $bag,
        CityRepository $cityRepository,
        AdsRepository $adsRepository
    ) {
        $this->translator = $translator;
        $this->bag = $bag;
        $this->cityRepository = $cityRepository;
        $this->adsRepository = $adsRepository;
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

    public function parseSearch(?ParameterBag $bag, ?string $extraParams): array
    {
        $currentPage = 1;
        $city = null;
        $ad = null;
        $searchData = [];
        $sortMapper = $this->bag->get('shop')['sort_mapping'];

        if ($bag->has('stranica')) {
            $currentPage = $bag->getInt('stranica');
        }

        if (null !== $extraParams) {
            $extraParamsArray = explode('/', $extraParams);

            $city = $this->cityRepository->findOneBy(['alias' => $extraParamsArray[0]]);

            if (isset($extraParamsArray[1])) {
                $ad = $this->adsRepository->findOneBy(['alias' => $extraParamsArray[1]]);
            }
        }

        foreach ($bag->all() as $filter => $items) {
            $filterTrans = $this->translator->trans($filter, [], 'messages', 'en');

            if ($filterTrans !== 'page') {
                $searchData[$filterTrans] = $items;
            }

            if ($filterTrans === 'sort') {
                $sort = $this->translator->trans($items, [], 'messages', 'rs');
                $searchData['orderBy'] = $sortMapper[$sort];
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
