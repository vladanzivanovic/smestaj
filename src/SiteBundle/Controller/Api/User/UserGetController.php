<?php

namespace SiteBundle\Controller\Api\User;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use SiteBundle\Constants\MessageConstants;
use SiteBundle\Controller\SiteController;
use SiteBundle\Entity\User;
use SiteBundle\Exceptions\ApplicationException;
use SiteBundle\Repository\AdsRepository;
use SiteBundle\Services\UserService;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;

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
    public function getUserByEmailAction(User $user)
    {
        return $this->jsonResponse->setData([ 'success' => true, 'data' => $this->objToArray($user)]);
    }
}
