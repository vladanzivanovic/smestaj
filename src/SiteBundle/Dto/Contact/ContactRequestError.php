<?php

declare(strict_types=1);

namespace SiteBundle\Dto\Contact;

final readonly class ContactRequestError
{
    /**
     * @param array<string, array<int, string>>|null $errors
     */
    public function __construct(
        public int $statusCode,
        public string $message,
        public ?array $errors = null,
    ) {
    }
}
