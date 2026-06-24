<?php

declare(strict_types=1);

namespace SiteBundle\EventListeners;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use SiteBundle\Entity\AdsInfoPageImage;
use SiteBundle\Services\ImageService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class AdsInfoPageFileUploadListener
{
    private const SUBDIR = 'info-pages/';

    public function __construct(
        private readonly ImageService $imageService,
        private readonly ParameterBagInterface $parameterBag
    ) {
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $this->adjustFile($args->getObject());
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $this->adjustFile($args->getObject());
    }

    public function postRemove(LifecycleEventArgs $args): void
    {
        $this->adjustFile($args->getObject());
    }

    private function adjustFile(object $entity): void
    {
        if (false === $entity instanceof AdsInfoPageImage) {
            return;
        }

        $file = $entity->getFile();

        if (false === $file instanceof UploadedFile) {
            return;
        }

        $uploadDir = rtrim($this->parameterBag->get('upload_image_dir'), '/') . '/' . self::SUBDIR;

        if (true === $entity->isDeleted()) {
            $this->imageService->deleteImages([$file]);

            return;
        }

        $targetName = $entity->getFilename() ?? $file->getClientOriginalName();

        $this->imageService->moveImageToFinalPath($file, $uploadDir, $targetName);
    }
}
