<?php

namespace SiteBundle\Handler;


use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use SiteBundle\Constants\MessageConstants;
use SiteBundle\Entity\Ads;
use SiteBundle\Entity\Gendertoads;
use SiteBundle\Entity\Youtubeinfo;
use SiteBundle\Services\ServiceContainer;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class YoutubeHandler extends ServiceContainer
{
    public function __construct(
        ObjectManager $entity,
        TokenStorageInterface $tokenStorage
    ) {
        parent::__construct($entity, $tokenStorage);
    }

    /**
     * Set Youtubes array in ads object
     * @param Ads $ads
     * @param array $youtubes
     * @return Ads
     */
    public function setYoutube(Ads $ads, array $youtubes)
    {
        foreach ($youtubes as $youtube) {

            /** @var Youtubeinfo $youTubeObj */
            $youtube['AdsId'] = $ads;
            $youTubeObj = $this->arrayToEntity($youtube, 'SiteBundle:Youtubeinfo');

            if(isset($youtube['isDeleted']) && true === $youtube['isDeleted'])
                $ads->removeYoutube($youTubeObj);
            if(null === $youTubeObj->getId())
                $ads->addYoutube($youTubeObj);
        }
        return $ads;
    }
}