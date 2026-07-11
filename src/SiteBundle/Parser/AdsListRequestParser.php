<?php

declare(strict_types=1);

namespace SiteBundle\Parser;

use SiteBundle\Dto\Ads\AdsListRequest;
use SiteBundle\Dto\Ads\AdsSearchCriteria;

final class AdsListRequestParser
{
    public function parse(AdsListRequest $dto): AdsSearchCriteria
    {
        return new AdsSearchCriteria(
            page: $dto->page,
            sort: $dto->sort,
            tags: $this->normalizeList($dto->tags),
            city: $this->normalizeList($dto->city),
            categories: $this->normalizeList($dto->categories),
            size: $this->normalizeList($dto->size),
            color: $this->normalizeList($dto->color),
            price: $this->normalizeList($dto->price),
        );
    }

    /**
     * @param string|array<int|string, mixed>|null $value
     *
     * @return list<string>
     */
    private function normalizeList(string|array|null $value): array
    {
        if (null === $value) {
            return [];
        }

        if (true === is_string($value)) {
            $value = explode(',', $value);
        }

        $list = [];

        foreach ($value as $item) {
            $item = trim((string) $item);

            if ('' === $item) {
                continue;
            }

            $list[] = $item;
        }

        return $list;
    }
}
