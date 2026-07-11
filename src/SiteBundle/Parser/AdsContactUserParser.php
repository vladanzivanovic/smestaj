<?php

declare(strict_types=1);

namespace SiteBundle\Parser;

use SiteBundle\Dto\Embedded\AdsContactDto;
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

    public function parse(Ads $ads, AdsContactDto $contact): void
    {
        $city = $this->cityRepository->findOneBy(['alias' => $contact->city]);

        $user = $this->getContact($ads);

        $user->setFirstname($contact->firstName);
        $user->setLastname($contact->surname);
        $user->setContactEmail(trim($contact->email));
        $user->setTelephone($contact->telephone);
        $user->setViber($contact->viber);
        $user->setMobilephone($contact->mobilePhone);
        $user->setAddress($contact->address);
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
