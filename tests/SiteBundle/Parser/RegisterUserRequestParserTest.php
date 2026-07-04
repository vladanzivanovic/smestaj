<?php

declare(strict_types=1);

namespace SiteBundle\Tests\Parser;

use PHPUnit\Framework\TestCase;
use SiteBundle\Dto\User\RegisterUserRequest;
use SiteBundle\Parser\RegisterUserRequestParser;
use Symfony\Component\HttpFoundation\Request;

final class RegisterUserRequestParserTest extends TestCase
{
    private RegisterUserRequestParser $parser;

    protected function setUp(): void
    {
        $this->parser = new RegisterUserRequestParser();
    }

    public function testToArrayProducesHandlerKeyShapeForRequiredFieldsOnly(): void
    {
        $dto = new RegisterUserRequest();
        $dto->email = 'user@example.com';
        $dto->password = 'secret123';
        $dto->firstname = 'First';
        $dto->lastname = 'Last';
        $dto->repassword = 'secret123';

        $data = $this->parser->toArray($dto);

        self::assertSame(
            [
                'email' => 'user@example.com',
                'password' => 'secret123',
                'firstname' => 'First',
                'lastname' => 'Last',
                'repassword' => 'secret123',
            ],
            $data,
        );
    }

    public function testToArrayIncludesFacebookIdWhenSet(): void
    {
        $dto = new RegisterUserRequest();
        $dto->email = 'fb@example.com';
        $dto->password = 'secret123';
        $dto->firstname = 'F';
        $dto->lastname = 'L';
        $dto->repassword = 'secret123';
        $dto->facebookId = 'fb-uid-42';

        $data = $this->parser->toArray($dto);

        self::assertArrayHasKey('facebookId', $data);
        self::assertSame('fb-uid-42', $data['facebookId']);
        self::assertArrayNotHasKey('facebookImage', $data);
    }

    public function testToArrayIncludesFacebookImageWhenSet(): void
    {
        $dto = new RegisterUserRequest();
        $dto->email = 'fb@example.com';
        $dto->password = 'secret123';
        $dto->firstname = 'F';
        $dto->lastname = 'L';
        $dto->repassword = 'secret123';
        $dto->facebookId = 'fb-uid-42';
        $dto->facebookImage = 'https://example.com/p.png';

        $data = $this->parser->toArray($dto);

        self::assertSame('https://example.com/p.png', $data['facebookImage']);
    }

    public function testToArrayOmitsFacebookKeysWhenNull(): void
    {
        $dto = new RegisterUserRequest();
        $dto->email = 'user@example.com';
        $dto->password = 'secret123';
        $dto->firstname = 'F';
        $dto->lastname = 'L';
        $dto->repassword = 'secret123';

        $data = $this->parser->toArray($dto);

        self::assertArrayNotHasKey('facebookId', $data);
        self::assertArrayNotHasKey('facebookImage', $data);
    }

    public function testFromRequestReturnsNullForEmptyRequest(): void
    {
        $request = new Request();

        self::assertNull($this->parser->fromRequest($request));
    }

    public function testFromRequestHydratesAllRequiredFields(): void
    {
        $request = Request::create('/', 'POST', [
            'email' => 'u@e.com',
            'password' => 'secret123',
            'firstname' => 'F',
            'lastname' => 'L',
            'repassword' => 'secret123',
        ]);

        $dto = $this->parser->fromRequest($request);

        self::assertInstanceOf(RegisterUserRequest::class, $dto);
        self::assertSame('u@e.com', $dto->email);
        self::assertSame('secret123', $dto->password);
        self::assertSame('F', $dto->firstname);
        self::assertSame('L', $dto->lastname);
        self::assertSame('secret123', $dto->repassword);
        self::assertNull($dto->facebookId);
        self::assertNull($dto->facebookImage);
    }

    public function testFromRequestHydratesOptionalFacebookFields(): void
    {
        $request = Request::create('/', 'POST', [
            'email' => 'fb@e.com',
            'password' => 'secret123',
            'firstname' => 'F',
            'lastname' => 'L',
            'repassword' => 'secret123',
            'facebookId' => 'fb-uid-42',
            'facebookImage' => 'https://fb.example/img.png',
        ]);

        $dto = $this->parser->fromRequest($request);

        self::assertInstanceOf(RegisterUserRequest::class, $dto);
        self::assertSame('fb-uid-42', $dto->facebookId);
        self::assertSame('https://fb.example/img.png', $dto->facebookImage);
    }

    public function testFromRequestHydratesEmptyStringWhenFieldOmitted(): void
    {
        $request = Request::create('/', 'POST', [
            'email' => 'u@e.com',
        ]);

        $dto = $this->parser->fromRequest($request);

        self::assertInstanceOf(RegisterUserRequest::class, $dto);
        self::assertSame('u@e.com', $dto->email);
        self::assertSame('', $dto->password);
        self::assertSame('', $dto->firstname);
        self::assertSame('', $dto->lastname);
        self::assertSame('', $dto->repassword);
        self::assertNull($dto->facebookId);
        self::assertNull($dto->facebookImage);
    }
}
