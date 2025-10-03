<?php

namespace SiteBundle\Handler;


use Doctrine\ORM\EntityManager;
use SiteBundle\Constants\MessageConstants;
use SiteBundle\Entity\Reservation;
use SiteBundle\Exceptions\ApplicationException;
use SiteBundle\Services\ServiceContainer;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class ReservationHandler extends ServiceContainer
{
    private UserReservationHandler $userHandler;

    public function __construct(EntityManager $entity, TokenStorage $tokenStorage, UserReservationHandler $userReservationHandler)
    {
        parent::__construct($entity, $tokenStorage);

        $this->userHandler = $userReservationHandler;
    }
    public function setReservation(array $data, $id = null)
    {
        if(empty(array_filter($data)))
            throw new BadRequestHttpException(MessageConstants::EMPTY_REQUEST);

        $this->em->beginTransaction();
        try{
            if(null === $id)
                $this->insertReservation($data);
            else
                $this->updateReservation($data, $id);

            $this->em->commit();
            return true;
        }catch (\Exception $exception){
            $this->em->rollback();
            return $exception->getMessage();
        }
    }

    /**
     * Delete Product from Db
     * @param $id
     * @return bool
     * @throws \SiteBundle\Exceptions\ApplicationException
     */
    public function deleteReservation($id)
    {
        $reservation = $this->em->getRepository('SiteBundle:Reservation')->find($id);

        if(null === $reservation)
            throw new ApplicationException(MessageConstants::NOT_FOUND);

        if($reservation->getReservations()->count() > 0){
            $reservation->setIsdeleted(1);
            $this->updateData($reservation);
        }else {
            $this->removeData($reservation);
        }

        return true;
    }

    private function insertReservation($data)
    {
        $resObj = $this->em->getRepository('SiteBundle:Reservation')->findOneBy([
            'adsid' => $data['AdsId'],
            'clientid' => $data['User']['Id'],
            'eventdate' => $data['EventDate'],
            'timefrom' => $data['TimeFrom'],
            'timeto' => $data['TimeTo']
        ]);

        if(null !== $resObj)
            throw new ApplicationException(MessageConstants::EXIST);

        /** @var Reservation $reservation */
        $reservation = $this->arrayToEntity($data, 'SiteBundle:Reservation');

        $this->userHandler->setUserReservation($data['User'], $data['User']['Id']);

//        $errors = $this->validator->validate($purchase);

        //dump(json_encode($errors)); exit;
//        if(count($errors) > 0)
//            throw new ValidationException($errors);

        $this->insertData($reservation);
    }

    private function updateReservation($data, $id)
    {
        $id = (int)$id;

        if( empty($id) )
            throw new \PDOException(MessageConstants::DATA_ID_NOT_EXIST);

        $reservationObj = $this->em->getRepository('SiteBundle:Reservation')->find($id);

        if(null === $reservationObj)
            throw new \PDOException(MessageConstants::DATA_ID_NOT_EXIST);

        $data['id'] = $id;

        /** @var Reservation $reservation */
        $reservation = $this->arrayToEntity($data, 'SiteBundle:Reservation');

        $this->userHandler->setUserReservation($data['User'], $data['User']['Id']);

//        $errors = $this->validator->validate($purchase);

        //dump(json_encode($errors)); exit;
//        if(count($errors) > 0)
//            throw new ValidationException($errors);

        $this->updateData($reservation);
    }
}