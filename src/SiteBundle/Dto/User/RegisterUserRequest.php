<?php

declare(strict_types=1);

namespace SiteBundle\Dto\User;

/**
 * Consumed by SiteBundle\Handler\UserHandler::insertUser(array).
 *
 * Array keys produced by the matching parser (mirrors the legacy registration form
 * payload at src/SiteBundle/Resources/views/Moduls/headerMiddle.html.twig):
 *   - email        (required; uniqueness check + entity hydrator)
 *   - password     (required; hashed by handler before persist)
 *   - firstname    (entity hydrator → User::setFirstname)
 *   - lastname     (entity hydrator → User::setLastname)
 *   - repassword   (entity hydrator → User::setRepassword)
 *   - facebookId   (optional; triggers UserHandler::setSocialData branch)
 *   - facebookImage(optional; only used when facebookId is set)
 *
 * 'status' and 'role' are overwritten inside the handler (STATUS_PENDING /
 * ROLE_ADVANCED_USER) and MUST NOT appear in the input array.
 */
final class RegisterUserRequest
{
    /*
     * Field-level validation lives on the User entity's Registration group;
     * this DTO is a typed transport only. Empty-body short-circuit handled
     * by RegisterUserRequestResolver.
     */
    public string $email = '';

    public string $password = '';

    public string $firstname = '';

    public string $lastname = '';

    public string $repassword = '';

    public ?string $facebookId = null;

    public ?string $facebookImage = null;
}
