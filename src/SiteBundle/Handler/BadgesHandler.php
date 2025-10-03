<?php

namespace SiteBundle\Handler;


use SiteBundle\Services\ImageService;
use Doctrine\ORM\EntityManager;
use SiteBundle\Constants\MessageConstants;
use SiteBundle\Entity\Ads;
use SiteBundle\Entity\Badges;
use SiteBundle\Entity\Badgetoad;
use SiteBundle\Exceptions\ApplicationException;
use SiteBundle\Services\ServiceContainer;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Validator\Constraints\Date;

class BadgesHandler extends ServiceContainer
{
    protected $badgesDir;
    protected $img;

    public function __construct(EntityManager $entity, TokenStorage $tokenStorage, $badgesDir, ImageService $imageService)
    {
        parent::__construct($entity, $tokenStorage);

        $this->badgesDir = $badgesDir;
        $this->img = $imageService;
    }

    /**
     * Insert or update badge
     * @param array $data
     * @param null $id
     * @return bool|string
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    public function setBadge(array $data, $id = null)
    {
        if(empty(array_filter($data)))
            throw new BadRequestHttpException(MessageConstants::EMPTY_REQUEST);

        $this->em->beginTransaction();
        try{
            if(null === $id)
                $this->insertBadge($data);
            else
                $this->updateBadge($data, $id);

            $this->em->commit();
            return true;
        }catch (\Exception $exception){
            $this->em->rollback();
            return $exception->getMessage();
        }
    }

    /**
     * Assign badge to ad
     * @param Ads $ads
     * @param array $badges
     * @return Ads
     */
    public function badgesToAds(Ads $ads, array $badges)
    {
        $this->removeBadges($ads);
        foreach ($badges as $badge) {
            $badge = (int)$badge;

            if($badge == 0)
                continue;

            $badgeObj = $this->em->getRepository('SiteBundle:Badges')->find($badge);

            $badgeToAds = new Badgetoad();
            $badgeToAds->setAdsid($ads);
            $badgeToAds->setBadgeid($badgeObj);
            $badgeToAds->setSyscreatorid($this->token->getToken()->getUser());
            $badgeToAds->setSyscreatedtime(new \DateTime());
            $badgeToAds->setSysmodifierid($this->token->getToken()->getUser());
            $badgeToAds->setSysmodifiedtime(new \DateTime());

            $ads->addBadge($badgeToAds);
        }
        return $ads;
    }

    /**
     * Delete Badge from Db
     * @param $id
     * @return bool
     * @throws \SiteBundle\Exceptions\ApplicationException
     */
    public function deleteBadge($id)
    {
        /** @var Badges $badge */
        $badge = $this->em->getRepository('SiteBundle:Badges')->find($id);

        if(null === $badge)
            throw new ApplicationException(MessageConstants::NOT_FOUND);

        $this->img->deleteImageFromDir("{$this->badgesDir}/{$badge->getImage()}");

        if($badge->getBadgeToAds()->count() > 0){
            throw new ApplicationException(MessageConstants::RELATIONS_ERROR);
        }else {
            $this->removeData($badge);
        }

        return true;
    }
    /**
     * Insert Badge
     * @param $data
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function insertBadge($data)
    {
        $badge = $this->em->getRepository('SiteBundle:Badges')->checkExistanceByName($data['Name']);

        if(null !== $badge )
            throw new \PDOException(MessageConstants::EXIST);

        $this->img->setImageToFileSystem($data['Documents'], $this->badgesDir);
        /** @var Badges $badge */
        $badge = $this->arrayToEntity($data, 'SiteBundle:Badges');
        $badge->setImage($data['Documents'][0]['FileName']);

//        $errors = $this->validator->validate($purchase);

        //dump(json_encode($errors)); exit;
//        if(count($errors) > 0)
//            throw new ValidationException($errors);


        $this->insertData($badge);
    }

    /**
     * Update Badge
     * @param $data
     * @param $id
     * @throws \PDOException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    private function updateBadge($data, $id)
    {
        $id = (int)$id;

        if( empty($id) )
            throw new \PDOException(MessageConstants::BADGE_ID_NOT_EXIST);

        $badge = $this->em->getRepository('SiteBundle:Badges')->checkExistanceByName($data['Name'], $id);

        if(null !== $badge )
            throw new \PDOException(MessageConstants::EXIST);

        $badgeObj = $this->em->getRepository('SiteBundle:Badges')->find($id);

        if(null === $badgeObj)
            throw new \PDOException(MessageConstants::BADGE_ID_NOT_EXIST);

        $this->img->setImageToFileSystem($data['Documents'], $this->badgesDir);

        $data['id'] = $id;
        /** @var Badges $badge */
        $badge = $this->arrayToEntity($data, 'SiteBundle:Badges');
        $badge->setImage(count( $data['Documents']) > 1 ? $data['Documents'][1]['FileName'] : $data['Documents'][0]['FileName']);


//        $errors = $this->validator->validate($purchase);

        //dump(json_encode($errors)); exit;
//        if(count($errors) > 0)
//            throw new ValidationException($errors);

        $this->updateData($badge);
    }

    private function removeBadges(Ads $ads)
    {
        if(null !== $ads->getBadges() && $ads->getBadges()->count() > 0)
            $ads->getBadges()->clear();
    }
}