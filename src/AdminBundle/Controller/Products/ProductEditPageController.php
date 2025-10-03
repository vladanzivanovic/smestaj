<?php

declare(strict_types=1);

namespace AdminBundle\Controller\Products;

use AdminBundle\Formatter\ProductEditResponseFormatter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SiteBundle\Entity\Ads;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

final class ProductEditPageController extends AbstractController
{
    private ProductEditResponseFormatter $responseFormatter;

    public function __construct(
        ProductEditResponseFormatter $responseFormatter
    ) {
        $this->responseFormatter = $responseFormatter;
    }

    /**
     * @Route("/add-product", name="admin.add_product_page", methods={"GET"})
     * @Template("@Admin/Pages/productEdit.html.twig")
     *
     * @return array
     */
    public function insert(): array
    {
        return $this->responseFormatter->formatResponse();
    }

    /**
     * @Route("/edit-product/{id}", name="admin.edit_product_page", methods={"GET"})
     * @Template("@Admin/Pages/productEdit.html.twig")
     *
     * @param Ads $product
     *
     * @return array
     */
    public function edit(Ads $product)
    {
        return $this->responseFormatter->formatResponse($product);
    }
}
