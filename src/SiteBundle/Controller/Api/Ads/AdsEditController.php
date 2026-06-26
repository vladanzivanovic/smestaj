<?php


namespace SiteBundle\Controller\Api\Ads;

use Psr\Log\LoggerInterface;
use SiteBundle\Dto\Ads\AdsInsertRequest;
use SiteBundle\Dto\Ads\AdsUpdateRequest;
use SiteBundle\Handler\AdsHandler;
use SiteBundle\Controller\SiteController;
use SiteBundle\Entity\Ads;
use SiteBundle\Exceptions\ApplicationException;
use SiteBundle\Parser\AdsEditParser;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class AdsEditController extends SiteController
{
    private AdsHandler $adsHandler;

    private TranslatorInterface $translator;

    private AdsEditParser $adsEditParser;

    private LoggerInterface $logger;

    public function __construct(
        AdsHandler $adsHandler,
        TranslatorInterface $translator,
        AdsEditParser $adsEditParser,
        LoggerInterface $logger
    ) {
        $this->adsHandler = $adsHandler;
        $this->translator = $translator;
        $this->adsEditParser = $adsEditParser;
        $this->logger = $logger;
    }

    #[Route('/api/product', name: 'site_ads_save', methods: ['POST'])]
    public function insert(AdsInsertRequest $insertRequest): JsonResponse
    {
        try {
            if (
                (false === $this->isCsrfTokenValid('set_ad', $insertRequest->csrfToken)) ||
                null === $this->getUser()
            ) {
                throw $this->createAccessDeniedException();
            }

            $ads = $this->adsEditParser->parse($insertRequest->body, $this->getUser(), $this->getUser());

            $this->adsHandler->save($ads);

            $this->addFlash('message', $this->translator->trans('data.success_send'));

            return $this->json([], Response::HTTP_CREATED);
        } catch (\Throwable $throwable) {
            $this->logger->error(
                'Failed to save ad',
                [
                    'message' => $throwable->getMessage(),
                    'csrfPrefix' => substr($insertRequest->csrfToken, 0, 8) . '…',
                    'bodyKeys' => array_keys($insertRequest->body->all()),
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
    public function update(#[MapEntity] Ads $ads, AdsUpdateRequest $updateRequest): JsonResponse
    {
        try {
            if (
                (false === $this->isCsrfTokenValid('set_ad', $updateRequest->csrfToken)) ||
                null === $this->getUser()
            ) {
                throw $this->createAccessDeniedException();
            }

            $ads = $this->adsEditParser->parse($updateRequest->body, $this->getUser(), $this->getUser(), $ads);

            $this->adsHandler->save($ads);

            $this->addFlash('message', $this->translator->trans('data.success_send'));

            return $this->json(null, Response::HTTP_CREATED);
        } catch (\Throwable $throwable) {
            $this->logger->error(
                'Failed to save ad',
                [
                    'message' => $throwable->getMessage(),
                    'csrfPrefix' => substr($updateRequest->csrfToken, 0, 8) . '…',
                    'bodyKeys' => array_keys($updateRequest->body->all()),
                    'stackTrace' => $throwable->getTraceAsString(),
                    'errorFile' => $throwable->getFile(),
                    'errorLine' => $throwable->getLine(),
                    'previousStackTrace' => null !== $throwable->getPrevious() ? $throwable->getPrevious()->getTraceAsString() : null,
                ]
            );

            return $this->json(null, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/api/product/{alias}', methods: ['DELETE'], name: 'remove_ad')]
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
