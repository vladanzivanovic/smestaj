<?php

declare(strict_types=1);

namespace SiteBundle\Tests\Dto\Ads;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use SiteBundle\Dto\Ads\CheckAdsExistRequest;
use Symfony\Component\Serializer\Attribute\SerializedName;

final class CheckAdsExistRequestTest extends TestCase
{
    public function testDefaultPropertiesAreNull(): void
    {
        $dto = new CheckAdsExistRequest();

        self::assertNull($dto->id);
        self::assertNull($dto->title);
    }

    public function testAcceptsIdAndTitle(): void
    {
        $dto = new CheckAdsExistRequest(id: 42, title: 'Some Ad Title');

        self::assertSame(42, $dto->id);
        self::assertSame('Some Ad Title', $dto->title);
    }

    public function testAcceptsOnlyTitle(): void
    {
        $dto = new CheckAdsExistRequest(title: 'Only Title');

        self::assertNull($dto->id);
        self::assertSame('Only Title', $dto->title);
    }

    public function testAcceptsOnlyId(): void
    {
        $dto = new CheckAdsExistRequest(id: 7);

        self::assertSame(7, $dto->id);
        self::assertNull($dto->title);
    }

    public function testTitlePropertyCarriesSerializedNameAttribute(): void
    {
        $reflection = new ReflectionClass(CheckAdsExistRequest::class);
        $constructor = $reflection->getConstructor();

        self::assertNotNull($constructor);

        $titleParam = null;

        foreach ($constructor->getParameters() as $param) {
            if ('title' === $param->getName()) {
                $titleParam = $param;
                break;
            }
        }

        self::assertNotNull($titleParam, 'title constructor parameter missing');

        $attributes = $titleParam->getAttributes(SerializedName::class);

        self::assertCount(1, $attributes);
        self::assertSame(['Title'], $attributes[0]->getArguments());
    }

    public function testIdPropertyDoesNotCarrySerializedName(): void
    {
        $reflection = new ReflectionClass(CheckAdsExistRequest::class);
        $constructor = $reflection->getConstructor();

        self::assertNotNull($constructor);

        $idParam = null;

        foreach ($constructor->getParameters() as $param) {
            if ('id' === $param->getName()) {
                $idParam = $param;
                break;
            }
        }

        self::assertNotNull($idParam);
        self::assertCount(0, $idParam->getAttributes(SerializedName::class));
    }

    public function testPropertiesAreReadonly(): void
    {
        $dto = new CheckAdsExistRequest(id: 1, title: 'foo');

        $this->expectException(\Error::class);

        /** @phpstan-ignore-next-line readonly write attempt is the assertion */
        $dto->id = 99;
    }
}
