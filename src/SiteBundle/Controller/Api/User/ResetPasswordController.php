<?php

namespace SiteBundle\Controller\Api\User;


use SiteBundle\Controller\SiteController;
use SiteBundle\Entity\User;
use SiteBundle\Exceptions\ApplicationException;
use SiteBundle\Handler\UserHandler;
use SiteBundle\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ResetPasswordController extends SiteController
{
    private $userHandler;
    private $translator;
    private UserRepository $userRepository;

    /**
     * ResetPasswordController constructor.
     *
     * @param UserHandler         $userHandler
     * @param TranslatorInterface $translator
     */
    public function __construct(
        UserHandler $userHandler,
        TranslatorInterface $translator,
        UserRepository $userRepository
    ) {
        $this->userHandler = $userHandler;
        $this->translator = $translator;
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/api/reset-password/{email}", name="site_user_reset_password", methods={"PUT"}, options={"expose": true})
     *
     * @param string $email
     *
     * @return JsonResponse
     * @throws ApplicationException
     */
    public function resetPasswordRequest(string $email): JsonResponse
    {
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->json(
                $this->translator->trans('fields.email', [], 'validators'),
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (null === $user) {
            return $this->json(
                ['message' => $this->translator->trans('user_not_exists', [], 'validators')],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        $this->userHandler->setResetPassword($user);

        return $this->json([]);
    }

    /**
     * @Route("/api/set-new-password/{token}", name="site_user_set_password", methods={"POST"})
     * @param User    $user
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function setNewPassword(User $user, Request $request)
    {
        $data = $this->requestToArray($request);

        $response = $this->userHandler->doResetPassword($user, $data);

        if (is_array($response)) {
            return $this->json($response, JsonResponse::HTTP_BAD_REQUEST);
        }

        $this->addFlash('message', $this->translator->trans('set_new_password_success'));

        return $this->json([]);
    }
}