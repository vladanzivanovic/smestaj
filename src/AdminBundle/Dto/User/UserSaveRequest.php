<?php

declare(strict_types=1);

namespace AdminBundle\Dto\User;

use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Consolidated wire DTO for BOTH `POST /api/add-user`
 * (`admin.add_user_api`) and `PUT /api/update-user/{id}`
 * (`admin.edit_user_api`). Consumed by
 * AdminBundle\Parser\Admin\UserEditRequestParser::parse.
 *
 * Insert-vs-update Assert divergence handled via validation groups
 * (`groups: ['insert']` on the `NotBlank` for `email` + `password`) —
 * controller passes `validationGroups: ['Default', 'insert']` on the
 * add endpoint and `['Default', 'update']` on the update endpoint.
 *
 * CSRF intention `set_user`; wire key `_csrf_token`. The wire also
 * carries additional address-block fields (`phone`, `address`, `city`,
 * `zip_code`, `country`, `note`) which the current parser does NOT
 * consume — they arrive on the request body but are silently dropped
 * by `ObjectNormalizer` since this DTO has no matching properties.
 * Preserves pre-consolidation behavior.
 */
final class UserSaveRequest
{
    #[SerializedName('_csrf_token')]
    #[Assert\NotBlank]
    public string $csrfToken = '';

    #[SerializedName('first_name')]
    #[Assert\Length(max: 100)]
    public ?string $firstName = null;

    #[SerializedName('last_name')]
    #[Assert\Length(max: 100)]
    public ?string $lastName = null;

    #[Assert\NotBlank(groups: ['insert'])]
    #[Assert\Email]
    public ?string $email = null;

    #[Assert\NotBlank(groups: ['insert'])]
    #[Assert\Length(min: 6)]
    public ?string $password = null;

    #[Assert\Choice(choices: ['ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN'])]
    public ?string $role = null;
}
