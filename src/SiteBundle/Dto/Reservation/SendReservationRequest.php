<?php

declare(strict_types=1);

namespace SiteBundle\Dto\Reservation;

use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Consumed by SiteBundle\Handler\UserReservationHandler::setReservation(array)
 * via SiteBundle\Services\ServiceContainer::arrayToEntity(Reservation::class).
 *
 * Array keys produced by the matching parser (mirror the legacy reservation form
 * payload at src/SiteBundle/Resources/views/Moduls/AdsInfo/reservation.html.twig):
 *   - firstname        (required; Reservation::setFirstname, non-null string column)
 *   - lastname         (required; Reservation::setLastname, non-null string column)
 *   - email            (form-required, entity-nullable; Reservation::setEmail)
 *   - mobile           (required; Reservation::setMobile, non-null string column)
 *   - viber            (optional; Reservation::setViber, nullable string column)
 *   - note             (optional; Reservation::setNote, nullable text column)
 *   - adultnumber      (required; Reservation::setAdultnumber, int column)
 *   - childrennumber   (optional; Reservation::setChildrennumber, nullable int column)
 *   - checkin          (required; Reservation::setCheckin, date column — string is
 *                       converted by ServiceContainer::prepareAttributes via
 *                       `new \DateTime($fieldValue)`)
 *   - checkout         (required; Reservation::setCheckout, date column)
 *   - notificationtype (required; Reservation::setNotificationType, int column —
 *                       PHP method dispatch is case-insensitive, so `setNotificationtype`
 *                       and `setNotificationType` resolve to the same method)
 *   - adsId            (injected by the parser from the MapEntity-resolved Ads object,
 *                       NOT from the form's hidden `adsid` field; overrides any
 *                       inbound `adsid` because PHP method dispatch is case-insensitive
 *                       and `adsId` is iterated after `adsid` in the parser output)
 *
 * The `$csrfToken` property carries the CSRF token — wire key is `token`
 * (preserved via `#[SerializedName('token')]`; the form emits
 * `<input name="token" value="{{ csrf_token('ad_reservation') }}">`). CSRF
 * validation stays in the controller (per CONTEXT_SPEC decision #6 — token
 * VALUE travels via DTO, VALIDATION stays in controller). The parser does
 * NOT include the token in the array returned for the handler.
 *
 * Empty-value-to-null normalization (port of SiteController::emptyValueSetToNull)
 * happens inside the parser, EXCEPT for `notificationtype` (preserved as-is —
 * matches the legacy `$this->emptyValueSetToNull($data, ['notificationtype'])`
 * call in the original controller).
 */
final class SendReservationRequest
{
    #[Assert\NotBlank]
    #[SerializedName('token')]
    public string $csrfToken = '';

    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    public string $firstname = '';

    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    public string $lastname = '';

    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 255)]
    public string $email = '';

    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    public string $mobile = '';

    #[Assert\Length(max: 100)]
    public ?string $viber = null;

    public ?string $note = null;

    #[Assert\NotBlank]
    public int $adultnumber = 0;

    public ?string $childrennumber = null;

    #[Assert\NotBlank]
    public string $checkin = '';

    #[Assert\NotBlank]
    public string $checkout = '';

    #[Assert\NotBlank]
    public ?int $notificationtype = null;
}
