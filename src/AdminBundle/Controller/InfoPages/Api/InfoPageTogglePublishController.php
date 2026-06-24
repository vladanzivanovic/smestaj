<?php

declare(strict_types=1);

namespace AdminBundle\Controller\InfoPages\Api;

use AdminBundle\Handler\InfoPageEditHandler;
use SiteBundle\Entity\AdsInfoPage;
use SiteBundle\Repository\AdsInfoPageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class InfoPageTogglePublishController extends AbstractController
{
    public function __construct(
        private readonly AdsInfoPageRepository $adsInfoPageRepository,
        private readonly InfoPageEditHandler $editHandler
    ) {
    }

    #[Route('/api/info-pages/{id}/publish', name: 'admin.api.info_pages.toggle_publish', methods: ['POST'], requirements: ['id' => '\d+'], options: ['expose' => true])]
    public function togglePublish(Request $request, int $id): JsonResponse
    {
        $entity = $this->adsInfoPageRepository->find($id);

        if (false === $entity instanceof AdsInfoPage) {
            throw new NotFoundHttpException();
        }

        $target = $this->parseBool($request->request->get('published'));

        $result = $this->editHandler->togglePublish($entity, $target);

        $status = true === $result['ok'] ? Response::HTTP_OK : Response::HTTP_UNPROCESSABLE_ENTITY;

        return new JsonResponse($result, $status);
    }

    private function parseBool(mixed $value): bool
    {
        if (true === $value || 1 === $value) {
            return true;
        }

        if (false === is_string($value)) {
            return false;
        }

        $normalised = strtolower(trim($value));

        return 'true' === $normalised || '1' === $normalised || 'on' === $normalised;
    }
}
