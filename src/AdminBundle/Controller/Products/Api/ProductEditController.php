<?php

declare(strict_types=1);

namespace AdminBundle\Controller\Products\Api;

use AdminBundle\Dto\Product\ProductSaveRequest;
use AdminBundle\Handler\ProductEditHandler;
use AdminBundle\Parser\RequestParserInterface;
use SiteBundle\Entity\Ads;
use SiteBundle\Entity\EntityStatusInterface;
use SiteBundle\Helper\ConstantsHelper;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

final class ProductEditController extends AbstractController
{
    public function __construct(
        #[Target('productEditRequestParser')] private readonly RequestParserInterface $requestParser,
        private readonly ProductEditHandler $editHandler,
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
    ) {
    }

    #[Route('/api/add-product', name: 'admin.add_product_api', methods: ['POST'], options: ['expose' => true])]
    public function insert(
        #[MapRequestPayload(acceptFormat: 'form')] ProductSaveRequest $dto,
    ): JsonResponse {
        if (false === $this->csrfTokenManager->isTokenValid(new CsrfToken('set_ad', $dto->csrfToken))) {
            throw $this->createAccessDeniedException();
        }

        $product = $this->requestParser->parse($dto);

        $this->editHandler->save($product);

        return $this->json(null, Response::HTTP_CREATED);
    }

    #[Route('/api/edit-product/{id}', name: 'admin.edit_product_api', methods: ['PUT'], options: ['expose' => true])]
    public function update(
        #[MapEntity(id: 'id')] Ads $ads,
        #[MapRequestPayload(acceptFormat: 'form')] ProductSaveRequest $dto,
    ): JsonResponse {
        if (false === $this->csrfTokenManager->isTokenValid(new CsrfToken('set_ad', $dto->csrfToken))) {
            throw $this->createAccessDeniedException();
        }

        $product = $this->requestParser->parse($dto, $ads);

        $this->editHandler->save($product);

        return $this->json(null, Response::HTTP_CREATED);
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
