<?php


namespace SiteBundle\Controller\Api\Ads;

use Psr\Log\LoggerInterface;
use SiteBundle\Handler\AdsHandler;
use SiteBundle\Controller\SiteController;
use SiteBundle\Entity\Ads;
use SiteBundle\Exceptions\ApplicationException;
use SiteBundle\Parser\AdsEditParser;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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

    /**
     * @Route("/api/product", name="site_ads_save", methods={"POST"})
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \LogicException
     * @throws \InvalidArgumentException
     * @throws ApplicationException
     * @throws \Doctrine\ORM\ORMException
     */
    public function insert(Request $request): JsonResponse
    {
        try {
            $csrf = $request->request->get('_csrf_token');

            if (
                (false === $this->isCsrfTokenValid('set_ad', $csrf)) ||
                null === $this->getUser()
            ) {
                $this->createAccessDeniedException();
            }

            $ads = $this->adsEditParser->parse($request->request, $this->getUser(), $this->getUser());

            $this->adsHandler->save($ads);

            $request->getSession()->getFlashBag()->add('message', $this->translator->trans('data.success_send'));

            return $this->json([], Response::HTTP_CREATED);
        } catch (\Throwable $throwable) {
            $this->logger->error(
                'Failed to save ad',
                [
                    'message' => $throwable->getMessage(),
                    'request' => (string) $request,
                    'stackTrace' => $throwable->getTraceAsString(),
                    'errorFile' => $throwable->getFile(),
                    'errorLine' => $throwable->getLine(),
                    'previousStackTrace' => null !== $throwable->getPrevious() ? $throwable->getPrevious()->getTraceAsString() : null,
                ]
            );

            return $this->json([], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Route("/product/{id}", name="site_ads_update", methods={"PUT"})
     * @param Ads     $ads
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function update(Ads $ads, Request $request): JsonResponse
    {
        try {
            $csrf = $request->request->get('_csrf_token');

            if (
                (false === $this->isCsrfTokenValid('set_ad', $csrf)) ||
                null === $this->getUser()
            ) {
                $this->createAccessDeniedException();
            }

            $ads = $this->adsEditParser->parse($request->request, $this->getUser(), $this->getUser(), $ads);

            $this->adsHandler->save($ads);

            $request->getSession()->getFlashBag()->add('message', $this->translator->trans('data.success_send'));

            return $this->json(null, Response::HTTP_CREATED);
        } catch (\Throwable $throwable) {
            $this->logger->error(
                'Failed to save ad',
                [
                    'message' => $throwable->getMessage(),
                    'request' => (string) $request,
                    'stackTrace' => $throwable->getTraceAsString(),
                    'errorFile' => $throwable->getFile(),
                    'errorLine' => $throwable->getLine(),
                    'previousStackTrace' => null !== $throwable->getPrevious() ? $throwable->getPrevious()->getTraceAsString() : null,
                ]
            );

            return $this->json(null, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Route("/api/product/{alias}", methods={"DELETE"}, name="remove_ad")
     *
     * @param Ads $ads
     *
     * @return JsonResponse
     */
    public function removeAd(Ads $ads)
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
