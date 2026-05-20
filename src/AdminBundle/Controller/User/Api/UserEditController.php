<?php

declare(strict_types=1);

namespace AdminBundle\Controller\User\Api;

use AdminBundle\Parser\Admin\UserEditRequestParser;
use SiteBundle\Entity\User;
use SiteBundle\Handler\UserHandler;
use SiteBundle\Helper\ConstantsHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final class UserEditController extends AbstractController
{
    private UserEditRequestParser $requestParser;

    private UserHandler $handler;

    private TranslatorInterface $translator;

    public function __construct(
        UserEditRequestParser $requestParser,
        UserHandler $handler,
        TranslatorInterface $translator
    ) {
        $this->requestParser = $requestParser;
        $this->handler = $handler;
        $this->translator = $translator;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \ReflectionException
     */
    #[Route('/api/add-user', name: 'admin.add_user_api', methods: ['POST'], options: ['expose' => true])]
    public function add(Request $request): JsonResponse
    {
        $csrf = $request->request->get('_csrf_token');
        $bag = $request->request;

        if (false === $this->isCsrfTokenValid('set_user', $csrf)) {
            $this->createAccessDeniedException();
        }

        try {
            $user = $this->requestParser->parse($bag);

            if (null !== $bag->get('address')) {
                $this->requestParser->parseAddress($bag, $user);
            }

            $this->handler->save($user, $request->getLocale(), 'SetUserAdmin', false, true);
            $request->getSession()->getFlashBag()->add('message', $this->translator->trans('my_account.personal_info.success.message'));

        } catch (BadRequestHttpException $httpException) {
            return $this->json(['error' => $httpException->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return $this->json(null, Response::HTTP_CREATED);
    }

    /**
     * @param Request $request
     * @param User    $user
     *
     * @return JsonResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    #[Route('/api/update-user/{id}', name: 'admin.edit_user_api', methods: ['PUT'], options: ['expose' => true])]
    public function update(Request $request, User $user): JsonResponse
    {
        $csrf = $request->request->get('_csrf_token');
        $bag = $request->request;

        if (false === $this->isCsrfTokenValid('set_user', $csrf)) {
            $this->createAccessDeniedException();
        }

        try {
            $user = $this->requestParser->parse($bag, $user);

            if (null !== $bag->get('address')) {
                $this->requestParser->parseAddress($bag, $user);
            }

            $this->handler->save($user, $request->getLocale(), 'UpdateUser', false, $bag->get('password') !== null);
            $request->getSession()->getFlashBag()->add('message', $this->translator->trans('my_account.personal_info.success.message'));

        } catch (BadRequestHttpException $httpException) {
            return $this->json(['error' => $httpException->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return $this->json(null, JsonResponse::HTTP_CREATED);
    }

    /**
     * @param User $user
     * @param int  $status
     *
     * @return JsonResponse
     */
    #[Route('/api/toggle-user-status/{id}/{status}', name: 'admin.api_toggle_user_status', methods: ['PATCH'], options: ['expose' => true])]
    public function toggleActivation(User $user, int $status): JsonResponse
    {
        $user->setStatus((int) $status);

        $this->handler->saveUser($user);

        $statusText = ConstantsHelper::getConstantName((string) $status, 'STATUS', User::class);

        return $this->json(['text' => $statusText]);
    }

    /**
     * @param User $user
     *
     * @return JsonResponse
     */
    #[Route('/api/disable-user/{id}', name: 'admin.disable_user_api', methods: ['DELETE'], options: ['expose' => true])]
    public function remove(User $user): JsonResponse
    {
        $user->setStatus(User::STATUS_DISABLED);

        $this->handler->save($user, 'rs', 'UpdateUser', false, false);


        return $this->json(['text' => $statusText]);
    }
}
