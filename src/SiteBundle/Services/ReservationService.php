<?php

namespace SiteBundle\Services;

use Doctrine\ORM\EntityManager;
use SiteBundle\Constants\MessageConstants;
use SiteBundle\Entity\Ads;
use SiteBundle\Entity\Reservation;
use SiteBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class ReservationService extends ServiceContainer
{
    public function __construct(EntityManager $entity, TokenStorage $tokenStorage)
    {
        parent::__construct($entity, $tokenStorage);
    }

    public function checkUserPermissionForReservation(User $user, Reservation $reservation)
    {
        /** @var Ads $ads */
        $ads = $reservation->getAdsId();
        /** @var User $userFromAds */
        $userFromAds = $ads->getSysCreatedUserId();

        if($userFromAds->getId() === $user->getId()) {
            return true;
        }

        return false;
    }

    public function alreadyConfirmedCheck($reservation)
    {
        if(!$reservation instanceof Reservation) {
            $reservation = $this->em->getRepository('SiteBundle:Reservation')->find($reservation);
        }

        if($reservation->getStatus() != Reservation::STATUS_PENDING) {
            return $reservation->getStatus() == Reservation::STATUS_CONFIRMED ?
                MessageConstants::RESERVATION_ALREADY_CONFIRMED : MessageConstants::RESERVATION_REJECTED;
        }

        return null;
    }
}