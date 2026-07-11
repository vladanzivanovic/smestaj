<?php

declare(strict_types=1);

namespace SiteBundle\Controller\Reservation;

use Psr\Log\LoggerInterface;
use SiteBundle\Constants\MessageConstants;
use SiteBundle\Controller\SiteController;
use SiteBundle\Dto\Reservation\SendReservationRequest;
use SiteBundle\Entity\Ads;
use SiteBundle\Handler\UserReservationHandler;
use SiteBundle\Parser\SendReservationRequestParser;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final class ReservationEditController extends SiteController
{
    private UserReservationHandler $reservationHandler;

    private LoggerInterface $logger;

    private SendReservationRequestParser $sendReservationRequestParser;

    public function __construct(
        UserReservationHandler $reservationHandler,
        LoggerInterface $logger,
        SendReservationRequestParser $sendReservationRequestParser
    ) {
        $this->reservationHandler = $reservationHandler;
        $this->logger = $logger;
        $this->sendReservationRequestParser = $sendReservationRequestParser;
    }

    #[Route('/api/ad-reservation/{slug}', name: 'site_ad_reservation', methods: ['POST'])]
    public function sendReservation(
        #[MapEntity(mapping: ['slug' => 'alias'])] Ads $ads,
        #[MapRequestPayload(acceptFormat: 'form')] SendReservationRequest $dto
    ): JsonResponse {
        if (false === $this->isCsrfTokenValid('ad_reservation', $dto->csrfToken)) {
            return new JsonResponse(['msg' => MessageConstants::EMPTY_REQUEST], Response::HTTP_BAD_REQUEST);
        }

        try {
            $data = $this->sendReservationRequestParser->toArray($dto, $ads);

            $reservation = $this->reservationHandler->setReservation($data);

            return $this->json(['reservation_id' => $reservation->getId()]);
        } catch (\Throwable $throwable) {
            $this->logger->error(
                'Reservation failed',
                [
                    'adsId' => $ads->getId(),
                    'reservationToken' => substr($dto->csrfToken, 0, 8) . '…',
                    'ads' => (array) $ads,
                    'errorMessage' => $throwable->getMessage(),
                    'errorCode' => $throwable->getCode(),
                    'errorTrace' => $throwable->getTraceAsString(),
                    'errorFile' => $throwable->getFile(),
                    'errorLine' => $throwable->getLine(),
                ],
            );
        }

        return new JsonResponse(['msg' => MessageConstants::EMPTY_REQUEST], Response::HTTP_BAD_REQUEST);
    }
}
