<?php

declare(strict_types=1);

namespace SiteBundle\Dto\Embedded;

use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Nested payload under the `contact[*]` bracket-notation form key.
 *
 * Field names mirror `SiteBundle\Parser\AdsContactUserParser::parse()` reads
 * so the on-wire keys (`first_name`, `mobile_phone`, ...) are preserved
 * byte-for-byte via `#[SerializedName]`.
 */
final class AdsContactDto
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    #[SerializedName('first_name')]
    public string $firstName = '';

    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    public string $surname = '';

    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 255)]
    public string $email = '';

    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    public string $telephone = '';

    #[Assert\Length(max: 100)]
    public ?string $viber = null;

    #[Assert\Length(max: 100)]
    #[SerializedName('mobile_phone')]
    public ?string $mobilePhone = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $address = '';

    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    public string $city = '';

    /**
     * Optional — only the AdminBundle product form emits `contact[password]`.
     * When present on a new-Ads create with no explicit owner, the parser
     * uses it to spin up a fresh owner User (see
     * AdminBundle\Parser\ProductEditRequestParser::setOwnerFromContact).
     * The site-side ads form never emits this field.
     */
    #[Assert\Length(max: 255)]
    public string $password = '';
}
