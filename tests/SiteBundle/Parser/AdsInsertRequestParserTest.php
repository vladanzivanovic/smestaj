<?php

declare(strict_types=1);

namespace SiteBundle\Tests\Parser;

use PHPUnit\Framework\TestCase;
use SiteBundle\Dto\Ads\AdsInsertRequest;
use SiteBundle\Parser\AdsInsertRequestParser;
use Symfony\Component\HttpFoundation\Request;

final class AdsInsertRequestParserTest extends TestCase
{
    public function testWrapsRequestBodyAndExtractsCsrfToken(): void
    {
        $parser = new AdsInsertRequestParser();
        $request = Request::create('/api/product', 'POST', [
            '_csrf_token' => 'abc',
            'title_rs' => 'x',
        ]);

        $dto = $parser->fromRequest($request);

        self::assertInstanceOf(AdsInsertRequest::class, $dto);
        self::assertSame($request->request, $dto->body);
        self::assertSame('abc', $dto->csrfToken);
        self::assertSame('x', $dto->body->get('title_rs'));
    }

    public function testEmptyCsrfTokenDefaultsToEmptyString(): void
    {
        $parser = new AdsInsertRequestParser();
        $request = Request::create('/api/product', 'POST', ['title_rs' => 'x']);

        $dto = $parser->fromRequest($request);

        self::assertSame('', $dto->csrfToken);
    }

    public function testCarriesAllBodyKeysThroughTheParameterBag(): void
    {
        $parser = new AdsInsertRequestParser();
        $request = Request::create('/api/product', 'POST', [
            '_csrf_token' => 'tok',
            'title_rs' => 'Title',
            'description_rs' => 'Description',
            'tags' => ['a', 'b'],
        ]);

        $dto = $parser->fromRequest($request);

        self::assertSame('Title', $dto->body->get('title_rs'));
        self::assertSame('Description', $dto->body->get('description_rs'));
        self::assertSame(['a', 'b'], $dto->body->all('tags'));
    }
}
