<?php

declare(strict_types=1);

namespace SiteBundle\Tests\Parser;

use PHPUnit\Framework\TestCase;
use SiteBundle\Dto\User\SetNewPasswordRequest;
use SiteBundle\Parser\SetNewPasswordRequestParser;

final class SetNewPasswordRequestParserTest extends TestCase
{
    private SetNewPasswordRequestParser $parser;

    protected function setUp(): void
    {
        $this->parser = new SetNewPasswordRequestParser();
    }

    public function testToArrayProducesHandlerKeyShape(): void
    {
        $dto = new SetNewPasswordRequest();
        $dto->password = 'newSecret';
        $dto->rePassword = 'newSecret';

        $data = $this->parser->toArray($dto);

        self::assertSame(['password' => 'newSecret', 'rePassword' => 'newSecret'], $data);
    }

    public function testToArrayPassesEmptyStringsVerbatim(): void
    {
        $dto = new SetNewPasswordRequest();

        $data = $this->parser->toArray($dto);

        self::assertSame(['password' => '', 'rePassword' => ''], $data);
    }

    public function testToArrayDoesNotCoerceMismatchedValues(): void
    {
        $dto = new SetNewPasswordRequest();
        $dto->password = 'aaa';
        $dto->rePassword = 'bbb';

        $data = $this->parser->toArray($dto);

        self::assertSame('aaa', $data['password']);
        self::assertSame('bbb', $data['rePassword']);
    }
}
