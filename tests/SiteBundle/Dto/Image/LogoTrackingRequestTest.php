<?php

declare(strict_types=1);

namespace SiteBundle\Tests\Dto\Image;

use PHPUnit\Framework\TestCase;
use SiteBundle\Dto\Image\LogoTrackingRequest;

final class LogoTrackingRequestTest extends TestCase
{
    public function testDefaultCodeIsNull(): void
    {
        $dto = new LogoTrackingRequest();

        self::assertNull($dto->code);
    }

    public function testAcceptsStringCode(): void
    {
        $dto = new LogoTrackingRequest('tracking-code-abc');

        self::assertSame('tracking-code-abc', $dto->code);
    }

    public function testAcceptsEmptyStringCode(): void
    {
        $dto = new LogoTrackingRequest('');

        self::assertSame('', $dto->code);
    }

    public function testAcceptsExplicitNullCode(): void
    {
        $dto = new LogoTrackingRequest(null);

        self::assertNull($dto->code);
    }

    public function testCodePropertyIsReadonly(): void
    {
        $dto = new LogoTrackingRequest('initial');

        $this->expectException(\Error::class);

        /** @phpstan-ignore-next-line readonly write attempt is the assertion */
        $dto->code = 'mutated';
    }
}
