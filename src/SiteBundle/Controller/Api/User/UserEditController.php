<?php

namespace SiteBundle\Controller\Api\User;


use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SiteBundle\Constants\MessageConstants;
use SiteBundle\Controller\SiteController;
use SiteBundle\Entity\User;
use SiteBundle\Handler\UserHandler;
use SiteBundle\Services\UserService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserEditController extends SiteController
{
    private UserHandler $userHandler;

    private LoggerInterface $logger;

    public function __construct(
        UserHandler $userHandler,
        LoggerInterface $logger
    ) {
        $this->userHandler = $userHandler;
        $this->logger = $logger;
    }

    /**
     * @Route("/api/add-new-user", name="site_registration_post", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function addNewUser(Request $request)
    {
        try {
            if (!($data = $this->requestToArray($request))) {
                throw new \Exception('Unable to register user');
            }

            $userResponse = $this->userHandler->insertUser($data);

            if (is_array($userResponse)) {
                return $this->json($userResponse, Response::HTTP_BAD_REQUEST);
            }

//            if (isset($data['facebookId'])) {
//                $userResponse['facebookId'] = $data['facebookId'];
//            }

            return $this->json($userResponse);
        }catch (\Throwable $throwable) {
            $this->logger->error(
                'Unable to register user',
                [
                    'request' => $request,
                    'exception' => $throwable
                ]
            );

            return $this->json(['msg' => MessageConstants::EMPTY_REQUEST], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @param $id
     * @param Request $request
     * @return JsonResponse
     */
    public function updateUserAction($id, Request $request)
    {
        if(!($data = $this->requestToArray($request)))
            return $this->jsonResponse->setData([ 'success' => false, 'msg' => MessageConstants::EMPTY_REQUEST ]);

        /** @var UserService $userService */
        $userService = $this->setService('site.user_service');
        $userResponse = $userService->setUpUser($data, $id);
        $userResponse['id'] = $id;

        return $this->jsonResponse->setData($userResponse);
    }

    /**
     * @Route("/aktivacija-naloga/{id}", name="site_activate_registration", methods={"GET"}, requirements={"id": "\d+"})
     * @Template("@Site/Site/accountActivation.html.twig")
     * @param User $user
     * @return array
     */
    public function activateRegistration(User $user)
    {
        return [
            'userActivation' => $this->userHandler->activateRegistration($user)
        ];
    }
}
