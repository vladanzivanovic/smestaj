<?php

declare(strict_types=1);

namespace SiteBundle\Tests\Parser\Resolver;

use PHPUnit\Framework\TestCase;
use SiteBundle\Dto\Ads\AdsInsertRequest;
use SiteBundle\Parser\AdsInsertRequestParser;
use SiteBundle\Parser\Resolver\AdsInsertRequestResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class AdsInsertRequestResolverTest extends TestCase
{
    private AdsInsertRequestResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new AdsInsertRequestResolver(new AdsInsertRequestParser());
    }

    public function testReturnsEmptyForUnrelatedType(): void
    {
        $argument = new ArgumentMetadata('value', 'int', false, false, null);

        $resolved = iterator_to_array(
            $this->resolver->resolve(Request::create('/api/product', 'POST'), $argument),
            false,
        );

        self::assertSame([], $resolved);
    }

    public function testYieldsAdsInsertRequestForMatchingType(): void
    {
        $request = Request::create('/api/product', 'POST', [
            '_csrf_token' => 'abc',
            'title_rs' => 'x',
        ]);
        $argument = new ArgumentMetadata('insertRequest', AdsInsertRequest::class, false, false, null);

        $resolved = iterator_to_array($this->resolver->resolve($request, $argument), false);

        self::assertCount(1, $resolved);
        self::assertInstanceOf(AdsInsertRequest::class, $resolved[0]);
        self::assertSame($request->request, $resolved[0]->body);
        self::assertSame('abc', $resolved[0]->csrfToken);
        self::assertSame('x', $resolved[0]->body->get('title_rs'));
    }

    public function testYieldsEmptyCsrfTokenWhenAbsent(): void
    {
        $request = Request::create('/api/product', 'POST', ['title_rs' => 'x']);
        $argument = new ArgumentMetadata('insertRequest', AdsInsertRequest::class, false, false, null);

        $resolved = iterator_to_array($this->resolver->resolve($request, $argument), false);

        self::assertCount(1, $resolved);
        self::assertSame('', $resolved[0]->csrfToken);
    }
}
