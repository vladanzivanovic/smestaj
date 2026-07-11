<?php

declare(strict_types=1);

namespace AdminBundle\Dto\InfoPage;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Form payload for POST /api/info-pages/{id}/publish (route
 * `admin.api.info_pages.toggle_publish`). Populated by
 * `#[MapRequestPayload(acceptFormat: 'form')]` from the FormData built
 * by `InfoPageEditHandler.js` (line 108-109): `published='1'` or `'0'`.
 *
 * Wire is form-encoded / multipart, NOT JSON — the plan literal
 * `#[MapRequestPayload]` is corrected to `acceptFormat: 'form'` here to
 * match the JS caller (executor deviation, byte-for-byte preservation).
 * Symfony's form denormalizer coerces the string `'0'`/`'1'` into the
 * typed `bool $published` property natively; a missing field falls
 * through to the `= false` default and is caught by `Assert\NotNull`.
 *
 * No CSRF on this endpoint today (§A.5) — none added.
 */
final class InfoPageTogglePublishRequest
{
    #[Assert\NotNull]
    public bool $published = false;
}
