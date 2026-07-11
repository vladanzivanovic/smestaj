<?php

declare(strict_types=1);

namespace AdminBundle\Controller\User\Api;

use AdminBundle\Dto\User\UserSaveRequest;
use AdminBundle\Parser\Admin\UserEditRequestParser;
use SiteBundle\Entity\User;
use SiteBundle\Handler\UserHandler;
use SiteBundle\Helper\ConstantsHelper;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class UserEditController extends AbstractController
{
    public function __construct(
        private readonly UserEditRequestParser $requestParser,
        private readonly UserHandler $handler,
        private readonly TranslatorInterface $translator,
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
    ) {
    }

    #[Route('/api/add-user', name: 'admin.add_user_api', methods: ['POST'], options: ['expose' => true])]
    public function add(
        Request $request,
        #[MapRequestPayload(acceptFormat: 'form', validationGroups: ['Default', 'insert'])] UserSaveRequest $dto,
    ): JsonResponse {
        if (false === $this->csrfTokenManager->isTokenValid(new CsrfToken('set_user', $dto->csrfToken))) {
            throw $this->createAccessDeniedException();
        }

        try {
            $user = $this->requestParser->parse($dto);

            $this->handler->save($user, $request->getLocale(), 'SetUserAdmin', false, true);
            $request->getSession()->getFlashBag()->add('message', $this->translator->trans('my_account.personal_info.success.message'));
        } catch (BadRequestHttpException $httpException) {
            return $this->json(['error' => $httpException->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return $this->json(null, Response::HTTP_CREATED);
    }

    #[Route('/api/update-user/{id}', name: 'admin.edit_user_api', methods: ['PUT'], options: ['expose' => true])]
    public function update(
        Request $request,
        #[MapEntity(id: 'id')] User $user,
        #[MapRequestPayload(acceptFormat: 'form', validationGroups: ['Default', 'update'])] UserSaveRequest $dto,
    ): JsonResponse {
        if (false === $this->csrfTokenManager->isTokenValid(new CsrfToken('set_user', $dto->csrfToken))) {
            throw $this->createAccessDeniedException();
        }

        try {
            $user = $this->requestParser->parse($dto, $user);

            $this->handler->save($user, $request->getLocale(), 'UpdateUser', false, null !== $dto->password);
            $request->getSession()->getFlashBag()->add('message', $this->translator->trans('my_account.personal_info.success.message'));
        } catch (BadRequestHttpException $httpException) {
            return $this->json(['error' => $httpException->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return $this->json(null, JsonResponse::HTTP_CREATED);
    }

    #[Route('/api/toggle-user-status/{id}/{status}', name: 'admin.api_toggle_user_status', methods: ['PATCH'], options: ['expose' => true])]
    public function toggleActivation(#[MapEntity(id: 'id')] User $user, int $status): JsonResponse
    {
        $user->setStatus($status);

        $this->handler->saveUser($user);

        $statusText = ConstantsHelper::getConstantName((string) $status, 'STATUS', User::class);

        return $this->json(['text' => $statusText]);
    }

    #[Route('/api/disable-user/{id}', name: 'admin.disable_user_api', methods: ['DELETE'], options: ['expose' => true])]
    public function remove(#[MapEntity(id: 'id')] User $user): JsonResponse
    {
        $user->setStatus(User::STATUS_DISABLED);

        $this->handler->save($user, 'rs', 'UpdateUser', false, false);

        $statusText = ConstantsHelper::getConstantName((string) User::STATUS_DISABLED, 'STATUS', User::class);

        return $this->json(['text' => $statusText]);
    }
}
