<?php

namespace SiteBundle\Controller\Api\Ads;

use SiteBundle\Controller\SiteController;
use SiteBundle\Helper\RandomCodeGenerator;
use SiteBundle\Services\Ads\AdsImageResizer;
use SiteBundle\Services\ImageService;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\SecurityBundle\Security;

class AdsImagesController extends SiteController
{
    private $imageResizer;
    private $imageService;
    private Security $security;
    private RandomCodeGenerator $randomCodeGenerator;

    public function __construct(
        AdsImageResizer $imageResizer,
        ImageService $imageService,
        Security $security,
        RandomCodeGenerator $randomCodeGenerator
    ) {
        $this->imageResizer = $imageResizer;
        $this->imageService = $imageService;
        $this->security = $security;
        $this->randomCodeGenerator = $randomCodeGenerator;
    }

    #[Route('/api/ads-image/resize', name: 'site_ads_image_resize_on_fly', methods: ['POST'])]
    public function resizeImageOnFlyAction(Request $request)
    {
        try {
            /** @var UploadedFile $file */
            $file = $request->files->get('tmp_image');

            $name = md5($file->getFilename()).
                $this->randomCodeGenerator->random(15);

            $originalPath = $this->imageResizer->resizeOnFly($file, $name.'.'.$file->getClientOriginalExtension());

            return $this->json([
                'file' => '/uploads/tmp_images/'.$originalPath,
                'originalFilePath' => $originalPath,
                'fileName' => $name,
                'isMain' => false,
                'isImage' => true,
            ]);

        } catch (\Throwable $throwable) {
            return $this->json([], JsonResponse::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/api/remove-tmp-image/{filename}', methods: ['DELETE'], name: 'remove_tmp_image')]
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
