<?php

declare(strict_types=1);

namespace AdminBundle\Parser\Admin;

use SiteBundle\Entity\EntityInterface;
use SiteBundle\Entity\EntityStatusInterface;
use SiteBundle\Entity\User;
use SiteBundle\Handler\RoleHandler;
use SiteBundle\Repository\UserRepository;
use Symfony\Component\HttpFoundation\ParameterBag;

final class UserEditRequestParser
{
    private UserRepository $userRepository;

    private RoleHandler $roleHandler;

    public function __construct(
        UserRepository $userRepository,
        RoleHandler $roleHandler
    ) {
        $this->userRepository = $userRepository;
        $this->roleHandler = $roleHandler;
    }

    public function parse(ParameterBag $bag, User $user = null): User
    {
        if (null === $user) {
            $user = new User();
            $user->setPassword($bag->get('password'));
            $user->setStatus(EntityStatusInterface::STATUS_PENDING);
        }

        $user->setFirstName($bag->get('first_name'));
        $user->setLastName($bag->get('last_name'));
        $user->setRoles([$bag->get('role')]);
        $user->setEmail($bag->get('email'));

        if (null !== $bag->get('password') && null !== $user->getId()) {
            $user->setPassword($bag->get('password'));
        }

        return $user;
    }
}
