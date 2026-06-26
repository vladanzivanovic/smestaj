<?php

declare(strict_types=1);

namespace SiteBundle\Dto\Index;

final class IndexRequest
{
    public function __construct(
        public readonly ?string $token = null,
    ) {
    }
}
