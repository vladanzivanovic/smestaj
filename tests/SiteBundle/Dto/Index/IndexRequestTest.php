<?php

declare(strict_types=1);

namespace SiteBundle\Tests\Dto\Index;

use PHPUnit\Framework\TestCase;
use SiteBundle\Dto\Index\IndexRequest;

final class IndexRequestTest extends TestCase
{
    public function testDefaultTokenIsNull(): void
    {
        $dto = new IndexRequest();

        self::assertNull($dto->token);
    }

    public function testAcceptsStringToken(): void
    {
        $dto = new IndexRequest('reset-token-123');

        self::assertSame('reset-token-123', $dto->token);
    }

    public function testAcceptsEmptyStringToken(): void
    {
        $dto = new IndexRequest('');

        self::assertSame('', $dto->token);
    }

    public function testAcceptsExplicitNullToken(): void
    {
        $dto = new IndexRequest(null);

        self::assertNull($dto->token);
    }

    public function testTokenPropertyIsReadonly(): void
    {
        $dto = new IndexRequest('initial');

        $this->expectException(\Error::class);

        /** @phpstan-ignore-next-line readonly write attempt is the assertion */
        $dto->token = 'mutated';
    }
}
