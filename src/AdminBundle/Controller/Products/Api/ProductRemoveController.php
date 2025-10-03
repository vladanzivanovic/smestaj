<?php

declare(strict_types=1);

namespace AdminBundle\Controller\Products\Api;

use Psr\Log\LoggerInterface;
use SiteBundle\Entity\Ads;
use SiteBundle\Handler\AdsHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ProductRemoveController extends AbstractController
{
    private AdsHandler $adsHandler;

    private TranslatorInterface $translator;

    private LoggerInterface $logger;

    public function __construct(
        AdsHandler $adsHandler,
        TranslatorInterface $translator,
        LoggerInterface $logger
    ) {

        $this->translator = $translator;
        $this->adsHandler = $adsHandler;
        $this->logger = $logger;
    }

    /**
     * @Route("/api/product/{id}", methods={"DELETE"}, name="admin.remove_product_api")
     *
     * @param Ads $ads
     *
     * @return JsonResponse
     */
    public function remove(Ads $ads): JsonResponse
    {
        try {
            $this->adsHandler->deleteAds($ads);

            return new JsonResponse(['message' => $this->translator->trans('data_success_deleted')]);
        } catch (\Throwable $throwable) {
            $this->logger->error(
                'Failed to delete ad from admin',
                [
                    'adId' => $ads->getId(),
                    'errorMessage' => $throwable->getMessage(),
                    'errorCode' => $throwable->getCode(),
                    'errorTrace' => $throwable->getTraceAsString(),
                    'errorFile' => $throwable->getFile(),
                    'errorLine' => $throwable->getLine(),
                ]
            );

            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }
    }
}
