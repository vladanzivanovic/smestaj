<?php

namespace SiteBundle\Handler;


use Doctrine\Common\Persistence\ObjectManager;
use SiteBundle\Services\Ads\AdsAdditionalInfoImageService;
use Doctrine\ORM\EntityManager;
use SiteBundle\Entity\Ads;
use SiteBundle\Entity\AdsAdditionalInfo;
use SiteBundle\Services\ServiceContainer;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AdsAdditionalInfoHandler extends ServiceContainer
{
    /**
     * @var AdsAdditionalInfoImageService
     */
    private $imageService;

    /**
     * AdsAdditionalInfoHandler constructor.
     *
     * @param ObjectManager                 $entity
     * @param TokenStorageInterface         $tokenStorage
     * @param AdsAdditionalInfoImageService $imageService
     */
    public function __construct(
        ObjectManager $entity,
        TokenStorageInterface $tokenStorage,
        AdsAdditionalInfoImageService $imageService
    ) {
        parent::__construct($entity, $tokenStorage);
        $this->imageService = $imageService;
    }

    public function setAdditionalInfo(Ads $ads, array $data)
    {
        foreach ($data as $key => $info) {
            $info['AdsId'] = $ads->getId();
            $info['Id'] = $info['InfoId'];

            /** @var AdsAdditionalInfo $infoObj */
            $infoObj = $this->arrayToEntity($info, 'SiteBundle:AdsAdditionalInfo');

            if (isset($info['isDeleted']) && true === $info['isDeleted']) {
                $ads->removeAdsadditionalinfo($infoObj);
            }
            if (isset($info['isModify']) && true === $info['isModify']) {
                $infoObj = $this->imageService->setImage($infoObj, json_decode($info['Documents'], true));
            }
            if (null === $infoObj->getId()) {
                $infoObj = $this->imageService->setImage($infoObj, json_decode($info['Documents'], true));
                $ads->addAdsadditionalinfo($infoObj);
            }
        }

        return $ads;
    }
}