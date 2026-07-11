<?php

declare(strict_types=1);

namespace AdminBundle\Dto\InfoPage;

/**
 * Payload for DELETE /api/info-pages/{id} (route
 * `admin.api.info_pages.remove`). Populated by the bespoke
 * `InfoPageRemoveConfirmValueResolver` (spec §132: `#[MapQueryString]`
 * on DELETE is forbidden). The resolver reads:
 *   - `$id` from the route path attribute
 *   - `$confirm` from the query string `?confirm=…`
 *
 * The `$confirm=obriši` typed-token surrogate is NOT CSRF — it is a
 * human-confirmation guard preserved byte-for-byte by the resolver.
 * The controller enforces the guard as a first-line manual check
 * (throws `AccessDeniedException` on mismatch), so no Asserts are
 * declared here.
 */
final class InfoPageRemoveDto
{
    public int $id = 0;

    public string $confirm = '';
}
