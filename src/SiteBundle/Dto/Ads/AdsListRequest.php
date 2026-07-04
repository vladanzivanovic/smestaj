<?php

declare(strict_types=1);

namespace SiteBundle\Dto\Ads;

use Symfony\Component\HttpFoundation\ParameterBag;

final readonly class AdsListRequest
{
    public function __construct(
        public ParameterBag $query,
        public ?string $extraParams = null,
    ) {
    }
}
