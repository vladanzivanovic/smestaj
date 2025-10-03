<?php

namespace AdminBundle\Handler;

use SiteBundle\Entity\EntityStatusInterface;
use SiteBundle\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AuthenticationHandler implements AuthenticationSuccessHandlerInterface, AuthenticationFailureHandlerInterface
{
    private TokenStorageInterface $tokenStorage;
    private SessionInterface $session;
    private TranslatorInterface $translator;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        SessionInterface $session,
        TranslatorInterface $translator
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->session = $session;
        $this->translator = $translator;
    }

    /**
     * @param Request        $request
     * @param TokenInterface $token
     *
     * @return JsonResponse|RedirectResponse|Response
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        if ( $request->isXmlHttpRequest() ) {

            /** @var User $user */
            $user = $token->getUser();

            if(EntityStatusInterface::STATUS_PENDING === $user->getStatus()){
                $this->tokenStorage->setToken(null);
                $request->getSession()->invalidate();

                return new JsonResponse(null, JsonResponse::HTTP_BAD_REQUEST);
            }

            $this->session->getFlashBag()->add('message', $this->translator->trans('login.successful'));

            return new JsonResponse(null);
        }

        $this->tokenStorage->setToken(null);
        $request->getSession()->invalidate();

        throw new BadRequestHttpException('Login is allowed only through POST method.');
    }

    /**
     * @param Request                 $request
     * @param AuthenticationException $exception
     *
     * @return JsonResponse|RedirectResponse|Response
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        if ( $request->isXmlHttpRequest() ) {
            $message = $this->translator->trans($exception->getMessage());
            $array = array('message' => $message );

            return new JsonResponse($array, JsonResponse::HTTP_BAD_REQUEST);
        }

        throw new BadRequestHttpException('Login is allowed only through POST method.');
    }
}
