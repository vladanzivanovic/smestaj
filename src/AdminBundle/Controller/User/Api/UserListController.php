<?php

declare(strict_types=1);

namespace AdminBundle\Controller\User\Api;

use AdminBundle\Formatter\Datatable\UserDataTableResponseFormatter;
use AdminBundle\Parser\DataTableRequestParser;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use SiteBundle\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class UserListController extends AbstractController
{
    private DataTableRequestParser $requestParser;

    private UserDataTableResponseFormatter $responseFormatter;

    private UserRepository $userRepository;

    public function __construct(
        DataTableRequestParser $requestParser,
        UserRepository $userRepository,
        UserDataTableResponseFormatter $responseFormatter
    ) {
        $this->requestParser = $requestParser;
        $this->responseFormatter = $responseFormatter;
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/api/get-user-list", name="admin.get_user_list", methods={"POST"}, options={"expose": true})
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getList(Request $request)
    {
        $formattedRequest = $this->requestParser->formatRequest($request);
        $total = $this->userRepository->countData($formattedRequest);

        $data = $this->userRepository->getAdminList($formattedRequest);

        $response = $this->responseFormatter->formatResponse($formattedRequest, $data, (int)$total);

        return new JsonResponse($response);
    }
}
