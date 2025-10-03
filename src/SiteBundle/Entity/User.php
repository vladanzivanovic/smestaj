<?php

namespace SiteBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="SiteBundle\Repository\UserRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap ({"user" = "User", "contact" = "Contact"})
 */
class User implements UserInterface, \Serializable, EntityInterface, EntityStatusInterface
{
    use ResourceTrait;
    use StatusTrait;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private ?string $username = null;

    /**
     * @ORM\Column(name="first_name", type="string", length=100, nullable=false)
     * @Assert\NotBlank(message="fields.required", groups={"Registration", "SetAd", "SetAdAdmin"})
     */
    private ?string $firstname = null;

    /**
     * @ORM\Column(name="last_name", type="string", length=100, nullable=false)
     * @Assert\NotBlank(message="fields.required", groups={"Registration", "SetAd", "SetAdAdmin"})
     */
    private ?string $lastname = null;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     * @Assert\NotBlank(message="fields.required", groups={"Registration", "ResetPassword"})
     * @Assert\EqualTo(message="fields.password_not_equal", propertyPath="repassword", groups={"Registration",
     *                                                      "ResetPassword"})
     */
    private ?string $password = null;

    private ?string $repassword = null;

    /**
     * @ORM\OneToMany(targetEntity="SiteBundle\Entity\Usertorole", mappedBy="userId", cascade={"persist", "remove"},
     *                                                             orphanRemoval=true)
     */
    private Collection $roles;

    /**
     * @Serializer\Exclude()
     * @ORM\OneToMany(targetEntity="SiteBundle\Entity\Ads", mappedBy="sysCreatedUserId", cascade={"persist"})
     */
    private Collection $sysCreatedAds;

    /**
     * @ORM\Column(type="string", length=60, unique=true, nullable=true)
     * @Assert\NotBlank(message="fields.required", groups={"Registration"})
     * @Assert\Email(message="fields.email", groups={"Registration"}, mode="loose")
     */
    private ?string $email = null;

    /**
     * @Serializer\Exclude()
     * @ORM\OneToMany(targetEntity="SiteBundle\Entity\UserToSocialNetwork", mappedBy="userid",
     *                                                                      cascade={"persist","remove"})
     */
    private Collection $socialId;

    /**
     * @ORM\Column(length=100, nullable=true)
     */
    private ?string $token = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?\DateTimeInterface $tokenValid = null;

    /**
     * @var bool
     */
    private bool $isResetPasswordRequest = false;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
        $this->sysCreatedAds = new ArrayCollection();
        $this->socialId = new ArrayCollection();
    }

    public function __clone()
    {
        $this->id = null;
    }

    public function getSalt()
    {
        // you *may* need a real salt depending on your encoder
        // see section on salt below
        return null;
    }

    public function setRoles(Usertorole $roles): void
    {
        $this->roles[] = $roles;
    }

    public function addRole(Usertorole $role): void
    {
        if (false === $this->hasRole($role)) {
            $this->roles[] = $role;

            $role->setUserId($this);
        }
    }

    public function getRoles(): array
    {
        $roles = [];

        /** @var Usertorole $role */
        foreach ($this->roles as $role) {
            $roles[] = $role->getRoleId()->getCode();
        }
        return $roles;
    }

    public function hasRole($role): bool
    {
        return $this->roles->exists(function ($item) use ($role) {
            /** @var Usertorole $item */
            return $item->getRoleId()->getCode() === $role;
        });
    }

    public function removeRole(Usertorole $role)
    {
        if (true === $this->hasRole($role)) {
            $this->roles->removeElement($role);
            $role->setUserId(null);
        }
    }

    public function removeAllRoles(): void
    {
        /** @var Usertorole $role */
        foreach ($this->roles as $role) {
            $this->removeRole($role);
        }
    }

    public function eraseCredentials()
    {
    }

    /** @see \Serializable::serialize() */
    public function serialize(): ?string
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->password,
            // see section on salt below
            // $this->salt,
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->password,
            // see section on salt below
            // $this->salt
            ) = unserialize($serialized);
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function setFirstname(string $firstname): void
    {
        $this->firstname = $firstname;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setLastname(string $lastname): void
    {
        $this->lastname = $lastname;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    /**
     * @param string $rePassword
     *
     * @return User
     */
    public function setRepassword(string $rePassword): User
    {
        $this->repassword = $rePassword;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getRepassword(): ?string
    {
        return $this->repassword;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getSocialId()
    {
        return $this->socialId;
    }

    public function addSocialId(UserToSocialNetwork $socialId): void
    {
        $this->socialId[] = $socialId;
    }

    public function removeSocialId(UserToSocialNetwork $socialId): void
    {
        $this->socialId->removeElement($socialId);
    }

    public function setToken(?string $token): void
    {
        $this->token = $token;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setTokenValid(?\DateTime $dateTime): void
    {
        $this->tokenValid = $dateTime;
    }

    public function getTokenValid(): ?\DateTimeInterface
    {
        return $this->tokenValid;
    }

    public function setIsResetPasswordRequest(bool $isResetPasswordRequest): void
    {
        $this->isResetPasswordRequest = $isResetPasswordRequest;
    }

    public function getIsResetPasswordRequest(): ?bool
    {
        return $this->isResetPasswordRequest;
    }

    public function addSysCreatedAd(Ads $sysCreatedAd): void
    {
        if (false === $this->hasSysCreatedAd($sysCreatedAd)) {
            $this->sysCreatedAds[] = $sysCreatedAd;

            $sysCreatedAd->setSysCreatedUserId($this);
        }
    }

    public function hasSysCreatedAd(Ads $sysCreatedAd): bool
    {
        return $this->sysCreatedAds->contains($sysCreatedAd);
    }

    public function removeSysCreatedAd(Ads $sysCreatedAd): void
    {
        if (true === $this->hasSysCreatedAd($sysCreatedAd)) {
            $this->sysCreatedAds->removeElement($sysCreatedAd);

            $sysCreatedAd->setSysCreatedUserId(null);
        }
    }

    public function getSysCreatedAds(): Collection
    {
        return $this->sysCreatedAds;
    }
}
