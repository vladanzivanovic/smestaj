<?php

declare(strict_types=1);

namespace SiteBundle\Tests\Parser;

use PHPUnit\Framework\TestCase;
use SiteBundle\Dto\Ads\AdsListRequest;
use SiteBundle\Dto\Ads\AdsSearchCriteria;
use SiteBundle\Parser\AdsListRequestParser;

final class AdsListRequestParserTest extends TestCase
{
    private AdsListRequestParser $parser;

    protected function setUp(): void
    {
        $this->parser = new AdsListRequestParser();
    }

    public function testEmptyDtoProducesEmptyCriteria(): void
    {
        $criteria = $this->parser->parse(new AdsListRequest());

        self::assertInstanceOf(AdsSearchCriteria::class, $criteria);
        self::assertNull($criteria->page);
        self::assertNull($criteria->sort);
        self::assertSame([], $criteria->tags);
        self::assertSame([], $criteria->city);
        self::assertSame([], $criteria->categories);
        self::assertSame([], $criteria->size);
        self::assertSame([], $criteria->color);
        self::assertSame([], $criteria->price);
    }

    public function testCarriesPageAndSortScalarsFromDto(): void
    {
        $dto = new AdsListRequest();
        $dto->page = 3;
        $dto->sort = 'najnovije';

        $criteria = $this->parser->parse($dto);

        self::assertSame(3, $criteria->page);
        self::assertSame('najnovije', $criteria->sort);
    }

    public function testNormalizesArrayShapedListFields(): void
    {
        $dto = new AdsListRequest();
        $dto->tags = ['1', '2', '3'];
        $dto->city = ['Beograd', 'Novi Sad'];
        $dto->categories = ['apartman'];
        $dto->size = ['M', 'L'];
        $dto->color = ['crvena'];
        $dto->price = ['0-100'];

        $criteria = $this->parser->parse($dto);

        self::assertSame(['1', '2', '3'], $criteria->tags);
        self::assertSame(['Beograd', 'Novi Sad'], $criteria->city);
        self::assertSame(['apartman'], $criteria->categories);
        self::assertSame(['M', 'L'], $criteria->size);
        self::assertSame(['crvena'], $criteria->color);
        self::assertSame(['0-100'], $criteria->price);
    }

    public function testNormalizesCsvStringListFieldsIntoArrays(): void
    {
        $dto = new AdsListRequest();
        $dto->tags = '1,2,3';
        $dto->city = 'Beograd';

        $criteria = $this->parser->parse($dto);

        self::assertSame(['1', '2', '3'], $criteria->tags);
        self::assertSame(['Beograd'], $criteria->city);
    }

    public function testDropsBlankAndWhitespaceOnlyEntries(): void
    {
        $dto = new AdsListRequest();
        $dto->tags = ['1', '', '  ', '2'];

        $criteria = $this->parser->parse($dto);

        self::assertSame(['1', '2'], $criteria->tags);
    }
}
