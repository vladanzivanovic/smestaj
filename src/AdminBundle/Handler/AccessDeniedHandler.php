<?php

declare(strict_types=1);

namespace AdminBundle\Handler;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;

final class AccessDeniedHandler implements AccessDeniedHandlerInterface
{
    private RouterInterface $router;

    public function __construct(
        RouterInterface $router
    ) {
        $this->router = $router;
    }

    public function handle(Request $request, AccessDeniedException $accessDeniedException)
    {
        if (false !== strpos('admin', $request->attributes->get('_route'))) {
            return new RedirectResponse($this->router->generate('admin.login'));
        }

        return new RedirectResponse($this->router->generate('site.home_page', ['_locale' => $request->getSession()->get('_locale')]));
    }
}