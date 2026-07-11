<?php

declare(strict_types=1);

namespace SiteBundle\Dto\Contact;

/**
 * Mailer payload value object produced by ContactFormRequestParser::parse
 * from a validated ContactFormRequest. Consumed by ContactMailer::send.
 *
 * The `website` honeypot field on the request DTO is intentionally NOT
 * carried here — the honeypot check happens in the controller and short-
 * circuits the parse/send chain when triggered.
 */
final readonly class ContactSubmission
{
    public function __construct(
        public string $name,
        public string $email,
        public string $subject,
        public string $message,
    ) {
    }
}
