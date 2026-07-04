<?php

declare(strict_types=1);

namespace SiteBundle\Dto\Ads;

use Symfony\Component\DependencyInjection\Attribute\Exclude;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[Exclude]
final readonly class ResizeImageRequest
{
    public function __construct(
        public UploadedFile $tmpImage,
    ) {
    }
}
