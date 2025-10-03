<?php

declare(strict_types=1);

namespace AdminBundle\Handler;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

final class AuthenticationEntryPointHandler implements AuthenticationEntryPointInterface
{
    private RouterInterface $router;

    public function __construct(
        RouterInterface $router
    ) {
        $this->router = $router;
    }

    /**
     * @param Request                      $request
     * @param AuthenticationException|null $authException
     *
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function start(Request $request, AuthenticationException $authException = null): RedirectResponse
    {
        if (null !== $authException) {
            if (false !== strpos($request->attributes->get('_route'), 'admin')) {
                return new RedirectResponse($this->router->generate('admin.login'));
            }

            return new RedirectResponse($this->router->generate('site.home_page', ['_locale' => $request->getSession()->get('_locale')]));
        }

    }
}