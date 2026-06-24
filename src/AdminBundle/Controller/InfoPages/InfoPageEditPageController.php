<?php

declare(strict_types=1);

namespace AdminBundle\Controller\InfoPages;

use AdminBundle\Formatter\InfoPageEditResponseFormatter;
use SiteBundle\Entity\AdsInfoPage;
use SiteBundle\Repository\AdsInfoPageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class InfoPageEditPageController extends AbstractController
{
    public function __construct(
        private readonly InfoPageEditResponseFormatter $responseFormatter,
        private readonly AdsInfoPageRepository $adsInfoPageRepository
    ) {
    }

    #[Route('/info-pages/new', name: 'admin.info_pages.new', methods: ['GET'])]
    public function create(): Response
    {
        return $this->render(
            '@Admin/Pages/infoPageEdit.html.twig',
            $this->responseFormatter->formatResponse(null) + ['routeName' => 'admin.info_pages']
        );
    }

    #[Route('/info-pages/{id}/edit', name: 'admin.info_pages.edit', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function edit(int $id): Response
    {
        $entity = $this->adsInfoPageRepository->find($id);

        if (false === $entity instanceof AdsInfoPage) {
            throw new NotFoundHttpException();
        }

        return $this->render(
            '@Admin/Pages/infoPageEdit.html.twig',
            $this->responseFormatter->formatResponse($entity) + ['routeName' => 'admin.info_pages']
        );
    }
}
