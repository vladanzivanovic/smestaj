<?php
/**
 * Created by PhpStorm.
 * User: vlada
 * Date: 7/23/2017
 * Time: 2:53 PM
 */

namespace SiteBundle\Handler;


use Doctrine\ORM\EntityManager;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use SiteBundle\Entity\User;
use SiteBundle\Entity\UserToSocialNetwork;
use SiteBundle\Services\ServiceContainer;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class UserToSocialHandler extends ServiceContainer
{
    public function checkAndSetUserSocialData(UserResponseInterface $userResponse, User $user)
    {
        $response = $userResponse->getResponse();
        $socialEntity = $this->em->getRepository(UserToSocialNetwork::class)->findOneBy(['socialid' => $response['id']]);

        if(null === $socialEntity) {
            $socialEntity = new UserToSocialNetwork();
            $socialEntity->setSocialId($response['id'])
                ->setType($userResponse->getResourceOwner()->getName())
                ->setUserid($user);

            if($userResponse->getResourceOwner()->getName() === UserToSocialNetwork::FACEBOOK_TYPE){
                $socialEntity->setImage($response['picture']['data']['url']);
            }

            $socialEntity = $this->insertData($socialEntity);
        }
        return $socialEntity;

    }
}