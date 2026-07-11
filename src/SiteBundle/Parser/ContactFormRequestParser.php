<?php

declare(strict_types=1);

namespace SiteBundle\Parser;

use SiteBundle\Dto\Contact\ContactSubmission;
use SiteBundle\Dto\ContactFormRequest;

final class ContactFormRequestParser
{
    /**
     * Build the mailer payload value object from a validated contact form DTO.
     * All validation + malformed-body handling is done by the framework's
     * MapRequestPayload attribute + PayloadMappingFailureListener.
     */
    public function parse(ContactFormRequest $dto): ContactSubmission
    {
        return new ContactSubmission(
            trim($dto->name),
            trim($dto->email),
            trim($dto->subject),
            trim($dto->message),
        );
    }
}
