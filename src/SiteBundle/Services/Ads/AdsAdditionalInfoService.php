<?php


namespace SiteBundle\Services\Ads;


use Doctrine\Common\Persistence\ObjectManager;
use SiteBundle\Repository\AdsAdditionalInfoRepository;
use SiteBundle\Repository\MediaRepository;
use SiteBundle\Services\ServiceContainer;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AdsAdditionalInfoService extends ServiceContainer
{
    private $upload_image_dir;
    private $infoRepository;
    private $mediaRepository;

    /**
     * AdsAdditionalInfoService constructor.
     *
     * @param ObjectManager               $entity
     * @param TokenStorageInterface       $tokenStorage
     * @param array                       $uploadAdsDir
     * @param AdsAdditionalInfoRepository $infoRepository
     * @param MediaRepository             $mediaRepository
     */
    public function __construct(
        ObjectManager $entity,
        TokenStorageInterface $tokenStorage,
        $uploadAdsDir,
        AdsAdditionalInfoRepository $infoRepository,
        MediaRepository $mediaRepository
    ) {
        parent::__construct($entity, $tokenStorage);
        $this->upload_image_dir = $uploadAdsDir;
        $this->infoRepository = $infoRepository;
        $this->mediaRepository = $mediaRepository;
    }

    /**
     * @param int $adsId
     * @return array
     */
    public function getByAdsId($adsId)
    {
        $adsInfo = $this->infoRepository->getByAdsId($adsId);

        foreach ($adsInfo as &$info) {
            $info['Media'] = $this->mediaRepository->getByAdsInfoId($info['InfoId'], $adsId, $this->upload_image_dir);
        }

        return $adsInfo;
    }
}