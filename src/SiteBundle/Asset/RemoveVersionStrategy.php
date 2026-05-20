<?php

namespace SiteBundle\Asset;

use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;

class RemoveVersionStrategy implements VersionStrategyInterface
{

    /**
     * Returns the asset version for an asset.
     *
     * @param string $path A path
     *
     * @return string The version string
     */
    public function getVersion(string $path): string
    {
        return '';
    }

    public function applyVersion(string $path): string
    {
        return $path;
    }
}