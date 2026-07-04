<?php

declare(strict_types=1);

namespace SiteBundle\Dto\Ads;

use Symfony\Component\HttpFoundation\ParameterBag;

final readonly class AdsUpdateRequest
{
    public function __construct(
        public ParameterBag $body,
        public string $csrfToken,
    ) {
    }
}
