<?php

declare(strict_types=1);

namespace SiteBundle\Tests\Dto\Ads;

use PHPUnit\Framework\TestCase;
use SiteBundle\Dto\Ads\AdsListRequest;
use Symfony\Component\HttpFoundation\ParameterBag;

final class AdsListRequestTest extends TestCase
{
    public function testHoldsTheGivenQueryBagAndExtraParams(): void
    {
        $query = new ParameterBag(['city' => 'budva', 'price' => '100']);

        $dto = new AdsListRequest($query, 'extra-token');

        self::assertSame($query, $dto->query);
        self::assertSame('extra-token', $dto->extraParams);
    }

    public function testExtraParamsDefaultsToNull(): void
    {
        $dto = new AdsListRequest(new ParameterBag());

        self::assertNull($dto->extraParams);
    }

    public function testQueryBagIsAccessible(): void
    {
        $dto = new AdsListRequest(new ParameterBag(['key' => 'value']));

        self::assertSame('value', $dto->query->get('key'));
    }

    public function testIsReadonly(): void
    {
        $dto = new AdsListRequest(new ParameterBag(), 'x');

        $this->expectException(\Error::class);

        /** @phpstan-ignore-next-line readonly write attempt is the assertion */
        $dto->extraParams = 'y';
    }
}
