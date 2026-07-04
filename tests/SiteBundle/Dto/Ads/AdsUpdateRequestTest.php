<?php

declare(strict_types=1);

namespace SiteBundle\Tests\Dto\Ads;

use PHPUnit\Framework\TestCase;
use SiteBundle\Dto\Ads\AdsUpdateRequest;
use Symfony\Component\HttpFoundation\ParameterBag;

final class AdsUpdateRequestTest extends TestCase
{
    public function testHoldsTheGivenBodyAndCsrfToken(): void
    {
        $body = new ParameterBag(['title_rs' => 'x', '_csrf_token' => 'abc']);

        $dto = new AdsUpdateRequest($body, 'abc');

        self::assertSame($body, $dto->body);
        self::assertSame('abc', $dto->csrfToken);
    }

    public function testBodyIsAccessible(): void
    {
        $dto = new AdsUpdateRequest(new ParameterBag(['title_rs' => 'value']), 'tok');

        self::assertSame('value', $dto->body->get('title_rs'));
    }

    public function testAcceptsEmptyCsrfToken(): void
    {
        $dto = new AdsUpdateRequest(new ParameterBag(), '');

        self::assertSame('', $dto->csrfToken);
    }

    public function testIsReadonly(): void
    {
        $dto = new AdsUpdateRequest(new ParameterBag(), 'abc');

        $this->expectException(\Error::class);

        /** @phpstan-ignore-next-line readonly write attempt is the assertion */
        $dto->csrfToken = 'xyz';
    }
}
