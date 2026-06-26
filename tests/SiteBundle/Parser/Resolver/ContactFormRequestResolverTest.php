<?php

declare(strict_types=1);

namespace SiteBundle\Tests\Parser\Resolver;

use PHPUnit\Framework\TestCase;
use SiteBundle\Dto\ContactFormRequest;
use SiteBundle\Parser\ContactFormRequestParser;
use SiteBundle\Parser\Resolver\ContactFormRequestResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ContactFormRequestResolverTest extends TestCase
{
    private ContactFormRequestResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new ContactFormRequestResolver(
            new ContactFormRequestParser(
                $this->buildValidator(),
                $this->buildTranslator(),
            ),
        );
    }

    public function testReturnsEmptyForUnrelatedType(): void
    {
        $argument = new ArgumentMetadata('value', 'int', false, false, null);

        $resolved = iterator_to_array($this->resolver->resolve(Request::create('/api/contact-us'), $argument), false);

        self::assertSame([], $resolved);
    }

    public function testYieldsSingleDtoForContactFormRequestType(): void
    {
        $request = $this->buildJsonRequest([
            'name' => 'John',
            'email' => 'john@example.com',
            'subject' => 'Hello',
            'message' => 'This is a test message body.',
        ]);

        $argument = new ArgumentMetadata('dto', ContactFormRequest::class, false, false, null);

        $resolved = iterator_to_array($this->resolver->resolve($request, $argument), false);

        self::assertCount(1, $resolved);
        self::assertInstanceOf(ContactFormRequest::class, $resolved[0]);
        self::assertNull($resolved[0]->error);
        self::assertSame('John', $resolved[0]->name);
    }

    public function testDelegatesParsingOfTheGivenRequest(): void
    {
        $request = $this->buildJsonRequest([
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'subject' => 'Delegation marker',
            'message' => 'Verifies that the resolver hands THIS request to the parser.',
        ]);

        $argument = new ArgumentMetadata('dto', ContactFormRequest::class, false, false, null);

        $resolved = iterator_to_array($this->resolver->resolve($request, $argument), false);

        self::assertCount(1, $resolved);
        self::assertSame('Alice', $resolved[0]->name);
        self::assertSame('alice@example.com', $resolved[0]->email);
        self::assertSame('Delegation marker', $resolved[0]->subject);
    }

    public function testPreservesErrorOnReturnedDto(): void
    {
        $request = Request::create(
            '/api/contact-us',
            'POST',
            server: ['CONTENT_TYPE' => 'text/plain'],
            content: 'not json at all',
        );

        $argument = new ArgumentMetadata('dto', ContactFormRequest::class, false, false, null);

        $resolved = iterator_to_array($this->resolver->resolve($request, $argument), false);

        self::assertCount(1, $resolved);
        self::assertNotNull($resolved[0]->error);
        self::assertSame(400, $resolved[0]->error->statusCode);
        self::assertSame('Invalid request payload.', $resolved[0]->error->message);
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
