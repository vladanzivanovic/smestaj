<?php

declare(strict_types=1);

namespace SiteBundle\Tests\Parser;

use PHPUnit\Framework\TestCase;
use SiteBundle\Parser\ContactFormRequestParser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ContactFormRequestParserTest extends TestCase
{
    private ContactFormRequestParser $parser;

    protected function setUp(): void
    {
        $this->parser = new ContactFormRequestParser(
            $this->buildValidator(),
            $this->buildTranslator(),
        );
    }

    public function testValidPayloadProducesNoError(): void
    {
        $request = $this->buildJsonRequest([
            'name' => 'John',
            'email' => 'john@example.com',
            'subject' => 'Hello',
            'message' => 'This is a test message body.',
        ]);

        $dto = $this->parser->fromRequest($request);

        self::assertNull($dto->error);
        self::assertSame('John', $dto->name);
        self::assertSame('john@example.com', $dto->email);
        self::assertSame('Hello', $dto->subject);
        self::assertSame('This is a test message body.', $dto->message);
        self::assertSame('', $dto->website);
    }

    public function testWrongContentTypeProducesBadRequest(): void
    {
        $request = Request::create(
            '/api/contact-us',
            'POST',
            server: ['CONTENT_TYPE' => 'text/plain'],
            content: 'whatever',
        );

        $dto = $this->parser->fromRequest($request);

        self::assertNotNull($dto->error);
        self::assertSame(400, $dto->error->statusCode);
        self::assertSame('Invalid request payload.', $dto->error->message);
        self::assertNull($dto->error->errors);
    }

    public function testMalformedJsonProducesBadRequest(): void
    {
        $request = Request::create(
            '/api/contact-us',
            'POST',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: 'not json',
        );

        $dto = $this->parser->fromRequest($request);

        self::assertNotNull($dto->error);
        self::assertSame(400, $dto->error->statusCode);
        self::assertSame('Invalid request payload.', $dto->error->message);
        self::assertNull($dto->error->errors);
    }

    public function testNonArrayPayloadProducesBadRequest(): void
    {
        $request = Request::create(
            '/api/contact-us',
            'POST',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: '"just a string"',
        );

        $dto = $this->parser->fromRequest($request);

        self::assertNotNull($dto->error);
        self::assertSame(400, $dto->error->statusCode);
        self::assertSame('Invalid request payload.', $dto->error->message);
        self::assertNull($dto->error->errors);
    }

    public function testMissingRequiredFieldsProducesValidationError(): void
    {
        $request = $this->buildJsonRequest([]);

        $dto = $this->parser->fromRequest($request);

        self::assertNotNull($dto->error);
        self::assertSame(422, $dto->error->statusCode);
        self::assertSame('Molimo proverite unete podatke.', $dto->error->message);
        self::assertNotNull($dto->error->errors);
        self::assertArrayHasKey('name', $dto->error->errors);
        self::assertArrayHasKey('email', $dto->error->errors);
        self::assertArrayHasKey('subject', $dto->error->errors);
        self::assertArrayHasKey('message', $dto->error->errors);
    }

    public function testInvalidEmailProducesValidationError(): void
    {
        $request = $this->buildJsonRequest([
            'name' => 'John',
            'email' => 'not-an-email',
            'subject' => 'Hello',
            'message' => 'This is a test message body.',
        ]);

        $dto = $this->parser->fromRequest($request);

        self::assertNotNull($dto->error);
        self::assertSame(422, $dto->error->statusCode);
        self::assertNotNull($dto->error->errors);
        self::assertArrayHasKey('email', $dto->error->errors);
    }

    public function testHoneypotValuePassesThroughWithoutError(): void
    {
        $request = $this->buildJsonRequest([
            'name' => 'John',
            'email' => 'john@example.com',
            'subject' => 'Hello',
            'message' => 'This is a test message body.',
            'website' => 'spam',
        ]);

        $dto = $this->parser->fromRequest($request);

        self::assertNull($dto->error);
        self::assertSame('spam', $dto->website);
    }

    public function testSurroundingWhitespaceIsTrimmed(): void
    {
        $request = $this->buildJsonRequest([
            'name' => '  John  ',
            'email' => '  john@example.com  ',
            'subject' => '  Hello  ',
            'message' => '  This is a test message body.  ',
        ]);

        $dto = $this->parser->fromRequest($request);

        self::assertNull($dto->error);
        self::assertSame('John', $dto->name);
        self::assertSame('john@example.com', $dto->email);
        self::assertSame('Hello', $dto->subject);
        self::assertSame('This is a test message body.', $dto->message);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function buildJsonRequest(array $payload): Request
    {
        return Request::create(
            '/api/contact-us',
            'POST',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode($payload, \JSON_THROW_ON_ERROR),
        );
    }

    private function buildValidator(): ValidatorInterface
    {
        return Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    private function buildTranslator(): TranslatorInterface
    {
        return new class implements TranslatorInterface {
            public function trans(string $id, array $parameters = [], ?string $domain = null, ?string $locale = null): string
            {
                return $id;
            }

            public function getLocale(): string
            {
                return 'rs';
            }
        };
    }
}
