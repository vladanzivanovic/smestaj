<?php

declare(strict_types=1);


namespace SiteBundle\Controller\Api\Ads;

use Psr\Log\LoggerInterface;
use SiteBundle\Dto\Ads\AdsSaveRequest;
use SiteBundle\Handler\AdsHandler;
use SiteBundle\Controller\SiteController;
use SiteBundle\Entity\Ads;
use SiteBundle\Exceptions\ApplicationException;
use SiteBundle\Parser\AdsEditParser;
use SiteBundle\Parser\AdsSaveRequestParser;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AdsEditController extends SiteController
{
    private AdsHandler $adsHandler;

    private TranslatorInterface $translator;

    private AdsEditParser $adsEditParser;

    private AdsSaveRequestParser $adsSaveRequestParser;

    private CsrfTokenManagerInterface $csrfTokenManager;

    private LoggerInterface $logger;

    public function __construct(
        AdsHandler $adsHandler,
        TranslatorInterface $translator,
        AdsEditParser $adsEditParser,
        AdsSaveRequestParser $adsSaveRequestParser,
        CsrfTokenManagerInterface $csrfTokenManager,
        LoggerInterface $logger
    ) {
        $this->adsHandler = $adsHandler;
        $this->translator = $translator;
        $this->adsEditParser = $adsEditParser;
        $this->adsSaveRequestParser = $adsSaveRequestParser;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->logger = $logger;
    }

    #[Route('/api/product', name: 'site_ads_save', methods: ['POST'])]
    public function insert(#[MapRequestPayload(acceptFormat: 'form')] AdsSaveRequest $dto): JsonResponse
    {
        if (false === $this->csrfTokenManager->isTokenValid(new CsrfToken('set_ad', $dto->csrfToken))) {
            throw $this->createAccessDeniedException();
        }

        try {
            $ads = $this->adsSaveRequestParser->parse($dto);

            $this->adsHandler->save($ads);

            $this->addFlash('message', $this->translator->trans('data.success_send'));

            return $this->json([], Response::HTTP_CREATED);
        } catch (\Throwable $throwable) {
            $this->logger->error(
                'Failed to save ad',
                [
                    'message' => $throwable->getMessage(),
                    'csrfPrefix' => substr($dto->csrfToken, 0, 8) . '…',
                    'stackTrace' => $throwable->getTraceAsString(),
                    'errorFile' => $throwable->getFile(),
                    'errorLine' => $throwable->getLine(),
                    'previousStackTrace' => null !== $throwable->getPrevious() ? $throwable->getPrevious()->getTraceAsString() : null,
                ]
            );

            return $this->json([], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/product/{id}', name: 'site_ads_update', methods: ['PUT'])]
    public function update(
        #[MapEntity] Ads $ads,
        #[MapRequestPayload(acceptFormat: 'form')] AdsSaveRequest $dto
    ): JsonResponse {
        if (false === $this->csrfTokenManager->isTokenValid(new CsrfToken('set_ad', $dto->csrfToken))) {
            throw $this->createAccessDeniedException();
        }

        try {
            $ads = $this->adsSaveRequestParser->parse($dto, $ads);

            $this->adsHandler->save($ads);

            $this->addFlash('message', $this->translator->trans('data.success_send'));

            return $this->json(null, Response::HTTP_CREATED);
        } catch (\Throwable $throwable) {
            $this->logger->error(
                'Failed to save ad',
                [
                    'message' => $throwable->getMessage(),
                    'csrfPrefix' => substr($dto->csrfToken, 0, 8) . '…',
                    'stackTrace' => $throwable->getTraceAsString(),
                    'errorFile' => $throwable->getFile(),
                    'errorLine' => $throwable->getLine(),
                    'previousStackTrace' => null !== $throwable->getPrevious() ? $throwable->getPrevious()->getTraceAsString() : null,
                ]
            );

            return $this->json(null, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/api/product/{alias}', name: 'remove_ad', methods: ['DELETE'])]
    public function removeAd(#[MapEntity(mapping: ['alias' => 'alias'])] Ads $ads)
    {
        try {
            $this->adsHandler->deleteAds($ads);

            return $this->json(['message' => $this->translator->trans('data_success_deleted')]);
        } catch (\Throwable $throwable) {
            $this->logger->error(
                'Failed to delete ad',
                [
                    'adId' => $ads->getId(),
                    'errorMessage' => $throwable->getMessage(),
                    'errorCode' => $throwable->getCode(),
                    'errorTrace' => $throwable->getTraceAsString(),
                    'errorFile' => $throwable->getFile(),
                    'errorLine' => $throwable->getLine(),
                ]
            );
        }
    }
}
