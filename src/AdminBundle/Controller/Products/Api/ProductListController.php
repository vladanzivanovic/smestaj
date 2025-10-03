<?php

declare(strict_types=1);

namespace AdminBundle\Controller\Products\Api;

use AdminBundle\Formatter\Datatable\ProductDataTableResponseFormatter;
use AdminBundle\Parser\DataTableRequestParser;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use SiteBundle\Repository\AdsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class ProductListController extends AbstractController
{
    private DataTableRequestParser $requestParser;

    private ProductDataTableResponseFormatter $responseFormatter;

    private AdsRepository $adsRepository;

    public function __construct(
        DataTableRequestParser $requestParser,
        ProductDataTableResponseFormatter $responseFormatter,
        AdsRepository $adsRepository
    ) {
        $this->requestParser = $requestParser;
        $this->responseFormatter = $responseFormatter;
        $this->adsRepository = $adsRepository;
    }

    /**
     * @Route("/api/get-product-list", name="admin.get_product_list", methods={"POST"}, options={"expose": true})
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
        $total = $this->adsRepository->countData($formattedRequest);

        $data = $this->adsRepository->getAdminList($formattedRequest);

        $response = $this->responseFormatter->formatResponse($formattedRequest, $data, (int)$total);

        return new JsonResponse($response);
    }
}
