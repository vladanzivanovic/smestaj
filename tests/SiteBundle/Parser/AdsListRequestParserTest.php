<?php

declare(strict_types=1);

namespace SiteBundle\Tests\Parser;

use PHPUnit\Framework\TestCase;
use SiteBundle\Dto\Ads\AdsListRequest;
use SiteBundle\Parser\AdsListRequestParser;
use Symfony\Component\HttpFoundation\Request;

final class AdsListRequestParserTest extends TestCase
{
    public function testWrapsTheRequestQueryBag(): void
    {
        $parser = new AdsListRequestParser();
        $request = Request::create('/sobe-apartmani?city=budva&price=100');

        $dto = $parser->fromRequest($request, null);

        self::assertInstanceOf(AdsListRequest::class, $dto);
        self::assertSame($request->query, $dto->query);
        self::assertSame('budva', $dto->query->get('city'));
        self::assertSame('100', $dto->query->get('price'));
    }

    public function testForwardsExtraParams(): void
    {
        $parser = new AdsListRequestParser();
        $request = Request::create('/sobe-apartmani/budva');

        $dto = $parser->fromRequest($request, 'budva');

        self::assertSame('budva', $dto->extraParams);
    }

    public function testAcceptsNullExtraParams(): void
    {
        $parser = new AdsListRequestParser();
        $request = Request::create('/sobe-apartmani');

        $dto = $parser->fromRequest($request, null);

        self::assertNull($dto->extraParams);
    }
}
