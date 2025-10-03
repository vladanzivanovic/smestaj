<?php
declare(strict_types=1);

namespace SiteBundle\Handler;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use SiteBundle\Entity\User;
use SiteBundle\Entity\Usertorole;
use SiteBundle\Repository\RoleRepository;
use SiteBundle\Services\ServiceContainer;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class RoleHandler extends ServiceContainer
{
    private RoleRepository $roleRepository;

    public function __construct(
        ObjectManager $objectManager,
        TokenStorageInterface $tokenStorage,
        RoleRepository $roleRepository
    ) {
        parent::__construct($objectManager, $tokenStorage);

        $this->roleRepository = $roleRepository;
    }

    public function setUserToRole(User $user, $role): User
    {
        $roleObj = $this->roleRepository->findOneBy(['code' => $role]);

        $userToRole = new Usertorole();
        $userToRole->setUserId($user);
        $userToRole->setRoleId($roleObj);


        $user->addRole($userToRole);

        return $user;
    }
}
