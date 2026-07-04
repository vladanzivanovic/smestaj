<?php

declare(strict_types=1);

namespace SiteBundle\Tests\Dto;

use PHPUnit\Framework\TestCase;
use SiteBundle\Dto\Contact\ContactRequestError;
use SiteBundle\Dto\ContactFormRequest;
use Symfony\Component\Validator\Constraints as Assert;

final class ContactFormRequestTest extends TestCase
{
    public function testDefaultValuesForEveryProperty(): void
    {
        $dto = new ContactFormRequest();

        self::assertSame('', $dto->name);
        self::assertSame('', $dto->email);
        self::assertSame('', $dto->subject);
        self::assertSame('', $dto->message);
        self::assertSame('', $dto->website);
        self::assertNull($dto->error);
    }

    public function testErrorPropertyAcceptsContactRequestError(): void
    {
        $dto = new ContactFormRequest();
        $error = new ContactRequestError(400, 'Invalid request payload.');

        $dto->error = $error;

        self::assertSame($error, $dto->error);
    }

    public function testWebsitePropertyHasNoAssertConstraints(): void
    {
        $reflection = new \ReflectionProperty(ContactFormRequest::class, 'website');

        $assertAttributes = [];

        foreach ($reflection->getAttributes() as $attribute) {
            $attributeName = $attribute->getName();

            if (true === str_starts_with($attributeName, 'Symfony\\Component\\Validator\\Constraints\\')) {
                $assertAttributes[] = $attributeName;
            }
        }

        self::assertSame([], $assertAttributes, 'website must have no Assert constraints (honeypot policy lives in controller).');
        self::assertNotContains(Assert\Blank::class, $assertAttributes);
    }

    public function testErrorPropertyHasNoAssertConstraints(): void
    {
        $reflection = new \ReflectionProperty(ContactFormRequest::class, 'error');

        $assertAttributes = [];

        foreach ($reflection->getAttributes() as $attribute) {
            $attributeName = $attribute->getName();

            if (true === str_starts_with($attributeName, 'Symfony\\Component\\Validator\\Constraints\\')) {
                $assertAttributes[] = $attributeName;
            }
        }

        self::assertSame([], $assertAttributes, 'error must have no Assert constraints (populated by Parser, not by inbound payload).');
    }
}
