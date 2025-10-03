<?php

namespace SiteBundle\EventListeners;

use Doctrine\ORM\Event\LifecycleEventArgs;
use SiteBundle\Entity\Media;
use SiteBundle\Services\ImageService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AdsFileUploadListener
{
    private ImageService $imageService;

    private ParameterBagInterface $parameterBag;

    public function __construct(
        ImageService $imageService,
        ParameterBagInterface $parameterBag
    ) {
        $this->imageService = $imageService;
        $this->parameterBag = $parameterBag;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        $this->adjustFile($entity);
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        $this->adjustFile($entity);
    }

    public function postRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        $this->adjustFile($entity);
    }

    private function adjustFile($entity)
    {
        if (!$entity instanceof Media) {
            return ;
        }

        if (!$entity->getFile() instanceof UploadedFile) {
            return ;
        }

        if(true === $entity->isDeleted()) {
            $this->imageService->deleteImages([$entity->getFile()]);

            return;
        }

        $uploadDir = $this->parameterBag->get('upload_image_dir');

        $this->imageService->moveImageToFinalPath($entity->getFile(), $uploadDir, $entity->getOriginalName());
    }
}
