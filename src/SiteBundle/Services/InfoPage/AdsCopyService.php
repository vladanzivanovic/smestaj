<?php

declare(strict_types=1);

namespace SiteBundle\Services\InfoPage;

use Random\RandomException;
use SiteBundle\Entity\Ads;
use SiteBundle\Entity\Adshastags;
use SiteBundle\Entity\AdsInfoHasTag;
use SiteBundle\Entity\AdsInfoPage;
use SiteBundle\Entity\AdsInfoPageImage;
use SiteBundle\Entity\Contact;
use SiteBundle\Entity\Media;
use SiteBundle\Services\ImageService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

final class AdsCopyService
{
    private const INFO_PAGES_SUBDIR = 'info-pages/';

    public function __construct(
        private readonly ImageService $imageService,
        private readonly ParameterBagInterface $parameterBag,
        private readonly Filesystem $filesystem
    ) {
    }

    public function copyFromAds(Ads $source, AdsInfoPage $target): void
    {
        $target->setPropertyName($source->getTitle());

        if (null !== $source->getShortDescription()) {
            $target->setShortDescriptionRs($source->getShortDescription());
        }

        if (true === $this->isValidUrl($source->getFacebook())) {
            $target->setFacebookUrl($source->getFacebook());
        }

        if (true === $this->isValidUrl($source->getInstagram())) {
            $target->setInstagramUrl($source->getInstagram());
        }

        if (null !== $source->getAddress()) {
            $target->setAddressStreet($source->getAddress());
        }

        $sourceCity = $source->getCityId();

        if (null !== $sourceCity) {
            $target->setAddressCity($sourceCity->getName());

            if (true === method_exists($sourceCity, 'getZipcode')) {
                $zip = $sourceCity->getZipcode();

                if (null !== $zip && '' !== $zip) {
                    $target->setAddressPostalCode((string) $zip);
                }
            }
        }

        $target->setGoogleMapsLat($source->getLat());
        $target->setGoogleMapsLng($source->getLng());

        $this->copyContact($source->getContact(), $target);
        $this->copyMedia($source, $target);
        $this->copyTags($source, $target);
    }

    private function copyTags(Ads $source, AdsInfoPage $target): void
    {
        $existingTagIds = [];

        foreach ($target->getHasTags() as $existing) {
            $tag = $existing->getTag();
            $existingTagIds[(int) $tag->getId()] = true;
        }

        foreach ($source->getHasTags() as $sourceRow) {
            if (false === $sourceRow instanceof Adshastags) {
                continue;
            }

            $tag = $sourceRow->getTag();

            if (null === $tag) {
                continue;
            }

            $tagId = (int) $tag->getId();

            if (true === array_key_exists($tagId, $existingTagIds)) {
                continue;
            }

            $row = new AdsInfoHasTag();
            $row->setTag($tag);
            $row->setValue((string) $sourceRow->getValue());
            $target->addHasTag($row);
            $existingTagIds[$tagId] = true;
        }
    }

    private function copyContact(?Contact $contact, AdsInfoPage $target): void
    {
        if (null === $contact) {
            return;
        }

        if (null !== $contact->getFirstname()) {
            $target->setHostFirstName($contact->getFirstname());
        }

        if (null !== $contact->getLastname()) {
            $target->setHostLastName($contact->getLastname());
        }

        if (null !== $contact->getMobilePhone()) {
            $target->setHostMobile($contact->getMobilePhone());
        }

        $viber = $contact->getViber();

        if (null !== $viber && '' !== trim($viber)) {
            $target->setViberPhone(trim($viber));
        }
    }

    private function copyMedia(Ads $source, AdsInfoPage $target): void
    {
        $sourceDir = $this->resolveSourceBaseDir();
        $targetDir = $sourceDir . self::INFO_PAGES_SUBDIR;

        $this->imageService->checkExistsAndCreateFolder($targetDir);

        $position = 0;

        foreach ($source->getMedia() as $media) {
            if (false === $media instanceof Media) {
                continue;
            }

            $originalFilename = $media->getOriginalName();

            if ('' === $originalFilename) {
                continue;
            }

            $sourcePath = $sourceDir . $originalFilename;

            if (false === $this->filesystem->exists($sourcePath)) {
                continue;
            }

            $newFilename = $this->buildUniqueFilename($targetDir, $originalFilename);

            $this->filesystem->copy($sourcePath, $targetDir . $newFilename);

            $image = new AdsInfoPageImage();
            $image->setFilename($newFilename);
            $image->setOriginalName($media->getOriginalName());
            $image->setPosition($position);

            $target->addImage($image);

            $position++;
        }
    }

    private function isValidUrl(?string $value): bool
    {
        if (null === $value || '' === $value) {
            return false;
        }

        return false !== filter_var($value, FILTER_VALIDATE_URL);
    }

    private function resolveSourceBaseDir(): string
    {
        $projectDir = rtrim((string) $this->parameterBag->get('kernel.project_dir'), '/');
        $uploadDir = trim((string) $this->parameterBag->get('upload_image_dir'), '/');

        return $projectDir . '/web/' . $uploadDir . '/';
    }

    private function buildUniqueFilename(string $targetDir, string $originalFilename): string
    {
        $pathInfo = pathinfo($originalFilename);
        $base = $pathInfo['filename'] ?? 'image';
        $extension = isset($pathInfo['extension']) ? '.' . $pathInfo['extension'] : '';

        try {
            $suffix = bin2hex(random_bytes(4));
        } catch (RandomException) {
            $suffix = (string) microtime(true);
        }

        $candidate = $base . '_' . $suffix . $extension;

        while (true === $this->filesystem->exists($targetDir . $candidate)) {
            try {
                $suffix = bin2hex(random_bytes(4));
            } catch (RandomException) {
                $suffix = (string) microtime(true);
            }

            $candidate = $base . '_' . $suffix . $extension;
        }

        return $candidate;
    }
}
