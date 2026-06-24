<?php

declare(strict_types=1);

namespace SiteBundle\Services\InfoPage;

final class GoogleReviewLinkBuilder
{
    private const PLACE_ID_REGEX = '/^[A-Za-z0-9_-]{20,}$/';

    private const PLACE_ID_URL = 'https://search.google.com/local/writereview?placeid=';

    public function build(?string $input): ?string
    {
        if (null === $input) {
            return null;
        }

        $trimmed = trim($input);

        if ('' === $trimmed) {
            return null;
        }

        if (true === str_starts_with($trimmed, 'http://') || true === str_starts_with($trimmed, 'https://')) {
            return $trimmed;
        }

        if (1 === preg_match(self::PLACE_ID_REGEX, $trimmed)) {
            return self::PLACE_ID_URL . $trimmed;
        }

        return null;
    }
}
