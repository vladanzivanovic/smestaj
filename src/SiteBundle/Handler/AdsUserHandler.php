<?php

namespace SiteBundle\Handler;


use Doctrine\Common\Persistence\ObjectManager;
use SiteBundle\Entity\Ads;
use SiteBundle\Entity\Role;
use SiteBundle\Entity\User;
use SiteBundle\Entity\Usertorole;
use SiteBundle\Repository\CityRepository;
use SiteBundle\Repository\UserRepository;
use SiteBundle\Services\ServiceContainer;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AdsUserHandler extends ServiceContainer
{
    private $userRepository;
    private $roleHandler;
    private $cityRepository;

    /**
     * AdsUserHandler constructor.
     *
     * @param ObjectManager         $objectManager
     * @param TokenStorageInterface $tokenStorage
     * @param UserRepository        $userRepository
     * @param RoleHandler           $roleHandler
     * @param CityRepository        $cityRepository
     */
    public function __construct(
        ObjectManager $objectManager,
        TokenStorageInterface $tokenStorage,
        UserRepository $userRepository,
        RoleHandler $roleHandler,
        CityRepository $cityRepository
    ) {
        parent::__construct($objectManager, $tokenStorage);
        $this->userRepository = $userRepository;
        $this->roleHandler = $roleHandler;
        $this->cityRepository = $cityRepository;
    }

    public function setUser(Ads $ads, array $data)
    {
        $user = $this->userRepository->findOneBy([
            'contactemail' => $data['ContactEmail'],
            'telephone' => $data['Telephone']
        ]);

        if (!$user instanceof User) {
            $user = $this->arrayToEntity($data, User::class);
            $this->roleHandler->setUserToRole($user, Role::ROLE_CONTACT);
        }

        $city = $this->cityRepository->findOneBy(['name' => $data['CityName']]);

        $user->setFirstname($data['FirstName'])
            ->setLastname($data['LastName'])
            ->setContactEmail($data['ContactEmail'])
            ->setTelephone($data['Telephone'])
            ->setViber($data['Viber'] ?? null)
            ->setMobilephone($data['MobilePhone'] ?? null)
            ->setCityid($city)
            ->setWebsite($data['Website'] ?? null)
            ->setFacebook($data['Facebook'] ?? null);

        $ads->setContact($user);

        return $ads;
    }
}