<?php

declare(strict_types=1);

namespace SiteBundle\Dto\Ads;

use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Query-string DTO for the ads-list endpoints (S6/S7/S8/S9).
 *
 * The URL scheme is Serbian by convention (verified via `paginate.html.twig` and
 * `AdsPageRouting.generateUrl` in the frontend), so on-wire query keys are
 * `stranica` / `sortiranje` / `tagovi` / `mesto` / `kategorije` / `veličina` /
 * `boja` / `cena`. Each PHP property carries a matching `#[SerializedName]`
 * so `#[MapQueryString]` can hydrate directly.
 *
 * Only `page` / `sort` / `tags` affect actual DB filtering (see
 * `AdsRepository::getPaginationQuery` and `SearchDataParser::parseSearch`). The
 * remaining fields are decorative — surfaced by `AdsPageCollector` for the
 * "selected filters" chip display in Twig, never used to filter results.
 *
 * `city` is a plain query-key filter for the chip display; the effective
 * city-entity filter is resolved from the `extraParams` path segment in the
 * controller, not from this DTO.
 *
 * English aliases from `SearchService.js` (e.g. `?tags=1,2,3`) are NOT bound —
 * the current bag-iterating parser already mishandles that CSV form (binds a
 * single string to an SQL `IN` clause) so nothing behaviourally sound depends
 * on it. `sortName` / `sortDirection` were likewise no-ops in the old parser
 * (only `sort` triggered the `sort_mapping` lookup) and are dropped here.
 */
final class AdsListRequest
{
    #[SerializedName('stranica')]
    #[Assert\Positive]
    public ?int $page = null;

    #[SerializedName('sortiranje')]
    #[Assert\Length(max: 100)]
    public ?string $sort = null;

    /**
     * @var list<string|int>|string|null
     */
    #[SerializedName('tagovi')]
    public string|array|null $tags = null;

    /**
     * @var list<string>|string|null
     */
    #[SerializedName('mesto')]
    public string|array|null $city = null;

    /**
     * @var list<string>|string|null
     */
    #[SerializedName('kategorije')]
    public string|array|null $categories = null;

    /**
     * @var list<string>|string|null
     */
    #[SerializedName('veličina')]
    public string|array|null $size = null;

    /**
     * @var list<string>|string|null
     */
    #[SerializedName('boja')]
    public string|array|null $color = null;

    /**
     * @var list<string>|string|null
     */
    #[SerializedName('cena')]
    public string|array|null $price = null;
}
