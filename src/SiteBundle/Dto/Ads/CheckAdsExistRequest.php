<?php

declare(strict_types=1);

namespace SiteBundle\Dto\Ads;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class CheckAdsExistRequest
{
    public function __construct(
        public readonly ?int $id = null,
        #[SerializedName('Title')]
        public readonly ?string $title = null,
    ) {
    }
}
