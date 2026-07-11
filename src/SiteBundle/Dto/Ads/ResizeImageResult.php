<?php

declare(strict_types=1);

namespace SiteBundle\Dto\Ads;

/**
 * Result of a successful temp-image resize orchestrated by
 * SiteBundle\Parser\ResizeImageRequestParser::parse.
 *
 * The controller serialises these properties one-for-one into the JSON
 * response body (preserved wire keys: file, originalFilePath, fileName,
 * isMain, isImage) — verified against
 * src/SiteBundle/Resources/public/js/Handler/AdsHandler.js response
 * consumption.
 */
final class ResizeImageResult
{
    public function __construct(
        public readonly string $file,
        public readonly string $originalFilePath,
        public readonly string $fileName,
        public readonly bool $isMain,
        public readonly bool $isImage,
    ) {
    }
}
