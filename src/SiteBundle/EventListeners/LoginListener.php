<?php

namespace SiteBundle\EventListeners;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class LoginListener
{
    protected TokenStorageInterface $token;

    public function __construct(TokenStorageInterface $storage)
    {
        $this->token = $storage;
    }

    public function onAuthenticationFailure(LoginFailureEvent $event): void {}

    public function onAuthenticationSuccess(LoginSuccessEvent $event): void {}
}
