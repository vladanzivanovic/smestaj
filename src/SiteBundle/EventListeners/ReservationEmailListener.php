<?php

namespace SiteBundle\EventListeners;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use SiteBundle\Constants\EmailConstants;
use SiteBundle\Entity\Reservation;
use SiteBundle\Helper\ConstantsHelper;
use SiteBundle\Helper\Email;
use SiteBundle\Repository\MediaRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ReservationEmailListener
{
    private MediaRepository $mediaRepository;
    private ParameterBagInterface $parameterBag;
    private Email $email;

    public function __construct(
        MediaRepository $mediaRepository,
        ParameterBagInterface $parameterBag,
        Email $email
    ) {
        $this->mediaRepository = $mediaRepository;
        $this->parameterBag = $parameterBag;
        $this->email = $email;
    }

    public function postPersist(LifecycleEventArgs $event)
    {
        $reservation = $event->getObject();

        if (!$reservation instanceof Reservation) {
            return;
        }

        $siteInfo = $this->parameterBag->get('site_info');
        $ads = $reservation->getAdsId();
        $notification = ConstantsHelper::getConstantName($reservation->getNotificationType(), 'NOTIFICATION', Reservation::class);

        $emailData = [];

        $emailData['toEmail'] = $ads->getContact()->getContactEmail();
        $replyTo = $reservation->getEmail();
        $emailData['replyTo'] = null !== $replyTo ? $replyTo : $siteInfo['site_email'];
        $emailData['replyToName'] = $reservation->getFirstname() . ' ' . $reservation->getLastname();
        $emailData['template'] = 'reservationEmail';
        $emailData['subject'] = 'Upit za smeštaj - '. $siteInfo['site_name'];
        $emailData['script'] = EmailConstants::USER_RESERVATION;
        $emailData['templateData']['reservation'] = $reservation;
        $emailData['templateData']['notification_type'] = 'reservation.'.$notification;
        $emailData['templateData']['ads'] = $ads;
        $emailData['templateData']['media'] = $this->mediaRepository->getMainImage($ads);

        $this->email->setAndSendEmail($emailData);
    }
}