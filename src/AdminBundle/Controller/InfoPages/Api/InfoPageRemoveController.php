<?php

declare(strict_types=1);

namespace AdminBundle\Controller\InfoPages\Api;

use AdminBundle\Dto\InfoPage\InfoPageRemoveDto;
use AdminBundle\Handler\InfoPageEditHandler;
use AdminBundle\ValueResolver\InfoPageRemoveConfirmValueResolver;
use RuntimeException;
use SiteBundle\Entity\AdsInfoPage;
use SiteBundle\Entity\User;
use SiteBundle\Repository\AdsInfoPageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class InfoPageRemoveController extends AbstractController
{
    public function __construct(
        private readonly AdsInfoPageRepository $adsInfoPageRepository,
        private readonly InfoPageEditHandler $editHandler,
    ) {
    }

    #[Route('/api/info-pages/{id}', name: 'admin.api.info_pages.remove', methods: ['DELETE'], requirements: ['id' => '\d+'], options: ['expose' => true])]
    public function remove(
        int $id,
        #[ValueResolver(InfoPageRemoveConfirmValueResolver::class)] InfoPageRemoveDto $dto,
    ): JsonResponse {
        if ('obriši' !== $dto->confirm) {
            throw $this->createAccessDeniedException();
        }

        $user = $this->getUser();

        if (false === $user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $entity = $this->adsInfoPageRepository->find($id);

        if (false === $entity instanceof AdsInfoPage) {
            throw new NotFoundHttpException();
        }

        try {
            $this->editHandler->hardDelete($entity, $user);
        } catch (RuntimeException $exception) {
            return new JsonResponse(['ok' => false, 'error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse(['ok' => true]);
    }
}
