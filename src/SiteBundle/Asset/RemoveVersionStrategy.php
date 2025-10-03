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
    public function getVersion($path)
    {
        // TODO: Implement getVersion() method.
    }

    /**
     * Applies version to the supplied path.
     *
     * @param string $path A path
     *
     * @return string The versionized path
     */
    public function applyVersion($path)
    {
        return $path;
    }
}