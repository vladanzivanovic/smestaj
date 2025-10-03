<?php

namespace SiteBundle\EventListeners;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use SiteBundle\Constants\EmailConstants;
use SiteBundle\Entity\Ads;
use SiteBundle\Entity\Media;
use SiteBundle\Entity\User;
use SiteBundle\Helper\Email;
use SiteBundle\Services\ImageService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AdsSetListener
{
    private $imageService;
    private $uploadAdsDir;
    private $email;
    private $parameterBag;

    /**
     * @param ImageService          $imageService
     * @param string                $uploadAdsDir
     * @param Email                 $email
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(
        ImageService $imageService,
        $uploadAdsDir,
        Email $email,
        ParameterBagInterface $parameterBag
    ) {
        $this->imageService = $imageService;
        $this->uploadAdsDir = $uploadAdsDir;
        $this->email = $email;
        $this->parameterBag = $parameterBag;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        $this->sendEmail($entity, true);
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        $this->sendEmail($entity, false);
    }

    private function sendEmail($entity, bool $isNew)
    {
        if (!$entity instanceof Ads || false === $entity->getSendEmail()) {
            return ;
        }

        $data = $this->prepareDataForEmail($entity, $isNew);
        $this->email->setAndSendEmail($data);
    }

    private function prepareDataForEmail(Ads $ads, bool $isNew): array
    {
        $subjectPart = false === $isNew ? 'Izmena oglasa - ' : 'Aktivacija oglasa - ';
        /** @var User $user */
        $user = $ads->getContact();
        $siteInfo = $this->parameterBag->get('site_info');

        $category = $ads->getCategoryId();

        $data['replyTo'] = $user->getContactEmail();
        $data['replyToName'] = $user->getFirstname().' '.$user->getLastname();
        $data['toEmail'] = $siteInfo['site_email'];
        $data['toEmailName'] = $siteInfo['site_name'];
        $data['subject'] = $subjectPart . $ads->getTitle();
        $data['template'] = false === $isNew ? 'user_update_ad' : 'user_set_ad';
        $data['userId'] = $user->getId();
        $data['script'] = false === $isNew ? EmailConstants::USER_EDIT_AD : EmailConstants::USER_ADD_AD;
        $data['user'] = $user;
        $data['templateData']['Title'] = $ads->getTitle();
        $data['templateData']['CategoryName'] = $category->getName();
        $data['templateData']['CategoryAlias'] = $category->getAlias();
        $data['templateData']['CityName'] = $ads->getCityId()->getName();
        $data['templateData']['PriceFrom'] = $ads->getPriceFrom();
        $data['templateData']['PriceTo'] = $ads->getPriceTo();
        $data['templateData']['Alias'] = $ads->getAlias();

        return $data;
    }
}