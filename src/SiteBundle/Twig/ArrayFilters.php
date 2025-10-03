<?php

declare(strict_types=1);

namespace SiteBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

final class ArrayFilters extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('unset', [$this, 'removeFromArray']),
        ];
    }

    public function removeFromArray($array, $key)
    {
        unset($array[$key]);

        return $array;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'array_filters';
    }
}