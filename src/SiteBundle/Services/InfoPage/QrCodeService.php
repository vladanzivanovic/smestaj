<?php

declare(strict_types=1);

namespace SiteBundle\Services\InfoPage;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

final class QrCodeService
{
    private const DEFAULT_SIZE = 300;

    private const MARGIN = 10;

    public function generatePng(string $absoluteUrl, int $size = self::DEFAULT_SIZE): string
    {
        $result = (new Builder(
            writer: new PngWriter(),
            data: $absoluteUrl,
            size: $size,
            margin: self::MARGIN,
        ))->build();

        return $result->getString();
    }
}
