<?php

declare(strict_types=1);

namespace SiteBundle\View;

use SiteBundle\Entity\Category;

final class CategoryView
{
    public function view(Category $category): array
    {
        $view = [
            'id' => $category->getId(),
            'title' => $category->getName(),
            'slug' => $category->getAlias(),
        ];

        return $view;
    }
}
