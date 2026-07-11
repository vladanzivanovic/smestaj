<?php

declare(strict_types=1);

namespace SiteBundle\Tests\Parser;

use PHPUnit\Framework\TestCase;
use SiteBundle\Dto\Contact\ContactSubmission;
use SiteBundle\Dto\ContactFormRequest;
use SiteBundle\Parser\ContactFormRequestParser;

final class ContactFormRequestParserTest extends TestCase
{
    private ContactFormRequestParser $parser;

    protected function setUp(): void
    {
        $this->parser = new ContactFormRequestParser();
    }

    public function testProducesMailerPayloadFromValidatedDto(): void
    {
        $dto = new ContactFormRequest();
        $dto->name = 'John';
        $dto->email = 'john@example.com';
        $dto->subject = 'Hello';
        $dto->message = 'This is a test message body.';

        $submission = $this->parser->parse($dto);

        self::assertInstanceOf(ContactSubmission::class, $submission);
        self::assertSame('John', $submission->name);
        self::assertSame('john@example.com', $submission->email);
        self::assertSame('Hello', $submission->subject);
        self::assertSame('This is a test message body.', $submission->message);
    }

    public function testTrimsSurroundingWhitespaceOnEveryField(): void
    {
        $dto = new ContactFormRequest();
        $dto->name = '  John  ';
        $dto->email = '  john@example.com  ';
        $dto->subject = '  Hello  ';
        $dto->message = '  This is a test message body.  ';

        $submission = $this->parser->parse($dto);

        self::assertSame('John', $submission->name);
        self::assertSame('john@example.com', $submission->email);
        self::assertSame('Hello', $submission->subject);
        self::assertSame('This is a test message body.', $submission->message);
    }

    public function testHoneypotFieldIsNotCarriedIntoSubmission(): void
    {
        $dto = new ContactFormRequest();
        $dto->name = 'John';
        $dto->email = 'john@example.com';
        $dto->subject = 'Hello';
        $dto->message = 'This is a test message body.';
        $dto->website = 'spam';

        $submission = $this->parser->parse($dto);

        self::assertObjectNotHasProperty('website', $submission);
    }
}
