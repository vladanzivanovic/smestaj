<?php

namespace SiteBundle\Controller\Api\User;


use Psr\Log\LoggerInterface;
use SiteBundle\Constants\MessageConstants;
use SiteBundle\Controller\SiteController;
use SiteBundle\Dto\User\RegisterUserRequest;
use SiteBundle\Dto\User\UpdateUserRequest;
use SiteBundle\Entity\User;
use SiteBundle\Handler\UserHandler;
use SiteBundle\Parser\RegisterUserRequestParser;
use SiteBundle\Parser\UpdateUserRequestParser;
use SiteBundle\Services\UserService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserEditController extends SiteController
{
    private UserHandler $userHandler;

    private LoggerInterface $logger;

    private RegisterUserRequestParser $registerUserRequestParser;

    private UpdateUserRequestParser $updateUserRequestParser;

    private UserService $userService;

    public function __construct(
        UserHandler $userHandler,
        LoggerInterface $logger,
        RegisterUserRequestParser $registerUserRequestParser,
        UpdateUserRequestParser $updateUserRequestParser,
        UserService $userService
    ) {
        $this->userHandler = $userHandler;
        $this->logger = $logger;
        $this->registerUserRequestParser = $registerUserRequestParser;
        $this->updateUserRequestParser = $updateUserRequestParser;
        $this->userService = $userService;
    }

    #[Route('/api/add-new-user', name: 'site_registration_post', methods: ['POST'])]
    public function addNewUser(
        ?RegisterUserRequest $dto = null,
    ): JsonResponse {
        if (null === $dto) {
            return $this->json(['msg' => MessageConstants::EMPTY_REQUEST], Response::HTTP_BAD_REQUEST);
        }

        try {
            $data = $this->registerUserRequestParser->toArray($dto);

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
        int $id,
        UpdateUserRequest $dto,
    ): JsonResponse {
        $data = $this->updateUserRequestParser->toArray($dto);

        $userResponse = $this->userService->setUpUser($data, $id);
        $userResponse['id'] = $id;

        return $this->json($userResponse);
    }

    #[Route('/aktivacija-naloga/{id}', name: 'site_activate_registration', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function activateRegistration(User $user): Response
    {
        return $this->render('@Site/Site/accountActivation.html.twig', [
            'userActivation' => $this->userHandler->activateRegistration($user)
        ]);
    }
}
