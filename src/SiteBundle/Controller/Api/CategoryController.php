<?php

namespace SiteBundle\Controller\Api;

use SiteBundle\Controller\SiteController;
use SiteBundle\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends SiteController
{
    private CategoryRepository $categoryRepository;

    public function __construct(
        CategoryRepository $categoryRepository
    ) {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @Route("/api/category-all", name="site_categories", methods={"GET"})
     * @return JsonResponse
     */
    public function getCategoryLists()
    {
        return $this->json($this->categoryRepository->getAllActive());
    }
}
