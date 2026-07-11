<?php

declare(strict_types=1);

namespace AdminBundle\Controller\InfoPages\Api;

use AdminBundle\Dto\Embedded\DataTableQueryDto;
use AdminBundle\Formatter\Datatable\InfoPageDataTableResponseFormatter;
use AdminBundle\Parser\DataTableRequestParser;
use SiteBundle\Repository\AdsInfoPageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final class InfoPageListController extends AbstractController
{
    public function __construct(
        private readonly DataTableRequestParser $dataTableRequestParser,
        private readonly AdsInfoPageRepository $adsInfoPageRepository,
        private readonly InfoPageDataTableResponseFormatter $responseFormatter
    ) {
    }

    #[Route('/api/info-pages/list', name: 'admin.api.info_pages.list', methods: ['POST'], options: ['expose' => true])]
    public function list(#[MapRequestPayload(acceptFormat: 'form')] DataTableQueryDto $dto): JsonResponse
    {
        $tableModel = $this->dataTableRequestParser->parse($dto);

        $rows = $this->adsInfoPageRepository->getAdminList($tableModel);
        $total = $this->adsInfoPageRepository->countData($tableModel);

        return new JsonResponse($this->responseFormatter->formatResponse($tableModel, $rows, $total));
    }
}
