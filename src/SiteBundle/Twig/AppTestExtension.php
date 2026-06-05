<?php

namespace SiteBundle\Twig;

use SiteBundle\Entity\Category;
use Twig\Extension\AbstractExtension;
use Twig\TwigTest;

class AppTestExtension extends AbstractExtension
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
