<?php

declare(strict_types=1);

namespace AdminBundle\Controller\InfoPages\Api;

use AdminBundle\Handler\InfoPageEditHandler;
use AdminBundle\Parser\InfoPageEditRequestParser;
use SiteBundle\Entity\AdsInfoPage;
use SiteBundle\Repository\AdsInfoPageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final class InfoPageEditController extends AbstractController
{
    public function __construct(
        private readonly InfoPageEditRequestParser $requestParser,
        private readonly InfoPageEditHandler $editHandler,
        private readonly AdsInfoPageRepository $adsInfoPageRepository,
        private readonly TranslatorInterface $translator
    ) {
    }

    #[Route('/api/info-pages/{id}', name: 'admin.api.info_pages.update', requirements: ['id' => '\d+'], options: ['expose' => true], methods: ['POST'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $entity = $this->adsInfoPageRepository->find($id);

        if (false === $entity instanceof AdsInfoPage) {
            throw new NotFoundHttpException();
        }

        $model = $this->requestParser->parse($request->request, $entity);

        if (0 < count($model->parserViolations)) {
            return new JsonResponse([
                'ok' => false,
                'violations' => $model->parserViolations,
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $result = $this->editHandler->update($entity, $model);

        if (0 < count($result['violations'])) {
            return new JsonResponse([
                'ok' => false,
                'violations' => $result['violations'],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->addFlash('message', $this->translator->trans('saved'));

        return new JsonResponse([
            'ok' => true,
            'violations' => [],
        ]);
    }
}
