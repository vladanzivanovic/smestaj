<?php

declare(strict_types=1);

namespace AdminBundle\Dto\Product;

use SiteBundle\Dto\Ads\AdsSaveRequest;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Consolidated wire DTO for BOTH `POST /api/add-product`
 * (`admin.add_product_api`) and `PUT /api/edit-product/{id}`
 * (`admin.edit_product_api`). Consumed by
 * AdminBundle\Parser\ProductEditRequestParser::parse.
 *
 * Extends AdsSaveRequest — inherits every wire property the site-side
 * product form carries (26 fields including CSRF token + contact
 * embedded) plus the two admin-only fields (`owner`, `shortDescription`)
 * below.
 *
 * - `owner` (int) — form select `<select name="owner">`; admin picks
 *   the User who owns the created Ads. `0` means "no owner" (parser
 *   then falls back to creating one from contact + password).
 * - `shortDescription` (string) — form field `short_description_rs`;
 *   only the Serbian variant is persisted by
 *   ProductEditRequestParser::parse.
 */
final class ProductSaveRequest extends AdsSaveRequest
{
    #[Assert\PositiveOrZero]
    public int $owner = 0;

    #[SerializedName('short_description_rs')]
    public string $shortDescription = '';
}
