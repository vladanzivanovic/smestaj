<?php

namespace SiteBundle\Services\Ads;


use Doctrine\Common\Persistence\ObjectManager;
use SiteBundle\Entity\AdsAdditionalInfo;
use SiteBundle\Entity\Media;
use SiteBundle\Services\ImageService;
use SiteBundle\Services\ServiceContainer;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AdsAdditionalInfoImageService extends ServiceContainer
{
    protected $uploadAdsDir;
    protected $img;

    public function __construct(
        ObjectManager $entity,
        TokenStorageInterface $tokenStorage,
        ImageService $imageService,
        $uploadAdsDir
    ) {
        parent::__construct($entity, $tokenStorage);
        $this->img = $imageService;
        $this->uploadAdsDir = $uploadAdsDir;
    }

    public function setImage(AdsAdditionalInfo $additionalInfo, array $data)
    {
        if(empty(array_filter($data)))
            return false;

        $savePath = "{$this->uploadAdsDir}{$additionalInfo->getAdsid()->getId()}/additional";

        $this->img->setImageToFileSystem($data, $savePath, true);
        $i = 0;

        foreach ($data as $image) {
            $i++;
            if(isset($image['Id']) && !isset($image['isDeleted']))
                continue;

            if(isset($image['isDeleted']) && true === $image['isDeleted']) {
                $this->deleteImages($additionalInfo, $image);
                continue;
            }

            $mediaObj = new Media();

            $mediaObj->setAdsid($additionalInfo->getAdsid());
            $mediaObj->setSyscreatorid($this->token->getToken()->getUser());
            $mediaObj->setSyscreatedtime(new \DateTime());
            $mediaObj->setIsmain(0);
            $mediaObj->setName($image['FileName']);
            $mediaObj->setAdsinfoid($additionalInfo);

            $additionalInfo->addMedia($mediaObj);
        }
        return $additionalInfo;
    }

//    /**
//     * Delete image from array collection
//     * @param AdsAdditionalInfo $additionalInfo
//     * @param array $image
//     */
//    public function deleteImages(AdsAdditionalInfo $additionalInfo, $image)
//    {
//        /** @var Media $imageObj */
//        $imageObj = $this->em->getReference('SiteBundle:Media', $image['Id']);
//        $additionalInfo->getMedia()->removeElement($imageObj);
//
//        $file = "{$this->uploadAdsDir}{$additionalInfo->getAdsid()->getId()}/additional/{$image['FileName']}";
//        $this->img->deleteImageFromDir($file);
//    }
}