<?php

declare(strict_types=1);

namespace SiteBundle\Twig;

use SiteBundle\Entity\Category;
use Twig\Extension\AbstractExtension;
use Twig\TwigTest;

final class AppTestExtension extends AbstractExtension
{
    public function getTests(): array
    {
        return [
            new TwigTest('category', [$this, 'isCategory']),
        ];
    }

    public function isCategory($item): bool
    {
        return $item instanceof Category;
    }
}
