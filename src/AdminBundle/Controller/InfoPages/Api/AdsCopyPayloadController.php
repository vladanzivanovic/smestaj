<?php

declare(strict_types=1);

namespace AdminBundle\Controller\InfoPages\Api;

use SiteBundle\Entity\Ads;
use SiteBundle\Entity\Media;
use SiteBundle\Repository\AdsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class AdsCopyPayloadController extends AbstractController
{
    public function __construct(
        private readonly AdsRepository $adsRepository,
        private readonly ParameterBagInterface $parameterBag
    ) {
    }

    #[Route('/api/info-pages/copy-payload/{adsId}', name: 'admin.api.info_pages.copy_payload', methods: ['GET'], requirements: ['adsId' => '\d+'], options: ['expose' => true])]
    public function payload(int $adsId): JsonResponse
    {
        $ads = $this->adsRepository->find($adsId);

        if (false === $ads instanceof Ads) {
            throw new NotFoundHttpException();
        }

        return new JsonResponse([
            'fields' => $this->buildFields($ads),
            'images' => $this->buildImages($ads),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildFields(Ads $ads): array
    {
        $contact = $ads->getContact();
        $city = $ads->getCityId();

        return [
            'propertyName' => $ads->getTitle(),
            'shortDescriptionRs' => $ads->getShortDescription(),
            'facebookUrl' => $this->onlyValidUrl($ads->getFacebook()),
            'instagramUrl' => $this->onlyValidUrl($ads->getInstagram()),
            'addressStreet' => $ads->getAddress(),
            'addressCity' => null === $city ? null : $city->getName(),
            'addressPostalCode' => null === $city ? null : (string) $city->getZipcode(),
            'googleMapsLat' => $ads->getLat(),
            'googleMapsLng' => $ads->getLng(),
            'hostFirstName' => null === $contact ? null : $contact->getFirstname(),
            'hostLastName' => null === $contact ? null : $contact->getLastname(),
            'hostMobile' => null === $contact ? null : $contact->getMobilePhone(),
            'viberPhone' => null === $contact ? null : (null === $contact->getViber() ? null : trim($contact->getViber())),
        ];
    }

    /**
     * @return array<int, array{filename: string, originalName: string, url: string}>
     */
    private function buildImages(Ads $ads): array
    {
        $base = '/' . ltrim((string) $this->parameterBag->get('upload_image_dir'), '/');
        $rows = [];

        foreach ($ads->getMedia() as $media) {
            if (false === $media instanceof Media) {
                continue;
            }

            $rows[] = [
                'filename' => $media->getOriginalName(),
                'originalName' => $media->getOriginalName(),
                'url' => rtrim($base, '/') . '/' . $media->getOriginalName(),
            ];
        }

        return $rows;
    }

    private function onlyValidUrl(?string $value): ?string
    {
        if (null === $value || '' === trim($value)) {
            return null;
        }

        if (false === filter_var($value, FILTER_VALIDATE_URL)) {
            return null;
        }

        return $value;
    }
}
