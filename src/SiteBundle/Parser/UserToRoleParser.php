<?php

declare(strict_types=1);

namespace SiteBundle\Parser;

use AdminBundle\Parser\RequestParserInterface;
use http\Exception\InvalidArgumentException;
use SiteBundle\Entity\EntityInterface;
use SiteBundle\Entity\Usertorole;
use SiteBundle\Repository\RoleRepository;
use Symfony\Component\HttpFoundation\ParameterBag;

final class UserToRoleParser implements RequestParserInterface
{
    private RoleRepository $roleRepository;

    public function __construct(RoleRepository $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    public function parse(ParameterBag $bag, EntityInterface $entity = null): EntityInterface
    {
        if (null === $user = $bag->get('user')) {
            throw new InvalidArgumentException('There is no user on which to set role.');
        }

        $roleObj = $this->roleRepository->findOneBy(['code' => $bag->get('user_role')]);

        $userToRole = $this->create();
        $userToRole->setUserId($user);
        $userToRole->setRoleId($roleObj);

        return $userToRole;
    }

    public function create(): Usertorole
    {
        return new Usertorole();
    }
}