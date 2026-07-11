<?php

declare(strict_types=1);

namespace AdminBundle\Dto\InfoPage;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Form payload for POST /api/info-pages (route
 * `admin.api.info_pages.create`). Populated by
 * `#[MapRequestPayload(acceptFormat: 'form')]`.
 *
 * Unlike `InfoPageEditRequest`, this endpoint is NOT a full-form
 * submission — it clones an existing `Ads` into a new `AdsInfoPage`.
 * The wire carries exactly two fields: `linkedAdsId` (the source Ads to
 * clone from) and `copyData` (whether to copy the source's textual
 * fields). The plan's suggestion to "reuse `InfoPageEditRequestParser`"
 * is architecturally incompatible with this endpoint's clone behavior;
 * the controller delegates to `InfoPageEditHandler::createFromAds`
 * instead (executor deviation, mirrors the flat-wire deviation on
 * `InfoPageEditRequest`).
 *
 * `copyData` is modelled as `?string` (not `bool`) to preserve the
 * legacy fault-tolerant coercion done today by
 * `InfoPageCreateController::parseBool` — inputs `'1'`, `'true'`,
 * `'on'`, `true`, `1` all become true; anything else becomes false. The
 * coercion moves into the controller as a private helper (unchanged
 * from the legacy shape).
 *
 * No CSRF on this endpoint today (§A.5) — none added (spec's "no silent
 * addition" rule).
 */
final class InfoPageCreateRequest
{
    #[Assert\Positive]
    public int $linkedAdsId = 0;

    public ?string $copyData = null;
}
