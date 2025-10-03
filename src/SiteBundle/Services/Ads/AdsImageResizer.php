<?php

namespace SiteBundle\Services\Ads;

use Liip\ImagineBundle\Controller\ImagineController;
use SiteBundle\Helper\RandomCodeGenerator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\DataUriNormalizer;

class AdsImageResizer
{
    private $rootDir;
    private $imagine;
    private $tmpDir;
    private $parameterBag;

    /**
     * @param ImagineController     $imagine
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(
        ImagineController $imagine,
        ParameterBagInterface $parameterBag
    ) {
        $this->parameterBag = $parameterBag;
        $this->rootDir = $parameterBag->get('kernel.root_dir');
        $this->imagine = $imagine;
        $this->tmpDir = $parameterBag->get('upload_tmp_dir');
    }

    /**
     * @param UploadedFile $file
     *
     * @return string
     */
    public function resizeOnFly(UploadedFile $file, string $name)
    {
        $movedFile = $file->move($this->tmpDir, $name);

        $originalPath = $movedFile->getPathname();
        $filteredPath = 'uploads/tmp_images/'. $originalPath;

        $this->imagine
            ->filterAction(
                new Request(),
                $originalPath,
                'tmp_images'
            );

        return $originalPath;
    }
}
