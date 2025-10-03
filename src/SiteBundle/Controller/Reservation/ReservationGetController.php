<?php

namespace SiteBundle\Controller\Reservation;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SiteBundle\Constants\MessageConstants;
use SiteBundle\Controller\SiteController;
use SiteBundle\Entity\Ads;
use SiteBundle\Entity\Reservation;
use SiteBundle\Entity\User;
use SiteBundle\Handler\UserReservationHandler;
use SiteBundle\Helper\ConstantsHelper;
use SiteBundle\Repository\AdsRepository;
use SiteBundle\Repository\ReservationRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

class ReservationGetController extends SiteController
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
    public function getReservationForAdAction(\DateTime $dateTime, Ads $ads): JsonResponse
    {
        if(null === $dateTime) {
            return $this->json(['success' => false, 'msg' => MessageConstants::EMPTY_REQUEST]);
        }

        $data = $this->reservationRepository->getByMonthAndYear($dateTime, $ads);

        return $this->json([ 'success' => true, 'data' => $data]);
    }

    /**
     * @ParamConverter("reservation", class="SiteBundle\Entity\Reservation", options={"id" = "reservationId"})
     * @Template("@Site/Site/reservationSuccess.html.twig")
     * @param Reservation $reservation
     *
     * @return array
     */
    public function successReservationPageAction(Reservation $reservation)
    {
        $ads = $reservation->getAdsId();

        $notification = ConstantsHelper::getConstantName($reservation->getNotificationType(), 'NOTIFICATION', Reservation::class);

        return [
            'reservation' => $reservation,
            'notification_type' => 'reservation.'.$notification,
            'ads' => $ads,
            'ads_suggestions' => $this->adsRepository->getSuggestions($ads->getCategoryId()->getId())
        ];
    }
}
