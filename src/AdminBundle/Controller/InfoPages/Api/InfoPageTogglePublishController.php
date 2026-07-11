<?php

declare(strict_types=1);

namespace AdminBundle\Controller\InfoPages\Api;

use AdminBundle\Dto\InfoPage\InfoPageTogglePublishRequest;
use AdminBundle\Handler\InfoPageEditHandler;
use SiteBundle\Entity\AdsInfoPage;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final class InfoPageTogglePublishController extends AbstractController
{
    public function __construct(
        private readonly InfoPageEditHandler $editHandler
    ) {
    }

    #[Route('/api/info-pages/{id}/publish', name: 'admin.api.info_pages.toggle_publish', methods: ['POST'], requirements: ['id' => '\d+'], options: ['expose' => true])]
    public function togglePublish(
        #[MapEntity(id: 'id')] AdsInfoPage $entity,
        #[MapRequestPayload(acceptFormat: 'form')] InfoPageTogglePublishRequest $dto
    ): JsonResponse {
        $result = $this->editHandler->togglePublish($entity, $dto->published);

        $status = true === $result['ok'] ? Response::HTTP_OK : Response::HTTP_UNPROCESSABLE_ENTITY;

        return new JsonResponse($result, $status);
    }
}
