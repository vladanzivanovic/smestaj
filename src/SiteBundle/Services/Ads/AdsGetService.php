<?php

namespace SiteBundle\Services\Ads;

use Doctrine\ORM\EntityManager;
use SiteBundle\Entity\Ads;
use SiteBundle\Entity\Media;
use SiteBundle\Entity\Youtubeinfo;
use SiteBundle\Helper\AppHelper;
use SiteBundle\Services\ServiceContainer;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class AdsGetService extends ServiceContainer
{
    private $appHelper;
    private $uploadAdsDir;

    public function __construct(
        EntityManager $entity,
        TokenStorage $tokenStorage,
        AppHelper $appHelper,
        $uploadAdsDir
    ){
        parent::__construct($entity, $tokenStorage);
        $this->appHelper = $appHelper;
        $this->uploadAdsDir = $uploadAdsDir;
    }

    public function getAdsForEdit(Ads $ads)
    {
        $adsInfo = $this->setService('site.additional_info_service')->getByAdsId($ads['AdsId']);

        return $this->jsonResponse->setData([
            'ads' => $ads,
            'media' => $this->getMedia($ads),
            'youtube' => $this->getYoutube($ads),
            'user' => $this->getContact($ads),
            'additionalInfo' => $adsInfo,
//            'tags' => $tags,
        ]);
    }

    private function getYoutube(Ads $ads)
    {
        $youtubes = $ads->getYoutube();

        $youTubeArray = [];

        /** @var Youtubeinfo $item */
        foreach ($youtubes->getIterator() as $item) {
            $youTubeArray[] = [
                'Id' => $item->getId(),
                'AdsId' => $ads->getId(),
                'Title' => $item->getTitle(),
                'YouTubeId' => $item->getYoutubeid(),
                'ChannelId' => $item->getChannelid(),
                'ChanelTitle' => $item->getChaneltitle(),
                'Thumbnails' => $item->getThumbnails()
            ];
        }

        return $youTubeArray;
    }

    private function getContact(Ads $ads)
    {
        $user = $ads->getContact();

        return [
            'Id' => $user->getId(),
            'Email' => $user->getEmail(),
            'FirstName' => $user->getFirstname(),
            'LastName' => $user->getLastname(),
            'Address' => $user->getAddress(),
            'Telephone' => $user->getTelephone(),
            'MobilePhone' => $user->getMobilePhone(),
            'Viber' => $user->getViber(),
            'Facebook' => $user->getFacebook(),
            'Website' => $user->getWebsite(),
            'ContactEmail' => $user->getContactEmail(),
            'CityName' => $user->getCityid()->getName()
        ];
    }

    private function getMedia(Ads $ads)
    {
        $media = $ads->getMedia();
        $formattedMedia = [];

        $imageUrl = $this->appHelper->getHttpHostBaseUrl() . $this->uploadAdsDir;

        /** @var Media $item */
        foreach ($media->getIterator() as $item) {
            $formattedMedia[] = [
                'Id' => $item->getId(),
                'AdsId' => $ads->getId(),
                'Name' => $item->getName(),
                'IsMain' => $item->getIsmain(),
                'AdsInfoId' => $item->getAdsinfoid(),
                'ImageUrl' => $imageUrl.$ads->getId().'/'.$item->getName(),
                'ThumbUrl' => $imageUrl.$ads->getId().'/thumb/'.$item->getName()
            ];
        }

        return $formattedMedia;
    }
}