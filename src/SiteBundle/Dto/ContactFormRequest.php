<?php

declare(strict_types=1);

namespace SiteBundle\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * JSON payload for POST /api/contact-us (route `site_send_contact_us`).
 * Populated by Symfony's #[MapRequestPayload] from the JSON body submitted
 * by the site contact form.
 *
 * On validation failure PayloadMappingFailureListener maps
 * `site_send_contact_us` to formatContactShape():
 *   - malformed JSON / non-JSON content-type ⇒ 400 {status:'error', message:'Invalid request payload.'}
 *   - Assert violations ⇒ 422 {status:'error', message:<firstMessage>, errors:{<field>: <message>}}
 * — the wire shape matches the pre-migration parser-driven error path
 * byte-for-byte.
 *
 * The `website` property is the honeypot field checked inline in the
 * controller (non-empty ⇒ silent 204 no-content). No CSRF today.
 */
final class ContactFormRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 120)]
    public string $name = '';

    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 180)]
    public string $email = '';

    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 200)]
    public string $subject = '';

    #[Assert\NotBlank]
    #[Assert\Length(min: 5, max: 5000)]
    public string $message = '';

    public string $website = '';
}
