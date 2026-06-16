<?php

declare(strict_types=1);

namespace SiteBundle\Tests\Parser;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use SiteBundle\Entity\AdsInfoHasTag;
use SiteBundle\Entity\AdsInfoPage;
use SiteBundle\Entity\Tag;
use SiteBundle\Parser\InfoPageTagParser;
use SiteBundle\Repository\TagRepository;

final class InfoPageTagParserTest extends TestCase
{
    private function buildParser(array $tagIdToTagMap): InfoPageTagParser
    {
        $repository = $this->createMock(TagRepository::class);
        $repository->method('find')->willReturnCallback(
            static function (int $id) use ($tagIdToTagMap): ?Tag {
                if (false === array_key_exists($id, $tagIdToTagMap)) {
                    return null;
                }

                return $tagIdToTagMap[$id];
            },
        );

        return new InfoPageTagParser($repository);
    }

    private function makeTag(int $id): Tag
    {
        $tag = new Tag();
        $reflection = new ReflectionClass($tag);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($tag, $id);

        return $tag;
    }

    private function makeRow(AdsInfoPage $page, Tag $tag, string $value): AdsInfoHasTag
    {
        $row = new AdsInfoHasTag();
        $row->setInfoPage($page)
            ->setTag($tag)
            ->setValue($value);
        $page->addHasTag($row);

        return $row;
    }

    private function buildTagMap(int ...$ids): array
    {
        $map = [];

        foreach ($ids as $id) {
            $map[$id] = $this->makeTag($id);
        }

        return $map;
    }

    public function testEmptyInputOnEmptyEntityProducesEmptyCollection(): void
    {
        $page = new AdsInfoPage();
        $parser = $this->buildParser([]);

        $parser->parse($page, []);

        self::assertSame(0, $page->getHasTags()->count());
    }

    public function testEmptyInputClearsExistingCollection(): void
    {
        $page = new AdsInfoPage();
        $tagMap = $this->buildTagMap(3, 7);
        $this->makeRow($page, $tagMap[3], '1');
        $this->makeRow($page, $tagMap[7], '1');

        $parser = $this->buildParser($tagMap);

        $parser->parse($page, []);

        self::assertSame(0, $page->getHasTags()->count());
    }

    public function testNewTagsInsertOnEmptyEntity(): void
    {
        $page = new AdsInfoPage();
        $tagMap = $this->buildTagMap(3, 7);
        $parser = $this->buildParser($tagMap);

        $parser->parse($page, ['amenity' => [3 => '1', 7 => '1']]);

        self::assertSame(2, $page->getHasTags()->count());

        $byTagId = [];

        foreach ($page->getHasTags() as $row) {
            $byTagId[$row->getTag()->getId()] = $row->getValue();
        }

        self::assertSame(['1', '1'], [$byTagId[3], $byTagId[7]]);
    }

    public function testIdempotentResaveIsAnInProcessNoOp(): void
    {
        $page = new AdsInfoPage();
        $tagMap = $this->buildTagMap(3, 7);
        $existingRow3 = $this->makeRow($page, $tagMap[3], '1');
        $existingRow7 = $this->makeRow($page, $tagMap[7], '1');
        $existingRow3Id = spl_object_id($existingRow3);
        $existingRow7Id = spl_object_id($existingRow7);

        $parser = $this->buildParser($tagMap);

        $parser->parse($page, ['amenity' => [3 => '1', 7 => '1']]);

        self::assertSame(2, $page->getHasTags()->count());

        $survivors = [];

        foreach ($page->getHasTags() as $row) {
            $survivors[$row->getTag()->getId()] = spl_object_id($row);
            self::assertSame('1', $row->getValue());
        }

        self::assertSame($existingRow3Id, $survivors[3]);
        self::assertSame($existingRow7Id, $survivors[7]);
    }

    public function testPartialAddPreservesExistingAndInsertsNew(): void
    {
        $page = new AdsInfoPage();
        $tagMap = $this->buildTagMap(3, 7, 11);
        $existingRow3 = $this->makeRow($page, $tagMap[3], '1');
        $existingRow7 = $this->makeRow($page, $tagMap[7], '1');
        $existingRow3Id = spl_object_id($existingRow3);
        $existingRow7Id = spl_object_id($existingRow7);

        $parser = $this->buildParser($tagMap);

        $parser->parse($page, ['amenity' => [3 => '1', 7 => '1', 11 => '1']]);

        self::assertSame(3, $page->getHasTags()->count());

        $rowsByTagId = [];

        foreach ($page->getHasTags() as $row) {
            $rowsByTagId[$row->getTag()->getId()] = $row;
        }

        self::assertSame($existingRow3Id, spl_object_id($rowsByTagId[3]));
        self::assertSame($existingRow7Id, spl_object_id($rowsByTagId[7]));
        self::assertNotSame($existingRow3Id, spl_object_id($rowsByTagId[11]));
        self::assertNotSame($existingRow7Id, spl_object_id($rowsByTagId[11]));
        self::assertSame('1', $rowsByTagId[11]->getValue());
    }

    public function testPartialRemoveDropsAbsentTags(): void
    {
        $page = new AdsInfoPage();
        $tagMap = $this->buildTagMap(3, 7, 11);
        $existingRow3 = $this->makeRow($page, $tagMap[3], '1');
        $this->makeRow($page, $tagMap[7], '1');
        $this->makeRow($page, $tagMap[11], '1');
        $existingRow3Id = spl_object_id($existingRow3);

        $parser = $this->buildParser($tagMap);

        $parser->parse($page, ['amenity' => [3 => '1']]);

        self::assertSame(1, $page->getHasTags()->count());

        $remaining = $page->getHasTags()->first();
        self::assertInstanceOf(AdsInfoHasTag::class, $remaining);
        self::assertSame(3, $remaining->getTag()->getId());
        self::assertSame($existingRow3Id, spl_object_id($remaining));
    }

    public function testDuplicateInputCollapsesLastWriteWins(): void
    {
        $page = new AdsInfoPage();
        $tagMap = $this->buildTagMap(3);
        $parser = $this->buildParser($tagMap);

        $parser->parse($page, ['amenity' => [3 => 'first'], 'range' => [3 => 'second']]);

        self::assertSame(1, $page->getHasTags()->count());

        $only = $page->getHasTags()->first();
        self::assertInstanceOf(AdsInfoHasTag::class, $only);
        self::assertSame(3, $only->getTag()->getId());
        self::assertSame('second', $only->getValue());
    }

    public function testEmptyStringAndNullValuesSkipRow(): void
    {
        $page = new AdsInfoPage();
        $tagMap = $this->buildTagMap(3, 7, 11);
        $parser = $this->buildParser($tagMap);

        $parser->parse($page, ['amenity' => [3 => '', 7 => null, 11 => '1']]);

        self::assertSame(1, $page->getHasTags()->count());

        $only = $page->getHasTags()->first();
        self::assertInstanceOf(AdsInfoHasTag::class, $only);
        self::assertSame(11, $only->getTag()->getId());
        self::assertSame('1', $only->getValue());
    }
}
