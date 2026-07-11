<?php

declare(strict_types=1);

namespace AdminBundle\Controller\Products\Api;

use AdminBundle\Dto\Embedded\DataTableQueryDto;
use AdminBundle\Formatter\Datatable\ProductDataTableResponseFormatter;
use AdminBundle\Parser\DataTableRequestParser;
use SiteBundle\Repository\AdsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final class ProductListController extends AbstractController
{
    public function __construct(
        private readonly DataTableRequestParser $dataTableRequestParser,
        private readonly ProductDataTableResponseFormatter $responseFormatter,
        private readonly AdsRepository $adsRepository,
    ) {
    }

    #[Route('/api/get-product-list', name: 'admin.get_product_list', methods: ['POST'], options: ['expose' => true])]
    public function getList(#[MapRequestPayload(acceptFormat: 'form')] DataTableQueryDto $dto): JsonResponse
    {
        $tableModel = $this->dataTableRequestParser->parse($dto);

        $rows = $this->adsRepository->getAdminList($tableModel);
        $total = $this->adsRepository->countData($tableModel);

        return new JsonResponse($this->responseFormatter->formatResponse($tableModel, $rows, (int) $total));
    }
}
