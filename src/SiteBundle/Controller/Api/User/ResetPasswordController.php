<?php

namespace SiteBundle\Controller\Api\User;


use SiteBundle\Controller\SiteController;
use SiteBundle\Dto\User\SetNewPasswordRequest;
use SiteBundle\Entity\User;
use SiteBundle\Exceptions\ApplicationException;
use SiteBundle\Handler\UserHandler;
use SiteBundle\Parser\SetNewPasswordRequestParser;
use SiteBundle\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ResetPasswordController extends SiteController
{
    private $userHandler;
    private $translator;
    private UserRepository $userRepository;
    private SetNewPasswordRequestParser $setNewPasswordRequestParser;

    /**
     * ResetPasswordController constructor.
     *
     * @param UserHandler         $userHandler
     * @param TranslatorInterface $translator
     */
    public function __construct(
        UserHandler $userHandler,
        TranslatorInterface $translator,
        UserRepository $userRepository,
        SetNewPasswordRequestParser $setNewPasswordRequestParser
    ) {
        $this->userHandler = $userHandler;
        $this->translator = $translator;
        $this->userRepository = $userRepository;
        $this->setNewPasswordRequestParser = $setNewPasswordRequestParser;
    }

    #[Route('/api/reset-password/{email}', name: 'site_user_reset_password', options: ['expose' => true], methods: ['PUT'])]
    public function resetPasswordRequest(string $email): JsonResponse
    {
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->json(
                $this->translator->trans('fields.email', [], 'validators'),
                Response::HTTP_BAD_REQUEST
            );
        }

        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (null === $user) {
            return $this->json(
                ['message' => $this->translator->trans('user_not_exists', [], 'validators')],
                Response::HTTP_NOT_FOUND
            );
        }

        $this->userHandler->setResetPassword($user);

        return $this->json([]);
    }

    #[Route('/api/set-new-password/{token}', name: 'site_user_set_password', methods: ['POST'])]
    public function setNewPassword(
        #[MapEntity(mapping: ['token' => 'token'])] User $user,
        #[MapRequestPayload(acceptFormat: 'form')] SetNewPasswordRequest $dto
    ): JsonResponse {
        $data = $this->setNewPasswordRequestParser->toArray($dto);

        $response = $this->userHandler->doResetPassword($user, $data);

        if (is_array($response)) {
            return $this->json($response, Response::HTTP_BAD_REQUEST);
        }

        $this->addFlash('message', $this->translator->trans('set_new_password_success'));

        return $this->json([]);
    }
}
