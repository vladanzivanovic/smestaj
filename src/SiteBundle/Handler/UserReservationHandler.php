<?php

namespace SiteBundle\Handler;

use Doctrine\Common\Persistence\ObjectManager;
use SiteBundle\Entity\Reservation;
use SiteBundle\Exceptions\ApplicationException;
use SiteBundle\Formatter\ReservationEmailFormatter;
use SiteBundle\Services\ServiceContainer;
use SiteBundle\Services\UserService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserReservationHandler extends ServiceContainer
{
    private $userService;
    private $userHandler;
    private $emailFormatter;
    private $parameterBag;

    /**
     * UserReservationHandler constructor.
     *
     * @param UserService               $userService
     * @param UserHandler               $userHandler
     * @param ReservationEmailFormatter $emailFormatter
     * @param ParameterBagInterface     $parameterBag
     */
    public function __construct(
        ObjectManager $objectManager,
        TokenStorageInterface $tokenStorage,
        UserService $userService,
        UserHandler $userHandler,
        ReservationEmailFormatter $emailFormatter,
        ParameterBagInterface $parameterBag
    ) {
        parent::__construct($objectManager, $tokenStorage);

        $this->userService = $userService;
        $this->userHandler = $userHandler;
        $this->emailFormatter = $emailFormatter;
        $this->parameterBag = $parameterBag;
    }

    /**
     * @throws ApplicationException
     */
    public function setReservation(array $data): Reservation
    {
        try{
            /** @var Reservation $reservation */
            $reservation = $this->arrayToEntity($data, Reservation::class);

            $this->insertData($reservation);

            return $reservation;
        }catch (\Exception $exception){
            throw new ApplicationException($exception->getMessage());
        }
    }
}
