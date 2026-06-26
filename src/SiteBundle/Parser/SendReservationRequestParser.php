<?php

declare(strict_types=1);

namespace SiteBundle\Parser;

use SiteBundle\Dto\Reservation\SendReservationRequest;
use SiteBundle\Entity\Ads;

/**
 * Converts a SendReservationRequest DTO into the array shape consumed by
 * SiteBundle\Handler\UserReservationHandler::setReservation(array).
 *
 * The returned array mirrors the legacy controller behaviour:
 *   - Excludes `token` (CSRF stays in controller; not a Reservation property).
 *   - Excludes the form's hidden `adsid` field (Ads comes from MapEntity instead).
 *   - Applies the same empty-value-to-null normalization as the legacy
 *     `SiteController::emptyValueSetToNull($data, ['notificationtype'])` call:
 *     empty strings become null, EXCEPT for `notificationtype` (preserved as-is).
 *   - Injects `adsId => Ads` so ServiceContainer::prepareAttributes resolves the
 *     association.
 */
final class SendReservationRequestParser
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(SendReservationRequest $dto, Ads $ads): array
    {
        $data = [
            'firstname' => $dto->firstname,
            'lastname' => $dto->lastname,
            'email' => $dto->email,
            'mobile' => $dto->mobile,
            'viber' => $dto->viber,
            'note' => $dto->note,
            'adultnumber' => $dto->adultnumber,
            'childrennumber' => $dto->childrennumber,
            'checkin' => $dto->checkin,
            'checkout' => $dto->checkout,
            'notificationtype' => $dto->notificationtype,
        ];

        $this->emptyValueSetToNull($data, ['notificationtype']);

        $data['adsId'] = $ads;

        return $data;
    }

    /**
     * Port of SiteBundle\Controller\SiteController::emptyValueSetToNull.
     *
     * Coerces every empty value in $data to null, except for keys listed in
     * $excludedProps which are left untouched.
     *
     * @param array<string, mixed> $data
     * @param list<string>         $excludedProps
     */
    private function emptyValueSetToNull(array &$data, array $excludedProps = []): void
    {
        foreach ($data as $prop => &$item) {
            if (true === empty($item) && false === in_array($prop, $excludedProps, true)) {
                $item = null;
            }
        }
    }
}
