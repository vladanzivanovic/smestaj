<?php
/**
 * Created by PhpStorm.
 * User: vlada
 * Date: 7/2/2017
 * Time: 3:18 PM
 */

namespace SiteBundle\Services;


use Doctrine\ORM\EntityManager;
use SiteBundle\Constants\EmailConstants;
use SiteBundle\Helper\Email;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class ContactService extends ServiceContainer
{
    private $siteInfo;
    private $email;

    public function __construct(EntityManager $entity, TokenStorage $tokenStorage, $siteInfo, Email $email)
    {
        parent::__construct($entity, $tokenStorage);
        $this->siteInfo = $siteInfo;
        $this->email = $email;
    }

    public function sendContactEmail(array $data)
    {
        $data['fromEmail'] = $data['email'];
        $data['toEmail'] = $this->siteInfo['site_email'];
        $data['subject'] = isset($data['subject']) ? $data['subject'] : 'Poruka od ' . $data['fullName'];
        $data['template'] = 'contact_us';
        $data['script'] = EmailConstants::CONTACT_US;

        $this->email->setAndSendEmail($data);

        return true;
    }
}