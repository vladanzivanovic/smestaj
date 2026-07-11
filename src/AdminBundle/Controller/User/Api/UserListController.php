<?php

declare(strict_types=1);

namespace AdminBundle\Controller\User\Api;

use AdminBundle\Dto\Embedded\DataTableQueryDto;
use AdminBundle\Formatter\Datatable\UserDataTableResponseFormatter;
use AdminBundle\Parser\DataTableRequestParser;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use SiteBundle\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final class UserListController extends AbstractController
{
    public function __construct(
        private readonly DataTableRequestParser $requestParser,
        private readonly UserRepository $userRepository,
        private readonly UserDataTableResponseFormatter $responseFormatter,
    ) {
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    #[Route('/api/get-user-list', name: 'admin.get_user_list', methods: ['POST'], options: ['expose' => true])]
    public function getList(#[MapRequestPayload(acceptFormat: 'form')] DataTableQueryDto $dto): JsonResponse
    {
        $formattedRequest = $this->requestParser->parse($dto);
        $total = $this->userRepository->countData($formattedRequest);

        $data = $this->userRepository->getAdminList($formattedRequest);

        $response = $this->responseFormatter->formatResponse($formattedRequest, $data, (int) $total);

        return new JsonResponse($response);
    }
}
