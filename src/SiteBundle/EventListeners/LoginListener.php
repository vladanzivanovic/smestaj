<?php

namespace SiteBundle\EventListeners;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginListener
{
    protected $token;

    public function __construct(TokenStorageInterface $storage)
    {
        $this->token = $storage;
    }

    /**
     * onAuthenticationFailure
     *
     * @param 	AuthenticationFailureEvent $event
     */
    public function onAuthenticationFailure( AuthenticationFailureEvent $event ){}

    /**
     * onAuthenticationSuccess
     *
     * @param 	InteractiveLoginEvent $event
     */
    public function onAuthenticationSuccess( InteractiveLoginEvent $event ){}
}