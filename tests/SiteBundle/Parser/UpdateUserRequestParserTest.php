<?php

declare(strict_types=1);

namespace SiteBundle\Tests\Parser;

use PHPUnit\Framework\TestCase;
use SiteBundle\Dto\User\UpdateUserRequest;
use SiteBundle\Parser\UpdateUserRequestParser;
use Symfony\Component\HttpFoundation\Request;

final class UpdateUserRequestParserTest extends TestCase
{
    private UpdateUserRequestParser $parser;

    protected function setUp(): void
    {
        $this->parser = new UpdateUserRequestParser();
    }

    public function testToArrayReturnsEmptyArrayForAllNullDto(): void
    {
        $dto = new UpdateUserRequest();

        $data = $this->parser->toArray($dto);

        self::assertSame([], $data);
    }

    public function testToArrayIncludesOnlyNonNullProperties(): void
    {
        $dto = new UpdateUserRequest();
        $dto->firstname = 'NewFirst';

        $data = $this->parser->toArray($dto);

        self::assertSame(['firstname' => 'NewFirst'], $data);
    }

    public function testToArrayProducesHandlerKeyShapeForFullDto(): void
    {
        $dto = new UpdateUserRequest();
        $dto->email = 'user@example.com';
        $dto->password = 'newSecret';
        $dto->firstname = 'First';
        $dto->lastname = 'Last';
        $dto->repassword = 'newSecret';

        $data = $this->parser->toArray($dto);

        self::assertSame(
            [
                'email' => 'user@example.com',
                'password' => 'newSecret',
                'firstname' => 'First',
                'lastname' => 'Last',
                'repassword' => 'newSecret',
            ],
            $data,
        );
    }

    public function testToArrayPreservesEmptyStringValuesAsPresent(): void
    {
        $dto = new UpdateUserRequest();
        $dto->firstname = '';

        $data = $this->parser->toArray($dto);

        self::assertArrayHasKey('firstname', $data);
        self::assertSame('', $data['firstname']);
    }

    public function testFromRequestReturnsDtoWithAllNullsForEmptyRequest(): void
    {
        $request = new Request();

        $dto = $this->parser->fromRequest($request);

        self::assertInstanceOf(UpdateUserRequest::class, $dto);
        self::assertNull($dto->email);
        self::assertNull($dto->password);
        self::assertNull($dto->firstname);
        self::assertNull($dto->lastname);
        self::assertNull($dto->repassword);
    }

    public function testFromRequestHydratesProvidedFieldsAndLeavesOmittedAsNull(): void
    {
        $request = Request::create('/', 'POST', [
            'firstname' => 'New',
            'email' => 'u@e.com',
        ]);

        $dto = $this->parser->fromRequest($request);

        self::assertSame('New', $dto->firstname);
        self::assertSame('u@e.com', $dto->email);
        self::assertNull($dto->password);
        self::assertNull($dto->lastname);
        self::assertNull($dto->repassword);
    }

    public function testFromRequestHydratesEmptyStringWhenFieldIsSubmittedBlank(): void
    {
        $request = Request::create('/', 'POST', [
            'firstname' => '',
        ]);

        $dto = $this->parser->fromRequest($request);

        self::assertSame('', $dto->firstname);
    }
}
