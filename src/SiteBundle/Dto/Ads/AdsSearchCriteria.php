<?php

declare(strict_types=1);

namespace SiteBundle\Dto\Ads;

/**
 * Normalized search criteria produced by `AdsListRequestParser`.
 *
 * The DTO carries wire-shape data (`string|array|null` unions to accept both
 * `?tagovi[]=x` and `?tagovi=x,y` forms). This value object holds the
 * post-normalization shape consumed by `SearchDataParser::parseSearch` and
 * eventually `AdsRepository::getPaginationQuery`.
 *
 * All list-shape fields (`tags`, `city`, `categories`, `size`, `color`,
 * `price`) are guaranteed lists of strings (never null, never CSV strings).
 * Scalar fields (`page`, `sort`) preserve nullability.
 */
final readonly class AdsSearchCriteria
{
    /**
     * @param list<string> $tags
     * @param list<string> $city
     * @param list<string> $categories
     * @param list<string> $size
     * @param list<string> $color
     * @param list<string> $price
     */
    public function __construct(
        public ?int $page = null,
        public ?string $sort = null,
        public array $tags = [],
        public array $city = [],
        public array $categories = [],
        public array $size = [],
        public array $color = [],
        public array $price = [],
    ) {
    }
}
