<?php

declare(strict_types=1);

namespace SiteBundle\Twig;

use Detection\MobileDetect;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class MobileDetectExtension extends AbstractExtension
{
    private MobileDetect $detector;

    public function __construct()
    {
        $this->detector = new MobileDetect();
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_mobile', [$this, 'isMobile']),
            new TwigFunction('is_tablet', [$this, 'isTablet']),
            new TwigFunction('is_mobile_view', [$this, 'isMobile']),
            new TwigFunction('is_tablet_view', [$this, 'isTablet']),
            new TwigFunction('is_full_view', [$this, 'isFullView']),
        ];
    }

    public function isMobile(): bool
    {
        return $this->detector->isMobile() && !$this->detector->isTablet();
    }

    public function isTablet(): bool
    {
        return $this->detector->isTablet();
    }

    public function isFullView(): bool
    {
        return !$this->detector->isMobile();
    }
}
