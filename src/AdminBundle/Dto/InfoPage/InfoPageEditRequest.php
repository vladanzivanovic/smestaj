<?php

declare(strict_types=1);

namespace AdminBundle\Dto\InfoPage;

use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Multipart form payload for POST /api/info-pages/{id} (route
 * `admin.api.info_pages.update`). Populated by
 * `#[MapRequestPayload(acceptFormat: 'form')]` from the flat form emitted
 * by `src/AdminBundle/Resources/views/Pages/infoPageEdit.html.twig` and
 * submitted by `InfoPageEditHandler.js::submitForm`.
 *
 * The form wire is FLAT — `address_street`, `google_maps_lat`,
 * `tagline_rs`, `check_in_time`, etc. are top-level form fields, not
 * bracket-nested. This DTO therefore holds flat typed properties with
 * `#[SerializedName]` aliases that preserve every legacy wire key. The
 * only bracket-nested wire cluster is `tags[<typeLabel>][<tagId>]=<value>`
 * — modelled as a raw `array $tags` that PHP's form parser naturally
 * builds as a 2D map (flat form wire ⇒ flat DTO properties; no embedded
 * DTO extraction — the SKILL's `§10` genuinely-nested rule requires
 * bracket-notation sub-trees or JSON arrays of objects, not flat clusters
 * on a single parent).
 *
 * DTO-level Asserts run inside MapRequestPayload; failure ⇒
 * PayloadMappingFailureListener maps `admin.api.info_pages.update` to
 * formatOkFalseViolations() returning
 * `{ok: false, violations: [{field, message}, ...]}` at 422. Cross-field
 * rules (address+city → coordinates required, URL/phone shape,
 * HTML sanitization) stay in the parser to preserve the legacy
 * fault-tolerant coercion behavior (invalid URLs/phones become null
 * silently, do not reject the whole request).
 *
 * CSRF token (intention `info_page_edit`) travels on `csrfToken` — wire
 * key `_csrf_token`, matches `csrf_token('info_page_edit')` emitted by
 * infoPageEdit.html.twig line 50. Controller validates first-line via
 * `CsrfTokenManagerInterface::isTokenValid` and throws AccessDenied on
 * mismatch (activates CSRF on this endpoint — previously the check was
 * absent).
 */
final class InfoPageEditRequest
{
    #[SerializedName('_csrf_token')]
    #[Assert\NotBlank]
    public string $csrfToken = '';

    #[Assert\Length(max: 255)]
    public ?string $slug = null;

    #[Assert\Length(max: 200)]
    public ?string $propertyName = null;

    #[SerializedName('host_first_name')]
    #[Assert\Length(max: 100)]
    public ?string $hostFirstName = null;

    #[SerializedName('host_last_name')]
    #[Assert\Length(max: 100)]
    public ?string $hostLastName = null;

    #[SerializedName('host_mobile')]
    #[Assert\Length(max: 100)]
    public ?string $hostMobile = null;

    #[SerializedName('instagram_url')]
    public ?string $instagramUrl = null;

    #[SerializedName('facebook_url')]
    public ?string $facebookUrl = null;

    #[SerializedName('whatsapp_phone')]
    public ?string $whatsappPhone = null;

    #[SerializedName('viber_phone')]
    public ?string $viberPhone = null;

    #[SerializedName('booking_url')]
    public ?string $bookingUrl = null;

    #[SerializedName('airbnb_url')]
    public ?string $airbnbUrl = null;

    #[SerializedName('google_review_input')]
    public ?string $googleReviewInput = null;

    #[SerializedName('address_street')]
    #[Assert\Length(max: 255)]
    public ?string $addressStreet = null;

    #[SerializedName('address_postal_code')]
    #[Assert\Length(max: 20)]
    public ?string $addressPostalCode = null;

    #[SerializedName('address_city')]
    #[Assert\Length(max: 100)]
    public ?string $addressCity = null;

    #[SerializedName('google_maps_lat')]
    public ?string $googleMapsLat = null;

    #[SerializedName('google_maps_lng')]
    public ?string $googleMapsLng = null;

    #[SerializedName('wifi_username')]
    #[Assert\Length(max: 100)]
    public ?string $wifiUsername = null;

    #[SerializedName('wifi_password')]
    #[Assert\Length(max: 100)]
    public ?string $wifiPassword = null;

    #[SerializedName('house_rules')]
    public ?string $houseRules = null;

    #[SerializedName('tagline_rs')]
    #[Assert\Length(max: 200)]
    public ?string $taglineRs = null;

    #[SerializedName('tagline_en')]
    #[Assert\Length(max: 200)]
    public ?string $taglineEn = null;

    #[SerializedName('short_description_rs')]
    #[Assert\Length(max: 500)]
    public ?string $shortDescriptionRs = null;

    #[SerializedName('short_description_en')]
    #[Assert\Length(max: 500)]
    public ?string $shortDescriptionEn = null;

    #[SerializedName('welcome_message_rs')]
    #[Assert\Length(max: 2000)]
    public ?string $welcomeMessageRs = null;

    #[SerializedName('welcome_message_en')]
    #[Assert\Length(max: 2000)]
    public ?string $welcomeMessageEn = null;

    #[SerializedName('check_in_time')]
    public ?string $checkInTime = null;

    #[SerializedName('check_out_time')]
    public ?string $checkOutTime = null;

    /**
     * Bracket-nested wire form `tags[<typeLabel>][<tagId>]=<value>` naturally
     * populates as a 2D map: `<typeLabel> => [<tagId> => <value>]`. Range-type
     * tag values are meter strings; non-range are `'1'`. Non-numeric tag IDs
     * and non-string type labels are silently dropped by the parser.
     *
     * @var array<string, array<int|string, string>>
     */
    public array $tags = [];

    /**
     * JSON-encoded dropzone image state emitted by
     * `InfoPageEditHandler.js::#collectDropzoneFiles`. Decoded and validated
     * inside the parser (invalid JSON degrades silently to an empty list).
     */
    public ?string $uploadedImages = null;

    /**
     * Wire value is the string `'1'` (checkbox checked) or absent (unchecked).
     * Coerced by the parser's `parseBool` — preserves the legacy
     * `'true'/'1'/'on'` acceptance.
     */
    public ?string $published = null;
}
