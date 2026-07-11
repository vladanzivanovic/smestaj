<?php

declare(strict_types=1);

namespace SiteBundle\Parser;

use AdminBundle\Parser\RequestParserInterface;
use InvalidArgumentException;
use SiteBundle\Dto\User\UserRoleAssignmentDto;
use SiteBundle\Entity\EntityInterface;
use SiteBundle\Entity\Usertorole;
use SiteBundle\Repository\RoleRepository;

final class UserToRoleParser implements RequestParserInterface
{
    private RoleRepository $roleRepository;

    public function __construct(RoleRepository $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    /**
     * @param UserRoleAssignmentDto $dto    concrete DTO — must be a UserRoleAssignmentDto instance
     * @param EntityInterface|null  $entity unused (LSP-satisfies the interface); a fresh Usertorole is always returned
     */
    public function parse(object $dto, ?EntityInterface $entity = null): Usertorole
    {
        if (false === $dto instanceof UserRoleAssignmentDto) {
            throw new InvalidArgumentException(sprintf(
                '%s::parse expects a %s, got %s',
                self::class,
                UserRoleAssignmentDto::class,
                $dto::class,
            ));
        }

        $roleObj = $this->roleRepository->findOneBy(['code' => $dto->roleCode]);

        $userToRole = $this->create();
        $userToRole->setUserId($dto->user);
        $userToRole->setRoleId($roleObj);

        return $userToRole;
    }

    public function create(): Usertorole
    {
        return new Usertorole();
    }
}
