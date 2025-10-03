<?php

namespace SiteBundle\Handler;


use Doctrine\ORM\EntityManager;
use SiteBundle\Constants\MessageConstants;
use SiteBundle\Entity\Ads;
use SiteBundle\Entity\Eventtype;
use SiteBundle\Exceptions\ApplicationException;
use SiteBundle\Services\ServiceContainer;
use SiteBundle\Services\UrlService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class EventTypeHandler extends ServiceContainer
{
    private $urlService;

    public function __construct(EntityManager $entity, TokenStorage $tokenStorage,  UrlService $urlService)
    {
        parent::__construct($entity, $tokenStorage);

        $this->urlService = $urlService;
    }

    /**
     * Insert or update eventType
     * @param array $data
     * @param null $id
     * @return bool|string
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    public function setEventType(array $data, $id = null)
    {
        if(empty(array_filter($data)))
            throw new BadRequestHttpException(MessageConstants::EMPTY_REQUEST);

        $this->em->beginTransaction();
        try{
            if(null === $id)
                $this->insertEventType($data);
            else
                $this->updateEventType($id, $data);

            $this->em->commit();
            return true;
        }catch (\Exception $exception){
            $this->em->rollback();
            return $exception->getMessage();
        }
    }

    /**
     * Delete EventType from Db
     * @param $id
     * @return bool
     * @throws \SiteBundle\Exceptions\ApplicationException
     */
    public function deleteEventType($id)
    {
        $eventType = $this->em->getRepository('SiteBundle:Eventtype')->find($id);

        if(null === $eventType)
            throw new ApplicationException(MessageConstants::NOT_FOUND);

        if($eventType->getReservations()->count() > 0)
            throw new ApplicationException(MessageConstants::EMPTY_REQUEST);
        else
            $this->removeData($eventType);

        return true;
    }

    /**
     * Insert new eventType
     * @param $data
     */
    private function insertEventType($data)
    {
        $eventTypeObj = new Eventtype();
        $eventTypeObj->setName($data['Name']);
        $eventTypeObj->setAlias($this->urlService->generateSeoUrl($data['Name']));
        $eventTypeObj->setSyscreatorid($this->token->getToken()->getUser());
        $eventTypeObj->setSyscreatedtime(new \DateTime());
        $eventTypeObj->setSysmodifierid($this->token->getToken()->getUser());
        $eventTypeObj->setSysmodifiedtime(new \DateTime());

        $this->insertData($eventTypeObj);
    }

    /**
     * Update EventType
     * @param $id
     * @param $data
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function updateEventType($id, $data)
    {
        $eventTypeObj = $this->em->getRepository('SiteBundle:Eventtype')->find($id);
        $eventTypeObj->setName($data['Name']);
        $eventTypeObj->setAlias($this->urlService->generateSeoUrl($data['Name']));
        $eventTypeObj->setSysmodifierid($this->token->getToken()->getUser());
        $eventTypeObj->setSysmodifiedtime(new \DateTime());

        $this->updateData($eventTypeObj);
    }
}