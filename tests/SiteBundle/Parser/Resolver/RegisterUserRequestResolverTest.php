<?php

declare(strict_types=1);

namespace SiteBundle\Tests\Parser\Resolver;

use PHPUnit\Framework\TestCase;
use SiteBundle\Dto\User\RegisterUserRequest;
use SiteBundle\Parser\RegisterUserRequestParser;
use SiteBundle\Parser\Resolver\RegisterUserRequestResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class RegisterUserRequestResolverTest extends TestCase
{
    private RegisterUserRequestResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new RegisterUserRequestResolver(new RegisterUserRequestParser());
    }

    public function testReturnsEmptyForUnrelatedType(): void
    {
        $argument = new ArgumentMetadata('value', 'int', false, false, null);

        $resolved = iterator_to_array(
            $this->resolver->resolve(new Request(), $argument),
            false,
        );

        self::assertSame([], $resolved);
    }

    public function testYieldsRegisterUserRequestForPopulatedRequest(): void
    {
        $request = Request::create('/', 'POST', [
            'email' => 'u@e.com',
            'password' => 'secret123',
            'firstname' => 'F',
            'lastname' => 'L',
            'repassword' => 'secret123',
        ]);

        $argument = new ArgumentMetadata('dto', RegisterUserRequest::class, false, false, null);

        $resolved = iterator_to_array($this->resolver->resolve($request, $argument), false);

        self::assertCount(1, $resolved);
        self::assertInstanceOf(RegisterUserRequest::class, $resolved[0]);
        self::assertSame('u@e.com', $resolved[0]->email);
    }

    public function testYieldsNullForEmptyRequest(): void
    {
        $request = new Request();
        $argument = new ArgumentMetadata('dto', RegisterUserRequest::class, false, false, null);

        $resolved = iterator_to_array($this->resolver->resolve($request, $argument), false);

        self::assertCount(1, $resolved);
        self::assertNull($resolved[0]);
    }
}
