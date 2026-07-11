<?php

declare(strict_types=1);

namespace SiteBundle\Tests\Parser;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use SiteBundle\Dto\Ads\AdsSaveRequest;
use SiteBundle\Entity\Ads;
use SiteBundle\Entity\User;
use SiteBundle\Parser\AdsEditParser;
use SiteBundle\Parser\AdsSaveRequestParser;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Merges the intent of the retired AdsInsertRequestParserTest +
 * AdsUpdateRequestParserTest. The consolidated parser accepts a nullable
 * ?Ads discriminator on its `parse` signature — null triggers the insert
 * path (fresh entity in AdsEditParser), non-null triggers the update path
 * (mutate existing entity). The token-storage guard is shared across both
 * paths and is covered by a single test.
 */
final class AdsSaveRequestParserTest extends TestCase
{
    public function testDelegatesToAdsEditParserOnInsertWithNullEntity(): void
    {
        $user = new User();
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->method('getToken')->willReturn($token);

        $expectedAds = new Ads();
        $dto = new AdsSaveRequest();
        $dto->title = 'Delegated';

        $adsEditParser = $this->createMock(AdsEditParser::class);
        $adsEditParser
            ->expects(self::once())
            ->method('parse')
            ->with($dto, $user, $user, null)
            ->willReturn($expectedAds);

        $parser = new AdsSaveRequestParser($adsEditParser, $tokenStorage);

        $result = $parser->parse($dto);

        self::assertSame($expectedAds, $result);
    }

    public function testDelegatesToAdsEditParserOnUpdateWithExistingEntity(): void
    {
        $user = new User();
        $existingAds = new Ads();
        $dto = new AdsSaveRequest();
        $dto->title = 'Updated';

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->method('getToken')->willReturn($token);

        $adsEditParser = $this->createMock(AdsEditParser::class);
        $adsEditParser
            ->expects(self::once())
            ->method('parse')
            ->with($dto, $user, $user, $existingAds)
            ->willReturn($existingAds);

        $parser = new AdsSaveRequestParser($adsEditParser, $tokenStorage);

        $result = $parser->parse($dto, $existingAds);

        self::assertSame($existingAds, $result);
    }

    public function testRejectsMissingAuthenticationToken(): void
    {
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->method('getToken')->willReturn(null);

        $adsEditParser = $this->createMock(AdsEditParser::class);
        $adsEditParser->expects(self::never())->method('parse');

        $parser = new AdsSaveRequestParser($adsEditParser, $tokenStorage);

        $this->expectException(RuntimeException::class);

        $parser->parse(new AdsSaveRequest());
    }
}
