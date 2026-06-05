<?php

namespace SiteBundle\Controller\Api\User;

use SiteBundle\Constants\MessageConstants;
use SiteBundle\Controller\SiteController;
use SiteBundle\Entity\User;
use SiteBundle\Exceptions\ApplicationException;
use SiteBundle\Repository\AdsRepository;
use SiteBundle\Services\UserService;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class UserGetController extends SiteController
{
    private $userService;

    /**
     * UserGetController constructor.
     *
     * @param UserService $userService
     */
    public function __construct(
        UserService $userService
    ) {
        $this->userService = $userService;
    }

    /**
     * @param User $user
     * @return JsonResponse
     */
    public function getUserByEmailAction(#[MapEntity(mapping: ['email' => 'email'])] User $user)
    {
        return $this->jsonResponse->setData([ 'success' => true, 'data' => $this->objToArray($user)]);
    }
}
