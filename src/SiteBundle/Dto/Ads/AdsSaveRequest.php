<?php

declare(strict_types=1);

namespace SiteBundle\Dto\Ads;

use JetBrains\PhpStorm\NoReturn;
use SiteBundle\Dto\Embedded\AdsContactDto;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Consolidated wire DTO for BOTH `POST /api/product` (`site_ads_save`) and
 * `PUT /product/{id}` (`site_ads_update`). Consumed by
 * SiteBundle\Parser\AdsSaveRequestParser::parse.
 *
 * Insert and Update carry byte-for-byte identical wire shapes (verified
 * pre-consolidation) — one Assert set runs on both endpoints, no
 * `validationGroups` needed on the controller side.
 *
 * See AdsContactDto for the sole embedded sub-DTO (bracket-notation
 * `contact[*]` wire sub-tree). All other clusters (coordinates, price
 * plan, social links) live flat on this parent because the wire is flat —
 * see `.opencode/skill/controller-pattern/SKILL.md` §10 for the
 * genuinely-nested rule.
 */
class AdsSaveRequest
{
    #[Assert\NotBlank]
    #[SerializedName('_csrf_token')]
    public string $csrfToken = '';

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[SerializedName('title_rs')]
    public string $title = '';

    #[Assert\NotBlank]
    #[SerializedName('description_rs')]
    public string $description = '';

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[SerializedName('_address')]
    public string $address = '';

    #[Assert\Positive]
    public int $category = 0;

    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    public string $city = '';

    #[Assert\Type('numeric')]
    #[Assert\Range(min: -90, max: 90)]
    public float $lat = 0.0;

    #[Assert\Type('numeric')]
    #[Assert\Range(min: -180, max: 180)]
    public float $lng = 0.0;

    #[Assert\PositiveOrZero]
    #[SerializedName('post_price_from')]
    public int $postPriceFrom = 0;

    #[Assert\PositiveOrZero]
    #[SerializedName('post_price_to')]
    public int $postPriceTo = 0;

    #[Assert\PositiveOrZero]
    #[SerializedName('pre_price_from')]
    public int $prePriceFrom = 0;

    #[Assert\PositiveOrZero]
    #[SerializedName('pre_price_to')]
    public int $prePriceTo = 0;

    #[Assert\PositiveOrZero]
    #[SerializedName('price_from')]
    public int $priceFrom = 0;

    #[Assert\PositiveOrZero]
    #[SerializedName('price_to')]
    public int $priceTo = 0;

    #[Assert\Length(max: 500)]
    public ?string $facebook = null;

    #[Assert\Length(max: 500)]
    public ?string $website = null;

    #[Assert\Length(max: 500)]
    public ?string $instagram = null;

    /**
     * JSON-encoded array of YouTube video URLs. Frontend serialises the
     * client-side collection with `JSON.stringify` before submitting;
     * the parser calls `json_decode(..., true)`.
     */
    public string $youtube = '[]';

    /**
     * JSON-encoded array of image metadata objects (id, alt, sort, tmp
     * upload token). Frontend serialises with `JSON.stringify`; the
     * parser calls `json_decode(..., true)` and dispatches to
     * AdsImageService::setImage.
     */
    public string $documents = '[]';

    /**
     * Nested `tags[<type>][<tag_id>] = <value>` bracket-notation payload.
     * Decoded by Symfony's form denormaliser into a two-level dict-of-dict
     * that AdsTagParser iterates verbatim.
     *
     * @var array<string, array<int, string>>
     */
    public array $tags = [];

    #[Assert\Positive]
    #[SerializedName('price_plan')]
    public int $pricePlan = 0;

    #[SerializedName('payment_date')]
    public ?string $paymentDate = null;

    #[Assert\Valid]
    public AdsContactDto $contact;

    public function __construct()
    {
        $this->contact = new AdsContactDto();
    }
}
