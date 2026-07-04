<?php

declare(strict_types=1);

namespace SiteBundle\Tests\Dto\Ads;

use PHPUnit\Framework\TestCase;
use SiteBundle\Dto\Ads\ResizeImageRequest;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class ResizeImageRequestTest extends TestCase
{
    private string $tmpPath = '';

    protected function setUp(): void
    {
        $this->tmpPath = (string) tempnam(sys_get_temp_dir(), 'rir');
        file_put_contents($this->tmpPath, 'binary-content');
    }

    protected function tearDown(): void
    {
        if ('' !== $this->tmpPath && true === file_exists($this->tmpPath)) {
            @unlink($this->tmpPath);
        }
    }

    public function testHoldsTheUploadedFile(): void
    {
        $file = new UploadedFile($this->tmpPath, 'x.png', 'image/png', null, true);

        $dto = new ResizeImageRequest($file);

        self::assertSame($file, $dto->tmpImage);
    }

    public function testIsReadonly(): void
    {
        $file = new UploadedFile($this->tmpPath, 'x.png', 'image/png', null, true);

        $dto = new ResizeImageRequest($file);

        $this->expectException(\Error::class);

        /** @phpstan-ignore-next-line readonly write attempt is the assertion */
        $dto->tmpImage = $file;
    }
}
