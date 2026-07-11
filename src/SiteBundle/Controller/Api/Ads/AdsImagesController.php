<?php

declare(strict_types=1);

namespace SiteBundle\Controller\Api\Ads;

use SiteBundle\Controller\SiteController;
use SiteBundle\Parser\ResizeImageRequestParser;
use SiteBundle\Services\ImageService;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapUploadedFile;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bundle\SecurityBundle\Security;

final class AdsImagesController extends SiteController
{
    private ImageService $imageService;
    private Security $security;
    private ResizeImageRequestParser $resizeImageRequestParser;

    public function __construct(
        ImageService $imageService,
        Security $security,
        ResizeImageRequestParser $resizeImageRequestParser
    ) {
        $this->imageService = $imageService;
        $this->security = $security;
        $this->resizeImageRequestParser = $resizeImageRequestParser;
    }

    #[Route('/api/ads-image/resize', name: 'site_ads_image_resize_on_fly', methods: ['POST'])]
    public function resizeImageOnFlyAction(
        #[MapUploadedFile(constraints: [new Assert\Image(maxSize: '10M')], name: 'tmp_image')] UploadedFile $file
    ): JsonResponse {
        try {
            $result = $this->resizeImageRequestParser->parse($file);

            return $this->json([
                'file' => $result->file,
                'originalFilePath' => $result->originalFilePath,
                'fileName' => $result->fileName,
                'isMain' => $result->isMain,
                'isImage' => $result->isImage,
            ]);
        } catch (\Throwable $throwable) {
            return $this->json([], JsonResponse::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/api/remove-tmp-image/{filename}', name: 'remove_tmp_image', methods: ['DELETE'])]
    public function removeTmpImage(string $filename)
    {
        $file = $this->imageService->setFileObject([
            'file' => 'uploads/tmp/'.$filename,
            'fileName' => $filename,
        ]);

        if ($file instanceof UploadedFile) {
            $this->imageService->deleteImage($file);
        }

        return $this->json([]);
    }
}
