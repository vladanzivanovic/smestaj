<?php

declare(strict_types=1);

namespace AdminBundle\Controller\Products;

use AdminBundle\Formatter\ProductEditResponseFormatter;
use SiteBundle\Entity\Ads;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProductEditPageController extends AbstractController
{
    private ProductEditResponseFormatter $responseFormatter;

    public function __construct(
        ProductEditResponseFormatter $responseFormatter
    ) {
        $this->responseFormatter = $responseFormatter;
    }

    /**
     * @return Response
     */
    #[Route('/add-product', name: 'admin.add_product_page', methods: ['GET'])]
    public function insert(): Response
    {
        return $this->render('@Admin/Pages/productEdit.html.twig', $this->responseFormatter->formatResponse());
    }

    /**
     * @param Ads $product
     *
     * @return Response
     */
    #[Route('/edit-product/{id}', name: 'admin.edit_product_page', methods: ['GET'])]
    public function edit(Ads $product): Response
    {
        return $this->render('@Admin/Pages/productEdit.html.twig', $this->responseFormatter->formatResponse($product));
    }
}
