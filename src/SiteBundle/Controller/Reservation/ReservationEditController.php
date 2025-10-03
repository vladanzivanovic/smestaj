<?php


namespace SiteBundle\Controller\Reservation;


use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SiteBundle\Constants\MessageConstants;
use SiteBundle\Controller\SiteController;
use SiteBundle\Entity\Ads;
use SiteBundle\Entity\Reservation;
use SiteBundle\Entity\User;
use SiteBundle\Handler\UserReservationHandler;
use SiteBundle\Helper\ConstantsHelper;
use SiteBundle\Services\ReservationService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReservationEditController extends SiteController
{
    private $reservationHandler;

    private LoggerInterface $logger;

    /**
     * ReservationEditController constructor.
     *
     * @param UserReservationHandler $reservationHandler
     */
    public function __construct(
        UserReservationHandler $reservationHandler,
        LoggerInterface $logger
    ) {
        $this->reservationHandler = $reservationHandler;
        $this->logger = $logger;
    }

    /**
     * @Route("/api/ad-reservation/{slug}", name="site_ad_reservation", methods={"POST"})
     * @ParamConverter("ads", options={"mapping": {"slug": "alias"}})
     * @param Ads     $ads
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function sendReservation( Ads $ads, Request $request): JsonResponse
    {
        $data = $this->requestToArray($request);

        if(!empty($data) && $this->isCsrfTokenValid('ad_reservation', $data['token'])) {

            try {
                $this->emptyValueSetToNull($data, ['notificationtype']);
                $data['adsId'] = $ads;

                $reservation = $this->reservationHandler->setReservation($data);

                return $this->json(['reservation_id' => $reservation->getId()]);
            } catch (\Throwable $throwable) {
                $this->logger->error(
                    'Reservation failed',
                    [
                        'request' => $request,
                        'ads' => (array) $ads,
                        'errorMessage' => $throwable->getMessage(),
                        'errorCode' => $throwable->getCode(),
                        'errorTrace' => $throwable->getTraceAsString(),
                        'errorFile' => $throwable->getFile(),
                        'errorLine' => $throwable->getLine(),
                    ],
                );
            }
        }

        return new JsonResponse(['msg' => MessageConstants::EMPTY_REQUEST], Response::HTTP_BAD_REQUEST);
    }
}
