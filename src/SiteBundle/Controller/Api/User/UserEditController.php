<?php

declare(strict_types=1);

namespace SiteBundle\Controller\Api\User;


use Psr\Log\LoggerInterface;
use SiteBundle\Constants\MessageConstants;
use SiteBundle\Controller\SiteController;
use SiteBundle\Dto\User\UserSaveRequest;
use SiteBundle\Entity\User;
use SiteBundle\Handler\UserHandler;
use SiteBundle\Parser\UserSaveRequestParser;
use SiteBundle\Security\Voter\UserVoter;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final class UserEditController extends SiteController
{
    private UserHandler $userHandler;

    private LoggerInterface $logger;

    private UserSaveRequestParser $userSaveRequestParser;

    public function __construct(
        UserHandler $userHandler,
        LoggerInterface $logger,
        UserSaveRequestParser $userSaveRequestParser,
    ) {
        $this->userHandler = $userHandler;
        $this->logger = $logger;
        $this->userSaveRequestParser = $userSaveRequestParser;
    }

    #[Route('/api/add-new-user', name: 'site_registration_post', methods: ['POST'])]
    public function addNewUser(
        #[MapRequestPayload(acceptFormat: 'form', validationGroups: ['Default', 'register'])] UserSaveRequest $dto,
    ): JsonResponse {
        try {
            $data = $this->userSaveRequestParser->toRegistrationArray($dto);

            $userResponse = $this->userHandler->insertUser($data);

            if (true === is_array($userResponse)) {
                return $this->json($userResponse, Response::HTTP_BAD_REQUEST);
            }

            return $this->json($userResponse);
        } catch (\Throwable $throwable) {
            $this->logger->error(
                'Unable to register user',
                [
                    'email' => $dto->email,
                    'exception' => $throwable,
                ]
            );

            return $this->json(['msg' => MessageConstants::EMPTY_REQUEST], Response::HTTP_BAD_REQUEST);
        }
    }

    public function updateUserAction(
        #[MapEntity(id: 'id')] User $user,
        #[MapRequestPayload(validationGroups: ['Default', 'update'])] UserSaveRequest $dto,
    ): JsonResponse {
        $this->denyAccessUnlessGranted(UserVoter::EDIT, $user);

        try {
            $user = $this->userSaveRequestParser->parse($dto, $user);
            $this->userHandler->saveUser($user);

            return $this->json(['ok' => true, 'id' => $user->getId()]);
        } catch (\Throwable $throwable) {
            $this->logger->error(
                'Unable to update user',
                [
                    'id' => $user->getId(),
                    'exception' => $throwable,
                ]
            );

            return $this->json(['msg' => MessageConstants::EMPTY_REQUEST], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/aktivacija-naloga/{id}', name: 'site_activate_registration', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function activateRegistration(User $user): Response
    {
        return $this->render('@Site/Site/accountActivation.html.twig', [
            'userActivation' => $this->userHandler->activateRegistration($user)
        ]);
    }
}
