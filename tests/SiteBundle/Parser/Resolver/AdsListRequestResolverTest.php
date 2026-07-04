<?php

declare(strict_types=1);

namespace SiteBundle\Tests\Parser\Resolver;

use PHPUnit\Framework\TestCase;
use SiteBundle\Dto\Ads\AdsListRequest;
use SiteBundle\Parser\AdsListRequestParser;
use SiteBundle\Parser\Resolver\AdsListRequestResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class AdsListRequestResolverTest extends TestCase
{
    private AdsListRequestResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new AdsListRequestResolver(new AdsListRequestParser());
    }

    public function testReturnsEmptyForUnrelatedType(): void
    {
        $argument = new ArgumentMetadata('value', 'int', false, false, null);

        $resolved = iterator_to_array(
            $this->resolver->resolve(Request::create('/sobe-apartmani'), $argument),
            false,
        );

        self::assertSame([], $resolved);
    }

    public function testYieldsAdsListRequestForMatchingType(): void
    {
        $request = Request::create('/sobe-apartmani?city=budva&price=100');
        $argument = new ArgumentMetadata('listRequest', AdsListRequest::class, false, false, null);

        $resolved = iterator_to_array($this->resolver->resolve($request, $argument), false);

        self::assertCount(1, $resolved);
        self::assertInstanceOf(AdsListRequest::class, $resolved[0]);
        self::assertSame($request->query, $resolved[0]->query);
    }

    public function testPullsExtraParamsFromRequestAttributes(): void
    {
        $request = Request::create('/sobe-apartmani/budva');
        $request->attributes->set('extraParams', 'budva');

        $argument = new ArgumentMetadata('listRequest', AdsListRequest::class, false, false, null);

        $resolved = iterator_to_array($this->resolver->resolve($request, $argument), false);

        self::assertCount(1, $resolved);
        self::assertSame('budva', $resolved[0]->extraParams);
    }

    public function testYieldsNullExtraParamsWhenAttributeMissing(): void
    {
        $request = Request::create('/sobe-apartmani');

        $argument = new ArgumentMetadata('listRequest', AdsListRequest::class, false, false, null);

        $resolved = iterator_to_array($this->resolver->resolve($request, $argument), false);

        self::assertCount(1, $resolved);
        self::assertNull($resolved[0]->extraParams);
    }

    public function testIgnoresNonStringExtraParamsAttribute(): void
    {
        $request = Request::create('/sobe-apartmani');
        $request->attributes->set('extraParams', 123);

        $argument = new ArgumentMetadata('listRequest', AdsListRequest::class, false, false, null);

        $resolved = iterator_to_array($this->resolver->resolve($request, $argument), false);

        self::assertCount(1, $resolved);
        self::assertNull($resolved[0]->extraParams);
    }
}
