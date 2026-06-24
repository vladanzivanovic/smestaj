<?php

declare(strict_types=1);

namespace AdminBundle\Controller\InfoPages\Api;

use AdminBundle\Dto\InfoPage\InfoPageRemoveDto;
use AdminBundle\Handler\InfoPageEditHandler;
use RuntimeException;
use SiteBundle\Entity\AdsInfoPage;
use SiteBundle\Entity\User;
use SiteBundle\Repository\AdsInfoPageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class InfoPageRemoveController extends AbstractController
{
    public function __construct(
        private readonly AdsInfoPageRepository $adsInfoPageRepository,
        private readonly InfoPageEditHandler $editHandler,
    ) {
    }

    // Controller-pattern layer notes (intentional deviations vs. canonical 5-layer):
    // - No Parser: delete-by-id has no DTO->entity translation; entity is loaded by repo.
    // - No entity-level Validator: domain invariants are not enforced on delete.
    // - No View: response is a static ack — matches existing precedent
    //   (InfoPageTogglePublishController, ProductRemoveController, ex-InfoPageRestoreController).
    // - DTO carries only the `obriši` confirm token. Because the param has no default
    //   and is non-nullable, MapQueryString always denormalizes and validates (empty
    //   query included); an empty or wrong token fails the EqualTo('obriši') constraint
    //   and the resolver throws HttpException(422) — the backend's authoritative gate
    //   (CONTEXT_SPEC.md §B; rationale in IMPLEMENTATION_PLAN.md Amendment 1).
    #[Route('/api/info-pages/{id}', name: 'admin.api.info_pages.remove', methods: ['DELETE'], requirements: ['id' => '\d+'], options: ['expose' => true])]
    public function remove(
        int $id,
        #[MapQueryString(validationFailedStatusCode: Response::HTTP_UNPROCESSABLE_ENTITY)]
        InfoPageRemoveDto $dto,
    ): JsonResponse {
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
