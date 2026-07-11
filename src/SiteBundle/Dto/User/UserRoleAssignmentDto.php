<?php

declare(strict_types=1);

namespace SiteBundle\Dto\User;

use SiteBundle\Entity\User;

/**
 * Internal-only DTO for SiteBundle\Parser\UserToRoleParser::parse.
 *
 * Carries the User entity to which a role should be attached and the
 * role code string. Not a wire DTO — the only caller is
 * AdminBundle\Parser\ProductEditRequestParser::setOwnerFromContact,
 * which constructs it in-code (never from an HTTP payload). No Assert
 * constraints and no MapRequestPayload usage.
 */
final class UserRoleAssignmentDto
{
    public function __construct(
        public readonly User $user,
        public readonly string $roleCode,
    ) {
    }
}
