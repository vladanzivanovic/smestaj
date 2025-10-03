<?php

declare(strict_types=1);

namespace SiteBundle\Collector;

use SiteBundle\Repository\CategoryRepository;
use SiteBundle\Repository\TagRepository;

final class DashboardCollector
{
    private TagRepository $tagRepository;
    private CategoryRepository $categoryRepository;

    public function __construct(
        TagRepository $tagRepository,
        CategoryRepository $categoryRepository
    ) {
        $this->tagRepository = $tagRepository;
        $this->categoryRepository = $categoryRepository;
    }
    public function collect(): array
    {
        return [
            'tags' => $this->tagRepository->getTags(),
            'categoryOptions' => $this->categoryRepository->getActiveForOptions(),
        ];
    }
}