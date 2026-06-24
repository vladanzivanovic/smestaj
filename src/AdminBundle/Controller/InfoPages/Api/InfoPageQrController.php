<?php

declare(strict_types=1);

namespace AdminBundle\Controller\InfoPages\Api;

use SiteBundle\Entity\AdsInfoPage;
use SiteBundle\Repository\AdsInfoPageRepository;
use SiteBundle\Services\InfoPage\QrCodeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class InfoPageQrController extends AbstractController
{
    public function __construct(
        private readonly AdsInfoPageRepository $adsInfoPageRepository,
        private readonly QrCodeService $qrCodeService,
        private readonly UrlGeneratorInterface $urlGenerator
    ) {
    }

    #[Route('/api/info-pages/{id}/qr.png', name: 'admin.api.info_pages.qr', methods: ['GET'], requirements: ['id' => '\d+'], options: ['expose' => true])]
    public function qr(int $id): Response
    {
        $entity = $this->adsInfoPageRepository->find($id);

        if (false === $entity instanceof AdsInfoPage) {
            throw new NotFoundHttpException();
        }

        $publicUrl = $this->urlGenerator->generate(
            'site.info_page.view',
            ['slug' => $entity->getSlug()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $png = $this->qrCodeService->generatePng($publicUrl);

        return new Response($png, Response::HTTP_OK, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => sprintf('attachment; filename="%s-qr.png"', $entity->getSlug()),
        ]);
    }
}
