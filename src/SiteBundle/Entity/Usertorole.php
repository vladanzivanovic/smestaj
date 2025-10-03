<?php

namespace SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserToRole
 *
 * @ORM\Table(name="usertorole")
 * @ORM\Entity(repositoryClass="SiteBundle\Repository\UserToRoleRepository")
 */
class Usertorole implements EntityInterface
{
    use ResourceTrait;

    /**
     * @ORM\ManyToOne(targetEntity="SiteBundle\Entity\User", inversedBy="roles")
     * @ORM\JoinColumn(name="UserId", referencedColumnName="Id")
     */
    private User $userId;

    /**
     * @ORM\ManyToOne(targetEntity="SiteBundle\Entity\Role", inversedBy="users")
     * @ORM\JoinColumn(name="RoleId", referencedColumnName="id")
     */
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
