<?php

declare(strict_types=1);

namespace SiteBundle\Tests\Services\InfoPage;

use HTMLPurifier;
use PHPUnit\Framework\TestCase;
use SiteBundle\Services\InfoPage\HouseRulesHtmlSanitizer;

final class HouseRulesHtmlSanitizerTest extends TestCase
{
    private HouseRulesHtmlSanitizer $sanitizer;

    protected function setUp(): void
    {
        $this->sanitizer = new HouseRulesHtmlSanitizer(new HTMLPurifier());
    }

    public function testSanitizeNullReturnsNull(): void
    {
        self::assertNull($this->sanitizer->sanitize(null));
    }

    public function testSanitizeEmptyStringReturnsNull(): void
    {
        self::assertNull($this->sanitizer->sanitize(''));
    }

    public function testSanitizeWhitespaceOnlyReturnsNull(): void
    {
        self::assertNull($this->sanitizer->sanitize('   '));
    }

    public function testSanitizePreservesAllowedTags(): void
    {
        $result = $this->sanitizer->sanitize('<p>Hello <strong>world</strong></p>');

        self::assertNotNull($result);
        self::assertStringContainsString('<p>', $result);
        self::assertStringContainsString('<strong>', $result);
    }

    public function testSanitizeStripsScriptTagButKeepsAllowedSibling(): void
    {
        $result = $this->sanitizer->sanitize('<script>alert(1)</script><p>ok</p>');

        self::assertNotNull($result);
        self::assertStringNotContainsString('<script', $result);
        self::assertStringContainsString('<p>ok</p>', $result);
    }

    public function testSanitizeStripsIframe(): void
    {
        $result = $this->sanitizer->sanitize('<iframe src="evil"></iframe><p>ok</p>');

        self::assertNotNull($result);
        self::assertStringNotContainsString('<iframe', $result);
        self::assertStringContainsString('<p>ok</p>', $result);
    }

    public function testSanitizeStripsInlineEventHandler(): void
    {
        $result = $this->sanitizer->sanitize('<p onclick="alert(1)">hi</p>');

        self::assertNotNull($result);
        self::assertStringNotContainsString('onclick', $result);
        self::assertStringContainsString('hi', $result);
    }

    public function testSanitizeNeutralisesJavascriptUrl(): void
    {
        $result = $this->sanitizer->sanitize('<a href="javascript:alert(1)">x</a>');

        if (null === $result) {
            self::assertNull($result);

            return;
        }

        self::assertStringNotContainsString('javascript:', $result);
    }

    public function testSanitizeStripsListMarkup(): void
    {
        $result = $this->sanitizer->sanitize('<ul><li>x</li></ul>');

        if (null === $result) {
            self::assertNull($result);

            return;
        }

        self::assertStringNotContainsString('<ul', $result);
        self::assertStringNotContainsString('<li', $result);
    }
}
