<?php

declare(strict_types=1);

namespace SiteBundle\Dto\User;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Consumed by SiteBundle\Handler\UserHandler::doResetPassword(User, array).
 * Array keys produced by the matching parser: 'password', 'rePassword'.
 */
final class SetNewPasswordRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 6)]
    public string $password = '';

    #[Assert\NotBlank]
    public string $rePassword = '';
}
