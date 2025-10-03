<?php

namespace SiteBundle\Handler;


use Doctrine\ORM\EntityManager;
use SiteBundle\Constants\MessageConstants;
use SiteBundle\Entity\Ads;
use SiteBundle\Exceptions\ApplicationException;
use SiteBundle\Services\ServiceContainer;
use SiteBundle\Services\UrlService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class GenderHandler extends ServiceContainer
{
    private $urlService;

    public function __construct(EntityManager $entity, TokenStorage $tokenStorage,  UrlService $urlService)
    {
        parent::__construct($entity, $tokenStorage);

        $this->urlService = $urlService;
    }

    /**
     * Insert or update gender
     * @param array $data
     * @param null $id
     * @return bool|string
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    public function setGender(array $data, $id = null)
    {
        if(empty(array_filter($data)))
            throw new BadRequestHttpException(MessageConstants::EMPTY_REQUEST);

        $this->em->beginTransaction();
        try{
            if(null === $id)
                $this->insertGender($data);
            else
                $this->updateGender($id, $data);

            $this->em->commit();
            return true;
        }catch (\Exception $exception){
            $this->em->rollback();
            return $exception->getMessage();
        }
    }

    /**
     * Delete Gender from Db
     * @param $id
     * @return bool
     * @throws \SiteBundle\Exceptions\ApplicationException
     */
    public function deleteGender($id)
    {
        $gender = $this->em->getRepository('SiteBundle:Genders')->find($id);

        if(null === $gender)
            throw new ApplicationException(MessageConstants::NOT_FOUND);

        if($gender->getGenderstoads()->count() > 0)
            throw new ApplicationException(MessageConstants::EMPTY_REQUEST);
        else
            $this->removeData($gender);

        return true;
    }

    public function genderToAds(Ads $ads, array $genders)
    {
        $this->removeGenders($ads);
        foreach ($genders as $gender) {
            $gender = (int)$gender;
            if($gender == 0)
                continue;

            $genderObj = $this->em->getRepository('SiteBundle:Genders')->find($gender);

            $genderToAds = new Gendertoads();
            $genderToAds->setAdsid($ads);
            $genderToAds->setGenderid($genderObj);

            $ads->addGenderId($genderToAds);
        }
        return $ads;
    }

    /**
     * Insert new gender
     * @param $data
     */
    private function insertGender($data)
    {
        $genderObj = new Genders();
        $genderObj->setName($data['Name']);
        $genderObj->setAlias($this->urlService->generateSeoUrl($data['Name']));
        $genderObj->setSyscreatorid($this->token->getToken()->getUser());
        $genderObj->setSyscreateddatetime(new \DateTime());
        $genderObj->setSysmodifyid($this->token->getToken()->getUser());
        $genderObj->setSysmodifydatetame(new \DateTime());

        $this->insertData($genderObj);
    }

    /**
     * Update Gender
     * @param $id
     * @param $data
     */
    private function updateGender($id, $data)
    {
        $category = $this->em->getRepository('SiteBundle:Category')->find($data['CategoryId']);
        $genderObj = $this->em->getRepository('SiteBundle:Genders')->find($id);
        $genderObj->setName($data['Name'])
            ->setAlias($this->urlService->generateSeoUrl($data['Name']))
            ->setSysmodifyid($this->token->getToken()->getUser())
            ->setSysmodifydatetame(new \DateTime())
            ->setCategory($category);

        $this->updateData($genderObj);
    }

    private function removeGenders(Ads $ads)
    {
        if($ads->getGenderIds()->count() > 0)
            $ads->getGenderIds()->clear();
    }
}