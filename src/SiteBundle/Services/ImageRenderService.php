<?php

declare(strict_types=1);

namespace SiteBundle\Services;

use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;
use Liip\ImagineBundle\Service\FilterService;
use SiteBundle\Exceptions\ImageNotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

final class ImageRenderService
{
    private FilterService $filterService;

    private ParameterBagInterface $parameterBag;

    public function __construct(
        ParameterBagInterface $parameterBag,
        FilterService $filterService
    ) {
        $this->filterService = $filterService;
        $this->parameterBag = $parameterBag;
    }
    /**
     * @param string $path
     * @param string $filter
     *
     * @return BinaryFileResponse
     */
    public function renderImageWithFilter(string $path, string $filter): BinaryFileResponse
    {
        try {
            $url = $this->filterService->getUrlOfFilteredImage($path, $filter);

            $path = parse_url($url, PHP_URL_PATH);

            $file = new BinaryFileResponse($path);
        } catch (FileException $exception) {
            $file = new BinaryFileResponse(substr($path, 1));
        } catch (NotLoadableException $notLoadableException) {
            throw new ImageNotFoundException('Image not found', 0 ,$notLoadableException);
        }

        return $file;
    }
}
