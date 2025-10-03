<?php

declare(strict_types=1);

namespace SiteBundle\Controller;

use Doctrine\ORM\ORMException;
use SiteBundle\Exceptions\ImageNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use Psr\Log\LoggerInterface;
use SiteBundle\Entity\Category;
use SiteBundle\Entity\Emails;
use SiteBundle\Repository\EmailsRepository;
use SiteBundle\Repository\MediaRepository;
use SiteBundle\Services\ImageRenderService;
use SiteBundle\Services\ImageService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SiteImageController extends SiteController
{
    private EmailsRepository $emailsRepository;

    private ImageRenderService $imageRenderService;

    private MediaRepository $mediaRepository;

    private LoggerInterface $logger;

    private string $uploadAdsDir;

    public function __construct(
        EmailsRepository $emailsRepository,
        ImageRenderService $imageRenderService,
        MediaRepository $mediaRepository,
        LoggerInterface $logger,
        string $uploadAdsDir
    ) {
        $this->emailsRepository = $emailsRepository;
        $this->imageRenderService = $imageRenderService;
        $this->mediaRepository = $mediaRepository;
        $this->logger = $logger;
        $this->uploadAdsDir = $uploadAdsDir;
    }

    /**
     * @Route("/logo.png", name="site_logo", methods={"GET"})
     * @param Request $request
     *
     * @return BinaryFileResponse
     * @throws ORMException
     */
    public function getLogo(Request $request): BinaryFileResponse
    {
        $data = $this->requestToArray($request);
        if (count($data) > 0) {
            $email = $this->emailsRepository->findOneBy(['code' => $data['code']]);

            if ($email instanceof Emails) {
                $email->setStatus(Emails::EMAIL_SEEN);
                $this->emailsRepository->persist($email);
                $this->emailsRepository->flush();
            }
        }

        $response = new BinaryFileResponse('images/logo.png');

        return $response;
    }

    /**
     * @Route(
     *     "/{filter}/crna-gora-smestaj/{alias}.jpg",
     *     methods={"GET"},
     *     name="category_image_show",
     *     defaults={"alias": "sobe-apartmani"}
     * )
     *
     * @param string $filter
     * @param Category $category
     *
     * @return BinaryFileResponse
     * @throws ImageNotFoundException
     */
    public function getCategoryImage(string $filter, Category $category): BinaryFileResponse
    {
        $image = $category->getImage();

        $response = $this->imageRenderService->renderImageWithFilter($this->uploadAdsDir.$image, $filter);

        $response->setPublic();
        $response->setMaxAge(864000);

        return $response;
    }

    /**
     * @Route("/{entity}-slika/{filter}/{name}.jpeg",
     *     methods={"GET"},
     *     name="app.image_show",
     *     requirements={
     *          "entity": "ads|oglasi"
     *     })
     *
     * @param string $filter
     * @param string $name
     * @param Request $request
     * @return Response
     */
    public function getImage(string $filter, string $name, Request $request): Response
    {
        $response = $this->getImageFromFileSystem($name, $filter);

        if (null === $response) {
            $response = $this->getImageFromDb($name, $filter);

            if (null === $response) {
                $this->logger->error(
                    'Failed render image',
                    [
                        'filter' => $filter,
                        'image' => $name,
                        'request' => $request
                    ]
                );

                $response = new Response('', Response::HTTP_NOT_FOUND);
                $response->setMaxAge(0);

                return $response;
            }
        }

        $response->setPublic();
        $response->setMaxAge(864000);

        return $response;
    }

    private function getImageFromFileSystem(string $name, string $filter): ?BinaryFileResponse
    {
        try {
            $response = $this->imageRenderService->renderImageWithFilter($this->uploadAdsDir . $name, $filter);

            $response->setPublic();
            $response->setMaxAge(864000);
        } catch (ImageNotFoundException $notFoundException) {
            $response = null;
        }

        return $response;
    }

    private function getImageFromDb(string $name, string $filter): ?BinaryFileResponse
    {
        try {
            $image = $this->mediaRepository->findOneBy(['slug' => $name]);

            if (null === $image) {
                return null;
            }

            $response = $this->imageRenderService->renderImageWithFilter('/'.$this->uploadAdsDir.$image->getOriginalName(), $filter);

            $response->setPublic();
            $response->setMaxAge(864000);

            return $response;
        } catch (ImageNotFoundException $notFoundException) {
            return null;
        }
    }
}
