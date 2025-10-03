<?php

declare(strict_types=1);

namespace SiteBundle\Parser;

use SiteBundle\Entity\Ads;
use SiteBundle\Entity\Contact;
use SiteBundle\Entity\Role;
use SiteBundle\Entity\User;
use SiteBundle\Entity\Usertorole;
use SiteBundle\Repository\CityRepository;
use SiteBundle\Repository\RoleRepository;

final class AdsContactUserParser
{
    private RoleRepository $roleRepository;

    private CityRepository $cityRepository;

    public function __construct(
        RoleRepository $roleRepository,
        CityRepository $cityRepository
    ) {
        $this->roleRepository = $roleRepository;
        $this->cityRepository = $cityRepository;
    }

    public function parse(Ads $ads, array $data): void
    {
        $city = $this->cityRepository->findOneBy(['alias' => $data['city']]);

        $user = $this->getContact($ads);

        $user->setFirstname($data['first_name']);
        $user->setLastname($data['surname']);
        $user->setContactEmail(trim($data['email']));
        $user->setTelephone($data['telephone']);
        $user->setViber($data['viber'] ?? null);
        $user->setMobilephone($data['mobile_phone'] ?? null);
        $user->setAddress($data['address']);
        $user->setCity($city);

        $ads->setContact($user);
    }

    private function create(): Contact
    {
        return new Contact();
    }

    private function getContact(Ads $ads): Contact
    {
        $user = $this->create();
        $this->setUserToRole($user, Role::ROLE_CONTACT);

        if (null !== $ads->getId()) {
            $user = $ads->getContact();
        }

        return $user;
    }

    private function setUserToRole(User $user, string $role): void
    {
        $roleObj = $this->roleRepository->findOneBy(['code' => $role]);

        $userToRole = new Usertorole();
        $userToRole->setUserId($user);
        $userToRole->setRoleId($roleObj);


        $user->addRole($userToRole);
    }
}
