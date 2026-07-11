<?php

declare(strict_types=1);

namespace SiteBundle\Controller\Reservation;


use SiteBundle\Constants\MessageConstants;
use SiteBundle\Controller\SiteController;
use SiteBundle\Entity\Ads;
use SiteBundle\Entity\Reservation;
use SiteBundle\Entity\User;
use SiteBundle\Handler\UserReservationHandler;
use SiteBundle\Helper\ConstantsHelper;
use SiteBundle\Repository\AdsRepository;
use SiteBundle\Repository\ReservationRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ReservationGetController extends SiteController
{
    private ReservationRepository $reservationRepository;
    private AdsRepository $adsRepository;

    public function __construct(
        ReservationRepository $reservationRepository,
        AdsRepository $adsRepository
    ) {
        $this->reservationRepository = $reservationRepository;
        $this->adsRepository = $adsRepository;
    }

    /**
     * Get reservation for given month and year
     *
     * @param \DateTime $dateTime
     * @param Ads       $ads
     *
     * @return JsonResponse
     */
    public function getReservationForAdAction(\DateTime $dateTime, #[MapEntity(id: 'adsId')] Ads $ads): JsonResponse
    {
        if(null === $dateTime) {
            return $this->json(['success' => false, 'msg' => MessageConstants::EMPTY_REQUEST]);
        }

        $data = $this->reservationRepository->getByMonthAndYear($dateTime, $ads);

        return $this->json([ 'success' => true, 'data' => $data]);
    }

    /**
     * @param Reservation $reservation
     *
     * @return Response
     */
    public function successReservationPageAction(#[MapEntity(id: 'reservationId')] Reservation $reservation): Response
    {
        $ads = $reservation->getAdsId();

        $notification = ConstantsHelper::getConstantName($reservation->getNotificationType(), 'NOTIFICATION', Reservation::class);

        return $this->render('@Site/Site/reservationSuccess.html.twig', [
            'reservation' => $reservation,
            'notification_type' => 'reservation.'.$notification,
            'ads' => $ads,
            'ads_suggestions' => $this->adsRepository->getSuggestions($ads->getCategoryId()->getId())
        ]);
    }
}
