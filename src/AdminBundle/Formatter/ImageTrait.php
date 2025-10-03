<?php

declare(strict_types=1);

namespace AdminBundle\Formatter;

use Doctrine\Common\Collections\Collection;
use Exception;
use SiteBundle\Entity\Media;
use Symfony\Component\Routing\RouterInterface;

trait ImageTrait
{
    /**
     * @param RouterInterface $router
     * @param array           $images
     * @param string          $entity
     * @param string          $filter
     *
     * @return array
     */
    private function imagesFormatter(RouterInterface $router, array $images, string $entity, string $filter = 'tmp_images'): array
    {
         return array_map(function ($image) use ($router, $entity, $filter) {
            $image['file'] = $router->generate('app.image_show', ['entity' => $entity, 'name' => $image['fileName'], 'filter' => $filter]);

            return $image;
        }, $images);
    }
}