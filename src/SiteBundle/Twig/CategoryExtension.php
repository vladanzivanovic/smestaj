<?php

namespace SiteBundle\Twig;

use SiteBundle\Entity\Category;
use SiteBundle\Entity\EntityStatusInterface;
use SiteBundle\Repository\AdsRepository;
use SiteBundle\Repository\CategoryRepository;
use SiteBundle\Services\CategoryService;

class CategoryExtension extends \Twig_Extension
{
    private AdsRepository $adsRepository;

    private CategoryRepository $categoryRepository;

    public function __construct(
        CategoryRepository $categoryRepository,
        AdsRepository $adsRepository
    ) {
        $this->adsRepository = $adsRepository;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('categories', [$this, 'getCategories']),
            new \Twig_SimpleFunction('search_categories', [$this, 'getCategoriesForSearch']),
        ];
    }

    public function getCategories(): array
    {
        $categoryArray = [];
        $categoryIdArray = [];
        $categories = $this->categoryRepository->findBy(['parent' => null, 'status' => EntityStatusInterface::STATUS_ACTIVE]);

        /** @var Category $category */
        foreach ($categories as $category) {
            $tmpArray = [
                'id' => $category->getId(),
                'name' => $category->getName(),
                'alias' => $category->getAlias(),
                'image' => $category->getImage(),
                'children' => null,
            ];

            $children = $category->getChildCategory();
            $categoryIdArray[$category->getId()] = $category->getId();

            if (false === $children->isEmpty()) {
                /** @var Category $child */
                foreach ($children->getIterator() as $child) {
                    $tmpArray['children'][] = [
                        'id' => $child->getId(),
                        'name' => $child->getName(),
                        'alias' => $child->getAlias(),
                        'image' => $child->getImage(),
                    ];

                    $categoryIdArray[$child->getId()] = $child->getId();
                }
            }

            $categoryArray[] = $tmpArray;
        }

        $counted = $this->adsRepository->countByCategories(array_keys($categoryIdArray));
        $formattedCount = [];
        foreach ($counted as $count) {
            $formattedCount[$count['category_id']] = $count['total'];
        }

        return ['categories' => $categoryArray, 'counted' => $formattedCount];
    }

    public function getCategoriesForSearch(): array
    {
        return $this->categoryRepository->getAllActive();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'category_extension';
    }
}
