<?php

declare(strict_types=1);

namespace AdminBundle\Parser;

use AdminBundle\Dto\InfoPage\InfoPageEditRequest;
use AdminBundle\Model\InfoPageEditModel;
use DateTimeImmutable;
use SiteBundle\Entity\AdsInfoPage;
use SiteBundle\Services\InfoPage\HouseRulesHtmlSanitizer;

/**
 * Parses the info-page edit form into the entity (scalar fields)
 * plus an InfoPageEditModel carrying supplementary data (tags,
 * image uploads/deletions, requested-publish flag) that
 * the handler needs to reconcile.
 *
 * Does not implement RequestParserInterface because the interface
 * contract (`parse(): EntityInterface`) does not capture the dual
 * output (entity + supplementary model) required here.
 */
final class InfoPageEditRequestParser
{
    private const LOCALES = ['rs', 'en'];

    public function __construct(
        private readonly HouseRulesHtmlSanitizer $sanitizer,
    ) {
    }

    public function parse(InfoPageEditRequest $dto, AdsInfoPage $entity): InfoPageEditModel
    {
        $this->writeScalars($dto, $entity);
        $this->writeLocaleFields($dto, $entity);
        $this->writeTimes($dto, $entity);

        $violations = [];

        $street = $this->trimToNull($dto->addressStreet);
        $city = $this->trimToNull($dto->addressCity);
        $lat = $this->toFloatOrNull($dto->googleMapsLat);
        $lng = $this->toFloatOrNull($dto->googleMapsLng);

        if (null !== $street && null !== $city && (null === $lat || null === $lng)) {
            $message = 'Mapa nije postavljena — unesite grad i ulicu pa sačekajte da se mapa centrira na tačnu lokaciju.';
            $violations['addressStreet'] = $message;
            $violations['addressCity'] = $message;
        }

        $wifiUsername = $entity->getWifiUsername();
        $wifiPassword = $entity->getWifiPassword();

        if (null !== $wifiUsername && 100 < mb_strlen($wifiUsername)) {
            $violations['wifiUsername'] = 'Wi-Fi korisničko ime može imati najviše 100 karaktera.';
        }

        if (null !== $wifiPassword && 100 < mb_strlen($wifiPassword)) {
            $violations['wifiPassword'] = 'Wi-Fi lozinka može imati najviše 100 karaktera.';
        }

        $tags = $this->parseTagsBag($dto->tags);

        $imageStates = $this->parseImageStates($dto->uploadedImages);

        return new InfoPageEditModel(
            entity: $entity,
            requestedPublish: $this->parseBool($dto->published),
            tags: $tags,
            imageStates: $imageStates,
            parserViolations: $violations,
        );
    }

    private function writeScalars(InfoPageEditRequest $dto, AdsInfoPage $entity): void
    {
        $slug = $this->trimToNull($dto->slug);

        if (null !== $slug) {
            $entity->setSlug($slug);
        }

        $propertyName = $this->trimToNull($dto->propertyName);

        if (null !== $propertyName) {
            $entity->setPropertyName($propertyName);
        }

        $entity->setHostFirstName($this->trimToNull($dto->hostFirstName));
        $entity->setHostLastName($this->trimToNull($dto->hostLastName));
        $entity->setHostMobile($this->trimToNull($dto->hostMobile));

        $entity->setInstagramUrl($this->validateUrlOrNull($dto->instagramUrl));
        $entity->setFacebookUrl($this->validateUrlOrNull($dto->facebookUrl));
        $entity->setWhatsappPhone($this->validatePhoneOrNull($dto->whatsappPhone));
        $entity->setViberPhone($this->validatePhoneOrNull($dto->viberPhone));
        $entity->setBookingUrl($this->validateUrlOrNull($dto->bookingUrl));
        $entity->setAirbnbUrl($this->validateUrlOrNull($dto->airbnbUrl));

        $entity->setGoogleReviewInput($this->trimToNull($dto->googleReviewInput));

        $entity->setAddressStreet($this->trimToNull($dto->addressStreet));
        $entity->setAddressPostalCode($this->trimToNull($dto->addressPostalCode));
        $entity->setAddressCity($this->trimToNull($dto->addressCity));

        $entity->setGoogleMapsLat($this->toFloatOrNull($dto->googleMapsLat));
        $entity->setGoogleMapsLng($this->toFloatOrNull($dto->googleMapsLng));

        $entity->setWifiUsername($this->trimToNull($dto->wifiUsername));
        $entity->setWifiPassword($this->trimToNull($dto->wifiPassword));
        $entity->setHouseRules($this->sanitizer->sanitize($this->trimToNull($dto->houseRules)));
    }

    private function writeLocaleFields(InfoPageEditRequest $dto, AdsInfoPage $entity): void
    {
        foreach (self::LOCALES as $locale) {
            $suffix = ucfirst($locale);

            $tagline = $this->trimToNull($this->localeValue($dto, 'tagline', $locale));
            $shortDescription = $this->trimToNull($this->localeValue($dto, 'shortDescription', $locale));
            $welcomeMessage = $this->trimToNull($this->localeValue($dto, 'welcomeMessage', $locale));

            $entity->{'setTagline' . $suffix}($tagline);
            $entity->{'setShortDescription' . $suffix}($shortDescription);
            $entity->{'setWelcomeMessage' . $suffix}($welcomeMessage);
        }
    }

    /**
     * Reads a locale-suffixed DTO property (e.g. `taglineRs`, `taglineEn`).
     * The DTO holds one flat property per locale per field — the parser
     * still groups them by locale to preserve the legacy loop structure.
     */
    private function localeValue(InfoPageEditRequest $dto, string $field, string $locale): ?string
    {
        $property = $field . ucfirst($locale);

        return $dto->{$property} ?? null;
    }

    private function writeTimes(InfoPageEditRequest $dto, AdsInfoPage $entity): void
    {
        $entity->setCheckInTime($this->parseTime($dto->checkInTime));
        $entity->setCheckOutTime($this->parseTime($dto->checkOutTime));
    }

    /**
     * Normalises the DTO's `tags` 2D map into
     * `[<tag_type_label>][<tag_id>] => <value-string>`. Non-numeric tag IDs,
     * non-string type labels, and non-scalar values are silently dropped.
     * Returns an empty array when the form omits the `tags` key entirely.
     *
     * @param array<string, array<int|string, string>> $raw
     *
     * @return array<string, array<int, string>>
     */
    private function parseTagsBag(array $raw): array
    {
        $out = [];

        foreach ($raw as $typeLabel => $tagArray) {
            if (false === is_string($typeLabel) || false === is_array($tagArray)) {
                continue;
            }

            foreach ($tagArray as $tagId => $value) {
                if (false === is_numeric($tagId)) {
                    continue;
                }

                $out[$typeLabel][(int) $tagId] = (string) (true === is_scalar($value) ? $value : '');
            }
        }

        return $out;
    }

    private function parseTime(mixed $value): ?DateTimeImmutable
    {
        if (false === is_string($value) || '' === trim($value)) {
            return null;
        }

        $parsed = DateTimeImmutable::createFromFormat('H:i', trim($value));

        if (false === $parsed) {
            return null;
        }

        return $parsed;
    }

    /**
     * Normalises the JSON `uploadedImages` payload sent by InfoPageEditHandler.js.
     * Each element carries the dropzone row state (`id`, `isMain`, `fileName`,
     * `originalFilePath`, `deleted`); missing/invalid input degrades silently
     * to an empty list — the frontend always emits a valid JSON array, and the
     * handler treats `[]` as "no image rows submitted".
     *
     * @return array<int, array{id: ?int, isMain: bool, fileName: ?string, originalFilePath: ?string, deleted: bool}>
     */
    private function parseImageStates(mixed $raw): array
    {
        if (false === is_string($raw) || '' === $raw) {
            return [];
        }

        $decoded = json_decode($raw, true);

        if (false === is_array($decoded)) {
            return [];
        }

        $out = [];

        foreach ($decoded as $item) {
            if (false === is_array($item)) {
                continue;
            }

            $rawId = $item['id'] ?? null;
            $id = null;

            if (true === is_numeric($rawId)) {
                $id = (int) $rawId;
            }

            $isMain = true === ($item['isMain'] ?? false);

            $fileName = null;

            if (true === is_string($item['fileName'] ?? null)) {
                $trimmed = trim($item['fileName']);

                if ('' !== $trimmed) {
                    $fileName = $trimmed;
                }
            }

            $originalFilePath = null;

            if (true === is_string($item['originalFilePath'] ?? null)) {
                $trimmed = trim($item['originalFilePath']);

                if ('' !== $trimmed) {
                    $originalFilePath = $trimmed;
                }
            }

            $deleted = true === ($item['deleted'] ?? false);

            $out[] = [
                'id' => $id,
                'isMain' => $isMain,
                'fileName' => $fileName,
                'originalFilePath' => $originalFilePath,
                'deleted' => $deleted,
            ];
        }

        return $out;
    }

    private function parseBool(mixed $value): bool
    {
        if (true === $value || 1 === $value) {
            return true;
        }

        if (false === is_string($value)) {
            return false;
        }

        $normalised = strtolower(trim($value));

        return 'true' === $normalised || '1' === $normalised || 'on' === $normalised;
    }

    private function trimToNull(mixed $value): ?string
    {
        if (false === is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        if ('' === $trimmed) {
            return null;
        }

        return $trimmed;
    }

    private function toFloatOrNull(mixed $value): ?float
    {
        if (null === $value || '' === $value) {
            return null;
        }

        if (false === is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }

    private function validateUrlOrNull(mixed $value): ?string
    {
        $trimmed = $this->trimToNull($value);

        if (null === $trimmed) {
            return null;
        }

        if (false === filter_var($trimmed, FILTER_VALIDATE_URL)) {
            return null;
        }

        return $trimmed;
    }

    private function validatePhoneOrNull(mixed $value): ?string
    {
        $trimmed = $this->trimToNull($value);

        if (null === $trimmed) {
            return null;
        }

        if (1 !== preg_match('/^\+?[0-9 ()-]{7,30}$/', $trimmed)) {
            return null;
        }

        return $trimmed;
    }
}
