<?php

declare(strict_types=1);

namespace SiteBundle\Tests\Dto\Contact;

use PHPUnit\Framework\TestCase;
use SiteBundle\Dto\Contact\ContactRequestError;

final class ContactRequestErrorTest extends TestCase
{
    public function testStoresAllPropertiesVerbatim(): void
    {
        $error = new ContactRequestError(
            422,
            'Molimo proverite unete podatke.',
            ['email' => ['This value is not a valid email address.']],
        );

        self::assertSame(422, $error->statusCode);
        self::assertSame('Molimo proverite unete podatke.', $error->message);
        self::assertSame(
            ['email' => ['This value is not a valid email address.']],
            $error->errors,
        );
    }

    public function testErrorsDefaultsToNullWhenOmitted(): void
    {
        $error = new ContactRequestError(400, 'Invalid request payload.');

        self::assertSame(400, $error->statusCode);
        self::assertSame('Invalid request payload.', $error->message);
        self::assertNull($error->errors);
    }

    public function testStatusCodePropertyIsReadonly(): void
    {
        $error = new ContactRequestError(400, 'msg');

        $this->expectException(\Error::class);

        /** @phpstan-ignore-next-line readonly write attempt is the assertion */
        $error->statusCode = 500;
    }

    public function testMessagePropertyIsReadonly(): void
    {
        $error = new ContactRequestError(400, 'msg');

        $this->expectException(\Error::class);

        /** @phpstan-ignore-next-line readonly write attempt is the assertion */
        $error->message = 'mutated';
    }

    public function testErrorsPropertyIsReadonly(): void
    {
        $error = new ContactRequestError(422, 'msg', ['name' => ['x']]);

        $this->expectException(\Error::class);

        /** @phpstan-ignore-next-line readonly write attempt is the assertion */
        $error->errors = null;
    }
}
