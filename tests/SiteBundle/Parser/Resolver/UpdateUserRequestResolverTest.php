<?php

declare(strict_types=1);

namespace SiteBundle\Tests\Parser\Resolver;

use PHPUnit\Framework\TestCase;
use SiteBundle\Dto\User\UpdateUserRequest;
use SiteBundle\Parser\Resolver\UpdateUserRequestResolver;
use SiteBundle\Parser\UpdateUserRequestParser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class UpdateUserRequestResolverTest extends TestCase
{
    private UpdateUserRequestResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new UpdateUserRequestResolver(new UpdateUserRequestParser());
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

    public function testYieldsUpdateUserRequestForAnyRequest(): void
    {
        $argument = new ArgumentMetadata('dto', UpdateUserRequest::class, false, false, null);

        $resolvedEmpty = iterator_to_array($this->resolver->resolve(new Request(), $argument), false);

        self::assertCount(1, $resolvedEmpty);
        self::assertInstanceOf(UpdateUserRequest::class, $resolvedEmpty[0]);

        $populated = Request::create('/', 'POST', ['firstname' => 'New']);
        $resolvedPopulated = iterator_to_array($this->resolver->resolve($populated, $argument), false);

        self::assertCount(1, $resolvedPopulated);
        self::assertInstanceOf(UpdateUserRequest::class, $resolvedPopulated[0]);
        self::assertSame('New', $resolvedPopulated[0]->firstname);
    }
}
