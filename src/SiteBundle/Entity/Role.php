<?php

namespace SiteBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Role
 *
 * @ORM\Table(name="role")
 * @ORM\Entity(repositoryClass="SiteBundle\Repository\RoleRepository")
 */
class Role
{
    const ROLE_ADMIN = 'ROLE_ADMIN';
    const ROLE_USER = 'ROLE_USER';
    const ROLE_ADVANCED_USER = 'ROLE_ADVANCED_USER';
    const ROLE_CONTACT = 'ROLE_CONTACT';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="Name", type="string", length=255)
     */
    private ?string $name = null;

    /**
     * @ORM\Column(name="Code", type="string", length=255)
     */
    private ?string $code = null;

    /**
     * @ORM\OneToMany(targetEntity="SiteBundle\Entity\Usertorole", mappedBy="role", cascade={"persist", "remove"},
     *                                                             orphanRemoval=true)
     */
    private Collection $users;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function hasUser(Usertorole $userToRole): bool
    {
        return $this->users->exists(function ($item) use ($userToRole) {
            /** @var Usertorole $item */
            return $item === $userToRole;
        });
    }

    public function setUsers(array $users): void
    {
        $this->users = new ArrayCollection($users);
    }

    public function adduser(Usertorole $userToRole): void
    {
        if (false === $this->hasUser($userToRole)) {
            $this->users->add($userToRole);

            $userToRole->setRoleId($this);
        }
    }

    /**
     * Remove role
     *
     * @param \SiteBundle\Entity\Usertorole $role
     */
    public function removeUser(Usertorole $userToRole)
    {
        if ($this->hasUser($userToRole)) {
            $this->users->removeElement($userToRole);

            $userToRole->setRoleId(null);
        }
    }
}
