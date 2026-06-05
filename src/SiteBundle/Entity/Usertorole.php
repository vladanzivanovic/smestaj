<?php

namespace SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'usertorole')]
#[ORM\Entity(repositoryClass: \SiteBundle\Repository\UserToRoleRepository::class)]
class Usertorole implements EntityInterface
{
    use ResourceTrait;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'roles')]
    #[ORM\JoinColumn(name: 'UserId', referencedColumnName: 'Id')]
    private User $userId;

    #[ORM\ManyToOne(targetEntity: Role::class, inversedBy: 'users')]
    #[ORM\JoinColumn(name: 'RoleId', referencedColumnName: 'id')]
    private Role $role;

    public function setUserId(User $user): self
    {
        $this->userId = $user;

        return $this;
    }

    public function getUserId(): User
    {
        return $this->userId;
    }

    public function setRoleId(Role $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getRoleId(): Role
    {
        return $this->role;
    }
}
