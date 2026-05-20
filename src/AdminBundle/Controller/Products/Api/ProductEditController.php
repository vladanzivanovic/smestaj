<?php

declare(strict_types=1);

namespace AdminBundle\Controller\Products\Api;

use AdminBundle\Handler\ProductEditHandler;
use SiteBundle\Entity\Ads;
use SiteBundle\Entity\EntityInterface;
use SiteBundle\Entity\EntityStatusInterface;
use SiteBundle\Helper\ConstantsHelper;
use AdminBundle\Parser\RequestParserInterface;
use SiteBundle\Parser\AdsEditParser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProductEditController extends AbstractController
{

    private RequestParserInterface $requestParser;

    private ProductEditHandler $editHandler;

    public function __construct(
        RequestParserInterface $productEditRequestParser,
        ProductEditHandler $editHandler
    ) {
        $this->requestParser = $productEditRequestParser;
        $this->editHandler = $editHandler;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \Doctrine\ORM\ORMException
     */
    #[Route('/api/add-product', name: 'admin.add_product_api', methods: ['POST'], options: ['expose' => true])]
    public function insert(Request $request): JsonResponse
    {
        if (false === $this->isCsrfTokenValid('set_ad', $request->request->get('_csrf_token'))) {
            $this->createAccessDeniedException();
        }

        $product = $this->requestParser->parse($request->request);

        $this->editHandler->save($product);

        return $this->json(null, Response::HTTP_CREATED);
    }

    /**
     * @param Request $request
     * @param Ads     $ads
     *
     * @return JsonResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    #[Route('/api/edit-product/{id}', name: 'admin.edit_product_api', methods: ['PUT'], options: ['expose' => true])]
    public function update(Request $request, Ads $ads): JsonResponse
    {
        if (false === $this->isCsrfTokenValid('set_ad', $request->request->get('_csrf_token'))) {
            $this->createAccessDeniedException();
        }

        try {
            $product = $this->requestParser->parse($request->request, $ads);

            $this->editHandler->save($product);

            return $this->json(null, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @param Ads $ads
     * @param int $status
     *
     * @return JsonResponse
     */
    #[Route('/api/product-change-status/{id}/{status}', name: 'admin.api_product_change_status', methods: ['PATCH'], options: ['expose' => true])]
    public function changeStatus(Ads $ads, int $status): JsonResponse
    {
        $this->editHandler->changeStatus($ads, $status);

        $statusText = ConstantsHelper::getConstantName((string) $status, 'STATUS', EntityStatusInterface::class);

        return $this->json(['text' => $statusText]);
    }
}
