<?php

declare(strict_types=1);

namespace SiteBundle\Dto\Image;

final class LogoTrackingRequest
{
    public function __construct(
        public readonly ?string $code = null,
    ) {
    }
}
