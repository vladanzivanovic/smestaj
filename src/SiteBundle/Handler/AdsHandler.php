<?php

namespace SiteBundle\Handler;

use Doctrine\Common\Persistence\ObjectManager;
use SiteBundle\Entity\EntityStatusInterface;
use SiteBundle\Entity\Media;
use SiteBundle\Helper\ValidatorHelper;
use SiteBundle\Repository\AdsRepository;
use SiteBundle\Services\Ads\AdsImageService;
use SiteBundle\Entity\Ads;
use SiteBundle\Services\ServiceContainer;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AdsHandler extends ServiceContainer
{
    private AdsImageService $adsImage;

    private AdsRepository $adsRepository;

    private ValidatorHelper $validator;

    public function __construct(
        ObjectManager $entity,
        TokenStorageInterface $tokenStorage,
        AdsImageService $adsImageService,
        AdsRepository $adsRepository,
        ValidatorHelper $validatorHelper
    ) {
        parent::__construct($entity, $tokenStorage);

        $this->adsImage = $adsImageService;
        $this->adsRepository = $adsRepository;
        $this->validator = $validatorHelper;
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     */
    public function save(Ads $ads)
    {
        $errors = $this->validator->validate($ads, null, "SetAd");

        if ($errors->count() > 0) {
            throw new UnprocessableEntityHttpException(json_encode($this->validator->parseErrors($errors)));
        }

        if (null == $ads->getId()) {
            $this->adsRepository->persist($ads);
        }

        $this->adsRepository->flush();
    }

    public function deleteAds(Ads $ads): void
    {
        if($ads->getReservations()->count() > 0){
            $ads->setStatus(EntityStatusInterface::STATUS_ARCHIVED);
            $this->adsRepository->flush();

            return;
        }

        /** @var Media $image */
        foreach ($ads->getMedia() as $image) {
            $this->adsImage->markImageAsDeleted($image);
        }

        $this->adsRepository->removeWithFlush($ads);
    }
}
