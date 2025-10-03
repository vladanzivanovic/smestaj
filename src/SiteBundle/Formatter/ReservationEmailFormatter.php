<?php

namespace SiteBundle\Formatter;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use SiteBundle\Constants\EmailConstants;
use SiteBundle\Entity\Reservation;
use SiteBundle\Helper\RandomCodeGenerator;
use SiteBundle\Repository\MediaRepository;
use SiteBundle\Services\ServiceContainer;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class ReservationEmailFormatter extends ServiceContainer
{
    private $codeGenerator;
    private $parameterBag;

    /**
     * UserReservationHandler constructor.
     *
     * @param RandomCodeGenerator   $codeGenerator
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(
        RandomCodeGenerator $codeGenerator,
        ParameterBagInterface $parameterBag
    ) {
        $this->codeGenerator = $codeGenerator;
        $this->parameterBag = $parameterBag;
    }

    public function prepareDataForEmailToOwner(array &$emailData, Reservation $reservation)
    {
        $ads = $reservation->getAdsId();
        /** @var MediaRepository $mediaRepo */
        $mediaRepo = $this->em->getRepository('SiteBundle:Media');

        $emailData['fromEmail'] = $reservation->getEmail();
        $emailData['toEmail'] = $ads->getContact()->getContactEmail();
        $emailData['replyTo'] = $reservation->getEmail();
        $emailData['template'] = 'reservationEmail';
        $emailData['subject'] = 'Upit za smeštaj - '. $this->parameterBag->get('site_info')['site_name'];
        $emailData['script'] = EmailConstants::USER_RESERVATION;
        $emailData['templateData']['reservation'] = $reservation;
        $emailData['templateData']['ads'] = $ads;
        $emailData['templateData']['media'] = $mediaRepo->getMainImage($ads);
        $emailData['templateData']['code'] = $this->codeGenerator->random();
    }
}