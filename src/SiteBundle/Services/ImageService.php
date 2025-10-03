<?php

namespace SiteBundle\Services;

ini_set('memory_limit', '512M');

use Liip\ImagineBundle\Controller\ImagineController;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Service\FilterService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use \Throwable;

class ImageService
{
    private $fs;
    private $fileTypes;
    private $tmpDir;
    private $cacheManager;
    private $imagine;

    private ParameterBagInterface $parameterBag;

    public function __construct(
        Filesystem $filesystem,
        ParameterBagInterface $parameterBag,
        CacheManager $cacheManager,
        ImagineController $imagine,
        DataManager $dataManager
    ) {
        $this->fs = $filesystem;
        $this->fileTypes = $parameterBag->get('file_types');
        $this->tmpDir = $parameterBag->get('upload_tmp_dir');
        $this->cacheManager = $cacheManager;
        $this->imagine = $imagine;
        $this->parameterBag = $parameterBag;
    }

    public function moveImageToFinalPath($file, $destination, $newName = null)
    {
        if(!($file instanceof UploadedFile)) {
            $file['file'] = substr($file['file'], 1);

            $file = $this->setFileObject($file);

            if (null === $file) {
                return false;
            }
        }

        $file->move($destination, $newName);
        $this->deleteTmpImage($file->getClientOriginalName());
    }

    /**
     * @param array $image
     *
     * @return UploadedFile
     */
    public function setFileObject(array $image): UploadedFile
    {
        return new UploadedFile($image['file'], $image['fileName'], null, null, true);
    }

    /**
     * Check if directory exist and thumb inside
     * If not exist then create new folders
     *
     * @return bool
     * @throws IOException
     */
    public function checkExistsAndCreateFolder($folder, $setThumb = false)
    {
        if (!$this->fs->exists($folder)) {
            $this->fs->mkdir($folder, 0775);
        }
        if (true === $setThumb && !$this->fs->exists($folder . '/thumb'))
            $this->fs->mkdir($folder . '/thumb', 0775);

        return true;
    }

    /**
     * @param UploadedFile $file
     */
    public function deleteImage(UploadedFile $file): void
    {
        $path = $file->getPathname();

        if ($this->fs->exists($path)) {
            $this->fs->remove($path);
            $this->cacheManager->remove($path);
        }
    }


    /**
     * @param array $images
     *
     * @return void
     */
    public function deleteImages(array $images): void
    {
        /** @var UploadedFile $image */
        foreach ($images as $image) {
            $this->deleteImage($image);
        }
    }

    // TODO This should be removed
    /**
     * @param UploadedFile $file
     * @param string       $filter
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function resizeOnFly(UploadedFile $file, string $filter)
    {
        $path = $this->imagine
            ->filterAction(
                new Request(),
                $file->getPathname(),
                $filter
            );

        return $path;
    }

    /**
     * @param        $file
     * @param string $path
     *
     * @return File
     */
    public function uploadToPath($file, string $path): File
    {
        if (!$file instanceof UploadedFile) {
            // TODO set instance for UploadedFile
        }

        try {
            $movedFile = $file->move($path, $file->getClientOriginalName());
        } catch (\Throwable $throwable) {
            dd($throwable);
        }

        return $movedFile;
    }

    private function deleteTmpImage($file):void
    {
        $path = $this->tmpDir.DIRECTORY_SEPARATOR.$file;

        if($file instanceof UploadedFile) {
            $path = $file->getPath();
        }

        if ($this->fs->exists($path)) {
            $this->fs->remove($path);
        }
    }
}