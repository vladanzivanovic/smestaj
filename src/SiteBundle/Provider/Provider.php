<?php
/**
 * Created by PhpStorm.
 * User: vlada
 * Date: 7/16/2017
 * Time: 2:59 PM
 */

namespace SiteBundle\Provider;


use Doctrine\ORM\EntityManager;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUserProvider;
use SiteBundle\Constants\MessageConstants;
use SiteBundle\Entity\User;
use SiteBundle\Entity\UserToSocialNetwork;
use SiteBundle\Handler\RoleHandler;
use SiteBundle\Handler\UserHandler;
use SiteBundle\Handler\UserToSocialHandler;
use SiteBundle\Repository\UserRepository;
use SiteBundle\Repository\UserToSocialNetworkRepository;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class Provider implements UserProviderInterface, OAuthAwareUserProviderInterface
{
    private $userResponse;
    private $request;
    private $session;
    private $tokenStorage;
    private $userToSocialNetworkRepository;
    private $userRepository;
    private $userToSocialHandler;
    private $userHandler;
    private $roleHandler;

    /**
     * Provider constructor.
     *
     * @param RequestStack                  $requestStack
     * @param SessionInterface              $session
     * @param TokenStorageInterface         $tokenStorage
     * @param UserToSocialNetworkRepository $userToSocialNetworkRepository
     * @param UserRepository                $userRepository
     * @param UserToSocialHandler           $userToSocialHandler
     * @param UserHandler                   $userHandler
     * @param RoleHandler                   $roleHandler
     */
    public function __construct(
        RequestStack $requestStack,
        SessionInterface $session,
        TokenStorageInterface $tokenStorage,
        UserToSocialNetworkRepository $userToSocialNetworkRepository,
        UserRepository $userRepository,
        UserToSocialHandler $userToSocialHandler,
        UserHandler $userHandler,
        RoleHandler $roleHandler
    )
    {
        $this->request = $requestStack;
        $this->session = $session;
        $this->tokenStorage = $tokenStorage;
        $this->userToSocialNetworkRepository = $userToSocialNetworkRepository;
        $this->userRepository = $userRepository;
        $this->userToSocialHandler = $userToSocialHandler;
        $this->userHandler = $userHandler;
        $this->roleHandler = $roleHandler;
    }

    public function loadUserByOAuthUserResponse(UserResponseInterface $userResponse)
    {
        $this->userResponse = $userResponse;

        /** @var User $user */
        $user = $this->findUser();

        if(null === $user) {
            $this->session->getFlashBag()->set('loginFacebookFault', MessageConstants::FACEBOOK_LOGIN_NOT_FOUND);
            return null;
        }
        $this->logInUser($user);
        $this->userToSocialHandler->checkAndSetUserSocialData($userResponse, $user);

        return $this->loadUserByUsername($user->getId());
    }

    public function loadUserByUsername($id)
    {
        /** @var User $user */
        return $this->userRepository->find($id);
    }

    /**
     * Log user in application on social response
     * @param User $user
     */
    public function logInUser(User $user)
    {
        $token = new UsernamePasswordToken($user, null, 'common', $user->getRoles());
        $request = $this->request->getMasterRequest();

        if (!$request->hasPreviousSession()) {
            $request->setSession($this->session);
            $request->getSession()->start();
            $request->cookies->set($request->getSession()->getName(), $request->getSession()->getId());
        }

        $this->tokenStorage->setToken($token);
        $this->session->set('_security_common', serialize($token));

        $event = new InteractiveLoginEvent($this->request->getMasterRequest(), $token);
        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->dispatch("security.interactive_login", $event);
    }

    /***
     * @param string $class
     * @return bool
     */
    public function supportsClass($class)
    {
        return $class === 'SiteBundle\\Entity\\User';
    }

    private function findUser()
    {
        $responseArray = $this->userResponse->getResponse();
        $id = $responseArray['id'];
        /** @var UserToSocialNetwork $userSocial */
        $userSocial = $this->userToSocialNetworkRepository->findOneBy(['socialid' => $id]);
        $user = $userSocial instanceof UserToSocialNetwork ? $userSocial->getUserid() : null;

        if (null === $user && !empty($this->userResponse->getEmail())) {
            $user = $this->userRepository->findOneBy(['email' => $this->userResponse->getEmail()]);
        }

        return $user;
    }

    /**
     * Refreshes the user for the account interface.
     *
     * It is up to the implementation to decide if the user data should be
     * totally reloaded (e.g. from the database), or if the UserInterface
     * object can just be merged into some internal array of users / identity
     * map.
     *
     * @param UserInterface $user
     *
     * @return UserInterface
     *
     * @throws UnsupportedUserException if the account is not supported
     */
    public function refreshUser(UserInterface $user)
    {
        // TODO: Implement refreshUser() method.
    }
}