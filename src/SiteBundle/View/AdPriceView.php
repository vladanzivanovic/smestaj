<?php

declare(strict_types=1);

namespace SiteBundle\View;

class AdPriceView
{
    public function view(int $fromPrice, int $toPrice): array
    {
        $view = [
            'from' => $fromPrice,
            'to' => $toPrice,
        ];

        return $view;
    }
}
