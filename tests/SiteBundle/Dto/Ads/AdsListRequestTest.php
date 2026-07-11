<?php

declare(strict_types=1);

namespace SiteBundle\Tests\Dto\Ads;

use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use SiteBundle\Dto\Ads\AdsListRequest;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Constraints as Assert;

final class AdsListRequestTest extends TestCase
{
    private Serializer $serializer;

    protected function setUp(): void
    {
        $metadataFactory = new ClassMetadataFactory(new AttributeLoader());
        $nameConverter = new MetadataAwareNameConverter($metadataFactory);
        $objectNormalizer = new ObjectNormalizer(
            $metadataFactory,
            $nameConverter,
            null,
            new ReflectionExtractor(),
        );

        $this->serializer = new Serializer([new ArrayDenormalizer(), $objectNormalizer]);
    }

    public function testDefaultPropertiesAreNull(): void
    {
        $dto = new AdsListRequest();

        self::assertNull($dto->page);
        self::assertNull($dto->sort);
        self::assertNull($dto->tags);
        self::assertNull($dto->city);
        self::assertNull($dto->categories);
        self::assertNull($dto->size);
        self::assertNull($dto->color);
        self::assertNull($dto->price);
    }

    public function testDenormalizesFromSerbianQueryKeysIntoTypedProperties(): void
    {
        $payload = [
            'stranica' => 3,
            'sortiranje' => 'najnovije',
            'tagovi' => ['1', '2', '3'],
            'mesto' => ['Beograd', 'Novi Sad'],
            'kategorije' => ['apartman'],
            'veličina' => ['M', 'L'],
            'boja' => ['crvena'],
            'cena' => ['0-100'],
        ];

        $dto = $this->serializer->denormalize($payload, AdsListRequest::class);

        self::assertSame(3, $dto->page);
        self::assertSame('najnovije', $dto->sort);
        self::assertSame(['1', '2', '3'], $dto->tags);
        self::assertSame(['Beograd', 'Novi Sad'], $dto->city);
        self::assertSame(['apartman'], $dto->categories);
        self::assertSame(['M', 'L'], $dto->size);
        self::assertSame(['crvena'], $dto->color);
        self::assertSame(['0-100'], $dto->price);
    }

    public function testDenormalizesScalarStringForUnionTypedProperties(): void
    {
        $payload = [
            'tagovi' => '1,2,3',
            'mesto' => 'Beograd',
        ];

        $dto = $this->serializer->denormalize($payload, AdsListRequest::class);

        self::assertSame('1,2,3', $dto->tags);
        self::assertSame('Beograd', $dto->city);
    }

    public function testPageCarriesPositiveConstraint(): void
    {
        $property = new ReflectionProperty(AdsListRequest::class, 'page');

        self::assertCount(1, $property->getAttributes(Assert\Positive::class));
    }

    public function testSortCarriesLengthConstraint(): void
    {
        $property = new ReflectionProperty(AdsListRequest::class, 'sort');

        self::assertCount(1, $property->getAttributes(Assert\Length::class));
    }

    public function testSerbianWireKeysPreservedViaSerializedName(): void
    {
        $expectedMappings = [
            'page' => 'stranica',
            'sort' => 'sortiranje',
            'tags' => 'tagovi',
            'city' => 'mesto',
            'categories' => 'kategorije',
            'size' => 'veličina',
            'color' => 'boja',
            'price' => 'cena',
        ];

        foreach ($expectedMappings as $propertyName => $wireKey) {
            $property = new ReflectionProperty(AdsListRequest::class, $propertyName);
            $attributes = $property->getAttributes(SerializedName::class);

            self::assertCount(1, $attributes, sprintf('property %s missing #[SerializedName]', $propertyName));
            self::assertSame([$wireKey], $attributes[0]->getArguments(), sprintf('property %s wire key mismatch', $propertyName));
        }
    }
}
