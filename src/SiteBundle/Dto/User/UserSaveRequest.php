<?php

declare(strict_types=1);

namespace SiteBundle\Dto\User;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Consolidated wire DTO for BOTH `POST /api/add-new-user`
 * (`site_registration_post`) and `PUT /user/{id}` (`site_user_update`).
 * Consumed by SiteBundle\Parser\UserSaveRequestParser.
 *
 * Insert-vs-update Assert divergence handled via validation groups
 * (`groups: ['register']` on the 5 `NotBlank` Asserts) — controller
 * passes `validationGroups: ['Default', 'register']` on the addNewUser
 * endpoint and `['Default', 'update']` on the updateUserAction endpoint.
 * `facebookId` / `facebookImage` are register-only wire fields (always
 * optional; the update parser method ignores them).
 *
 * No CSRF on either endpoint (verified in §A.5).
 *
 * See SiteBundle\Parser\UserSaveRequestParser for the typed
 * `toArray*` method preserving byte-for-byte compatibility with the
 * downstream handler (UserHandler::insertUser(array)).
 */
final class UserSaveRequest
{
    #[Assert\NotBlank(groups: ['register'])]
    #[Assert\Email]
    public ?string $email = null;

    #[Assert\NotBlank(groups: ['register'])]
    #[Assert\Length(min: 6)]
    public ?string $password = null;

    #[Assert\NotBlank(groups: ['register'])]
    public ?string $firstname = null;

    #[Assert\NotBlank(groups: ['register'])]
    public ?string $lastname = null;

    #[Assert\NotBlank(groups: ['register'])]
    public ?string $repassword = null;

    public ?string $facebookId = null;

    public ?string $facebookImage = null;
}
