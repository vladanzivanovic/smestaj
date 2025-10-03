<?php

declare(strict_types=1);

namespace SiteBundle\Services\Ads;

use Gedmo\Sluggable\Util\Urlizer;
use Psr\Log\LoggerInterface;
use SiteBundle\Entity\Ads;
use SiteBundle\Entity\Media;
use SiteBundle\Exceptions\ApplicationException;
use SiteBundle\Repository\MediaRepository;
use SiteBundle\Services\ImageService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Webmozart\Assert\Assert;

class AdsImageService
{
    private ImageService $imageService;

    private ParameterBagInterface $parameterBag;

    private MediaRepository $mediaRepository;

    private LoggerInterface $logger;

    public function __construct(
        ImageService $imageService,
        ParameterBagInterface $parameterBag,
        MediaRepository $mediaRepository,
        LoggerInterface $logger
    ) {
        $this->imageService = $imageService;
        $this->parameterBag = $parameterBag;
        $this->mediaRepository = $mediaRepository;
        $this->logger = $logger;
    }

    /**
     * @param Ads   $ads
     * @param array $data
     *
     * @return Ads
     */
    public function setImage(Ads $ads, array $data): void
    {
        $tmpDir = $this->parameterBag->get('upload_tmp_dir');
        $imageDir = $this->parameterBag->get('upload_image_dir');
        
        if(empty(array_filter($data))) {
            return;
        }

        Assert::true($this->validateMainImage($data), 'fields.main_image');

        $slug = Urlizer::transliterate($ads->getTitle());

        foreach ($data as $index => $image) {
            if (isset($image['id'])) {
                $imageObj = $this->mediaRepository->find($image['id']);

                if(isset($image['deleted']) && true === $image['deleted']) {
                    $image['file'] = $imageDir.$imageObj->getOriginalName();

                    try {
                        $this->markImageAsDeleted($imageObj);
                    } catch (FileNotFoundException $notFoundException) {
                        $this->logger->warning(
                            sprintf('File "%s" for ad "%s" has not been found.',
                                $imageObj->getName(),
                                $imageObj->getId()
                            )
                        );
                    }

                    $this->mediaRepository->delete($imageObj);

                    continue;
                }

                $imageObj->setIsMain($image['isMain']);

                continue;
            }

            try {
                $image['file'] = $image['originalFilePath'];
                $file = $this->imageService->setFileObject($image);
            } catch (FileNotFoundException $exception) {
                $exceptions[] = $image['fileName'];

                continue;
            }

            $mediaObj = new Media();

            if (!($file instanceof UploadedFile)) {
                continue;
            }

            $newName = md5($file->getFilename().$slug);

            $mediaObj->setName($newName);
            $mediaObj->setIsmain($image['isMain']);
            $mediaObj->setOriginalName($newName.'.'.$file->guessExtension());
            $mediaObj->setFile($file);

            $ads->addMedia($mediaObj);
        }
    }

    /**
     * Delete image from array collection
     * @param Ads $ads
     * @param $image
     */
    public function markImageAsDeleted(Media $media)
    {
        $imageDir = $this->parameterBag->get('upload_image_dir');

        $image = [
            'file' => $imageDir.$media->getOriginalName(),
            'fileName' => $media->getName(),
        ];

        try {
            $file = $this->imageService->setFileObject($image);

            $media->setFile($file);
        } catch (FileNotFoundException $exception) {
        }

        $media->setIsDeleted(true);
    }

    private function validateMainImage(array $data): bool
    {
        foreach ($data as $image) {
            if (true === !!$image['isMain']) {
                return true;
            }
        }

        return false;
    }
}
