<?php

declare(strict_types=1);

namespace SiteBundle\Parser;

use SiteBundle\Dto\Ads\ResizeImageRequest;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

final class ResizeImageRequestParser
{
    public function fromRequest(Request $request): ?ResizeImageRequest
    {
        $file = $request->files->get('tmp_image');

        if (false === $file instanceof UploadedFile) {
            return null;
        }

        return new ResizeImageRequest($file);
    }
}
