<?php

declare(strict_types=1);

namespace AdminBundle\Parser\Admin;

use AdminBundle\Dto\User\UserSaveRequest;
use SiteBundle\Entity\EntityStatusInterface;
use SiteBundle\Entity\User;
use SiteBundle\Handler\RoleHandler;
use SiteBundle\Repository\UserRepository;

final class UserEditRequestParser
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly RoleHandler $roleHandler,
    ) {
    }

    public function parse(UserSaveRequest $dto, ?User $user = null): User
    {
        if (null === $user) {
            $user = new User();
            $user->setPassword((string) $dto->password);
            $user->setStatus(EntityStatusInterface::STATUS_PENDING);
        }

        if (null !== $dto->firstName) {
            $user->setFirstName($dto->firstName);
        }

        if (null !== $dto->lastName) {
            $user->setLastName($dto->lastName);
        }

        if (null !== $dto->role) {
            $user->setRoles([$dto->role]);
        }

        if (null !== $dto->email && '' !== $dto->email) {
            $user->setEmail($dto->email);
        }

        if (null !== $dto->password && '' !== $dto->password && null !== $user->getId()) {
            $user->setPassword($dto->password);
        }

        return $user;
    }
}
