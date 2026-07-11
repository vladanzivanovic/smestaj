<?php

declare(strict_types=1);

namespace AdminBundle\Dto\InfoPage;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Query payload for GET /api/info-pages/ads-autocomplete (route
 * `admin.api.info_pages.ads_autocomplete`). Populated by
 * `#[MapQueryString]` from the `?q=<term>` query string.
 *
 * Empty query is legitimate (returns `results: []`), so the DTO is
 * passed nullable in the controller signature and the length guard is
 * a soft upper bound only.
 *
 * No CSRF on GET.
 */
final class AdsAutocompleteRequest
{
    #[Assert\Length(max: 100)]
    public string $q = '';
}
