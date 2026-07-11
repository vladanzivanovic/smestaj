<?php

declare(strict_types=1);

namespace SiteBundle\Twig;

use SiteBundle\Entity\Category;
use SiteBundle\Entity\EntityStatusInterface;
use SiteBundle\Repository\AdsRepository;
use SiteBundle\Repository\CategoryRepository;
use SiteBundle\Services\CategoryService;

final class CategoryExtension extends \Twig\Extension\AbstractExtension
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
    public function getFunctions(): array
    {
        return [
            new \Twig\TwigFunction('categories', [$this, 'getCategories']),
            new \Twig\TwigFunction('search_categories', [$this, 'getCategoriesForSearch']),
            new \Twig\TwigFunction('default_category', [$this, 'getDefaultCategory']),
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

    public function getDefaultCategory(): Category
    {
        return $this->categoryRepository->findOneBy(['parent' => null, 'status' => EntityStatusInterface::STATUS_ACTIVE, 'alias' => 'sobe-apartmani']);
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
