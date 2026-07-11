<?php

declare(strict_types=1);

namespace SiteBundle\Asset;

use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;

final class RemoveVersionStrategy implements VersionStrategyInterface
{

    /**
     * Returns the asset version for an asset.
     *
     * @param string $path A path
     *
     * @return string The version string
     */
    public function getVersion($path): string
    {
        return '';
    }

    public function applyVersion($path): string
    {
        return $path;
    }
}
