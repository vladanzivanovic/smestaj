<?php

declare(strict_types=1);

namespace AdminBundle\Parser;

use AdminBundle\Model\InfoPageEditModel;
use DateTimeImmutable;
use SiteBundle\Entity\AdsInfoPage;
use SiteBundle\Services\InfoPage\HouseRulesHtmlSanitizer;
use Symfony\Component\HttpFoundation\ParameterBag;

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

    public function parse(ParameterBag $bag, AdsInfoPage $entity): InfoPageEditModel
    {
        $this->writeScalars($bag, $entity);
        $this->writeLocaleFields($bag, $entity);
        $this->writeTimes($bag, $entity);

        $violations = [];

        $street = $this->trimToNull($bag->get('address_street'));
        $city = $this->trimToNull($bag->get('address_city'));
        $lat = $this->toFloatOrNull($bag->get('google_maps_lat'));
        $lng = $this->toFloatOrNull($bag->get('google_maps_lng'));

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

        $tags = $this->parseTagsBag($bag);

        $imageStates = $this->parseImageStates($bag->get('uploadedImages'));

        return new InfoPageEditModel(
            entity: $entity,
            requestedPublish: $this->parseBool($bag->get('published')),
            tags: $tags,
            imageStates: $imageStates,
            parserViolations: $violations,
        );
    }

    private function writeScalars(ParameterBag $bag, AdsInfoPage $entity): void
    {
        $slug = $this->trimToNull($bag->get('slug'));

        if (null !== $slug) {
            $entity->setSlug($slug);
        }

        $propertyName = $this->trimToNull($bag->get('propertyName'));

        if (null !== $propertyName) {
            $entity->setPropertyName($propertyName);
        }

        $entity->setHostFirstName($this->trimToNull($bag->get('host_first_name')));
        $entity->setHostLastName($this->trimToNull($bag->get('host_last_name')));
        $entity->setHostMobile($this->trimToNull($bag->get('host_mobile')));

        $entity->setInstagramUrl($this->validateUrlOrNull($bag->get('instagram_url')));
        $entity->setFacebookUrl($this->validateUrlOrNull($bag->get('facebook_url')));
        $entity->setWhatsappPhone($this->validatePhoneOrNull($bag->get('whatsapp_phone')));
        $entity->setViberPhone($this->validatePhoneOrNull($bag->get('viber_phone')));
        $entity->setBookingUrl($this->validateUrlOrNull($bag->get('booking_url')));
        $entity->setAirbnbUrl($this->validateUrlOrNull($bag->get('airbnb_url')));

        $entity->setGoogleReviewInput($this->trimToNull($bag->get('google_review_input')));

        $entity->setAddressStreet($this->trimToNull($bag->get('address_street')));
        $entity->setAddressPostalCode($this->trimToNull($bag->get('address_postal_code')));
        $entity->setAddressCity($this->trimToNull($bag->get('address_city')));

        $entity->setGoogleMapsLat($this->toFloatOrNull($bag->get('google_maps_lat')));
        $entity->setGoogleMapsLng($this->toFloatOrNull($bag->get('google_maps_lng')));

        $entity->setWifiUsername($this->trimToNull($bag->get('wifi_username')));
        $entity->setWifiPassword($this->trimToNull($bag->get('wifi_password')));
        $entity->setHouseRules($this->sanitizer->sanitize($this->trimToNull($bag->get('house_rules'))));
    }

    private function writeLocaleFields(ParameterBag $bag, AdsInfoPage $entity): void
    {
        foreach (self::LOCALES as $locale) {
            $suffix = ucfirst($locale);

            $tagline = $this->trimToNull($bag->get('tagline_' . $locale));
            $shortDescription = $this->trimToNull($bag->get('short_description_' . $locale));
            $welcomeMessage = $this->trimToNull($bag->get('welcome_message_' . $locale));

            $entity->{'setTagline' . $suffix}($tagline);
            $entity->{'setShortDescription' . $suffix}($shortDescription);
            $entity->{'setWelcomeMessage' . $suffix}($welcomeMessage);
        }
    }

    private function writeTimes(ParameterBag $bag, AdsInfoPage $entity): void
    {
        $entity->setCheckInTime($this->parseTime($bag->get('check_in_time')));
        $entity->setCheckOutTime($this->parseTime($bag->get('check_out_time')));
    }

    /**
     * Extracts the nested tag bag posted by the InfoEdit form into
     * `[<tag_type_label>][<tag_id>] => <value-string>`. Non-numeric tag IDs,
     * non-string type labels, and non-scalar values are silently dropped.
     * Returns an empty array when the form omits the `tags` key entirely.
     *
     * @return array<string, array<int, string>>
     */
    private function parseTagsBag(ParameterBag $bag): array
    {
        $raw = $bag->all()['tags'] ?? [];

        if (false === is_array($raw)) {
            return [];
        }

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
