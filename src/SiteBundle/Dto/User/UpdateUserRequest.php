<?php

declare(strict_types=1);

namespace SiteBundle\Dto\User;

/**
 * Intended for SiteBundle\Services\UserService::setUpUser(array, $id).
 *
 * NOTE (pre-existing baseline): UserService::setUpUser does not currently exist in
 * SiteBundle\Services\UserService, and the service id `site.user_service` is
 * commented out in src/SiteBundle/Resources/config/services.yml. The legacy
 * UserEditController::updateUserAction therefore returns HTTP 500 today. This
 * refactor restores the DTO + Parser + DI surface so a future patch that
 * (re-)introduces setUpUser will be wired correctly. Matches the pattern from
 * IMPLEMENTATION_PLAN.md Risks #5 / #6 / #14 (out-of-scope pre-existing failure).
 *
 * All properties are nullable so PUT requests can carry partial bodies
 * (PATCH-semantics), which the future setUpUser implementation is expected to
 * tolerate by skipping unset fields during entity hydration. Inferred key set
 * mirrors UserHandler::insertUser's entity-hydration keys (firstname / lastname
 * casing matches User entity setters: setFirstname, setLastname, setRepassword).
 *
 *   - email        (optional; entity hydrator)
 *   - password     (optional; will be hashed if setUpUser mirrors insertUser)
 *   - firstname    (optional; entity hydrator → User::setFirstname)
 *   - lastname     (optional; entity hydrator → User::setLastname)
 *   - repassword   (optional; entity hydrator → User::setRepassword)
 */
final class UpdateUserRequest
{
    public ?string $email = null;

    public ?string $password = null;

    public ?string $firstname = null;

    public ?string $lastname = null;

    public ?string $repassword = null;
}
