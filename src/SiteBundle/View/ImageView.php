<?php

declare(strict_types=1);

namespace SiteBundle\View;

use SiteBundle\Entity\Media;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

final class ImageView
{
    private RouterInterface $router;

    public function __construct(
        RouterInterface $router
    ) {
        $this->router = $router;
    }

    public function view(Media $media, string $entityName, array $filters): array
    {
        $imageLinks = [];

        foreach ($filters as $filter) {
            $imageLinks[$filter] = $this->generateImageLink($media->getName(), $entityName, $filter);
        }

        $view = [
            'id' => $media->getId(),
            '_links' => $imageLinks,
        ];

        return $view;
    }


    private function generateImageLink(string $name, string $entity, string $filter): string
    {
        return $this->router->generate(
            'app.image_show',
            ['entity' => $entity, 'name' => $name, 'filter' => $filter],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }
}
