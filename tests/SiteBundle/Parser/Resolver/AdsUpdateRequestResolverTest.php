<?php

declare(strict_types=1);

namespace SiteBundle\Tests\Parser\Resolver;

use PHPUnit\Framework\TestCase;
use SiteBundle\Dto\Ads\AdsUpdateRequest;
use SiteBundle\Parser\AdsUpdateRequestParser;
use SiteBundle\Parser\Resolver\AdsUpdateRequestResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class AdsUpdateRequestResolverTest extends TestCase
{
    private AdsUpdateRequestResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new AdsUpdateRequestResolver(new AdsUpdateRequestParser());
    }

    public function testReturnsEmptyForUnrelatedType(): void
    {
        $argument = new ArgumentMetadata('value', 'int', false, false, null);

        $resolved = iterator_to_array(
            $this->resolver->resolve(Request::create('/product/1', 'PUT'), $argument),
            false,
        );

        self::assertSame([], $resolved);
    }

    public function testYieldsAdsUpdateRequestForMatchingType(): void
    {
        $request = Request::create('/product/1', 'PUT', [
            '_csrf_token' => 'abc',
            'title_rs' => 'x',
        ]);
        $argument = new ArgumentMetadata('updateRequest', AdsUpdateRequest::class, false, false, null);

        $resolved = iterator_to_array($this->resolver->resolve($request, $argument), false);

        self::assertCount(1, $resolved);
        self::assertInstanceOf(AdsUpdateRequest::class, $resolved[0]);
        self::assertSame($request->request, $resolved[0]->body);
        self::assertSame('abc', $resolved[0]->csrfToken);
        self::assertSame('x', $resolved[0]->body->get('title_rs'));
    }

    public function testYieldsEmptyCsrfTokenWhenAbsent(): void
    {
        $request = Request::create('/product/1', 'PUT', ['title_rs' => 'x']);
        $argument = new ArgumentMetadata('updateRequest', AdsUpdateRequest::class, false, false, null);

        $resolved = iterator_to_array($this->resolver->resolve($request, $argument), false);

        self::assertCount(1, $resolved);
        self::assertSame('', $resolved[0]->csrfToken);
    }
}
