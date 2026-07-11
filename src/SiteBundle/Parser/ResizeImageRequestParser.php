<?php

declare(strict_types=1);

namespace SiteBundle\Parser;

use SiteBundle\Dto\Ads\ResizeImageResult;
use SiteBundle\Helper\RandomCodeGenerator;
use SiteBundle\Services\Ads\AdsImageResizer;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Orchestrates the DTO → domain-value translation for POST
 * /api/ads-image/resize. Moves the temp-image resize + naming logic out
 * of the controller (previously inline) into this dedicated parser.
 */
final class ResizeImageRequestParser
{
    public function __construct(
        private readonly AdsImageResizer $adsImageResizer,
        private readonly RandomCodeGenerator $randomCodeGenerator,
    ) {
    }

    public function parse(UploadedFile $file): ResizeImageResult
    {
        $name = md5($file->getFilename()) . $this->randomCodeGenerator->random(15);
        $originalPath = $this->adsImageResizer->resizeOnFly(
            $file,
            $name . '.' . $file->getClientOriginalExtension()
        );

        return new ResizeImageResult(
            file: '/uploads/tmp_images/' . $originalPath,
            originalFilePath: $originalPath,
            fileName: $name,
            isMain: false,
            isImage: true,
        );
    }
}
