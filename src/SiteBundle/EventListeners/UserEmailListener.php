<?php

namespace SiteBundle\EventListeners;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use SiteBundle\Constants\EmailConstants;
use SiteBundle\Entity\EntityInterface;
use SiteBundle\Entity\EntityStatusInterface;
use SiteBundle\Entity\User;
use SiteBundle\Helper\Email;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class UserEmailListener
{
    private $parameterBag;
    /**
     * @var Email
     */
    private $email;

    /**
     * ReservationEmailSubscriber constructor.
     *
     * @param ParameterBagInterface $parameterBag
     * @param Email                 $email
     */
    public function __construct(
        ParameterBagInterface $parameterBag,
        Email $email
    ) {
        $this->parameterBag = $parameterBag;
        $this->email = $email;
    }

    public function postPersist(LifecycleEventArgs $event)
    {
        $this->sendEmail($event);
    }

    public function postUpdate(LifecycleEventArgs $event)
    {
        $this->sendEmail($event);
    }

    private function sendEmail(LifecycleEventArgs $event)
    {
        $user = $event->getObject();

        if (!$user instanceof User) {
            return;
        }

        $siteInfo = $this->parameterBag->get('site_info');
        $role = $user->getRoles()[0];

        $emailData = [];

        $subject = 'Registracija na sajtu ';
        $template = 'user_registration';
        $script = EmailConstants::USER_REGISTRATION_SCRIPT;

        if (true === $user->getIsResetPasswordRequest()) {
            $subject = 'Reset lozinke na sajtu ';
            $template = 'user_reset_password';
            $script = EmailConstants::USER_RESET_PASSWORD;
            $emailData['templateData']['token'] = $user->getToken();
        }

        $emailData['fromEmail'] = $siteInfo['site_email'];
        $emailData['toEmail'] = $user->getEmail();
        $emailData['replyTo'] = $siteInfo['site_email'];
        $emailData['template'] = $template;
        $emailData['subject'] = $subject . $siteInfo['site_name'];
        $emailData['script'] = $script;
        $emailData['templateData']['user'] = $user;

        if ((EntityStatusInterface::STATUS_PENDING === $user->getStatus() || true === $user->getIsResetPasswordRequest()) && in_array($role, ['ROLE_USER', 'ROLE_ADVANCED_USER'])) {
            $this->email->setAndSendEmail($emailData);
        }
    }
}
