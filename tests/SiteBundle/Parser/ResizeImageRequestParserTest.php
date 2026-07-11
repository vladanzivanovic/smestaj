<?php

declare(strict_types=1);

namespace SiteBundle\Tests\Parser;

use PHPUnit\Framework\TestCase;
use SiteBundle\Dto\Ads\ResizeImageResult;
use SiteBundle\Helper\RandomCodeGenerator;
use SiteBundle\Parser\ResizeImageRequestParser;
use SiteBundle\Services\Ads\AdsImageResizer;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class ResizeImageRequestParserTest extends TestCase
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

    public function testProducesResultCarryingResizedFileAndGeneratedName(): void
    {
        $file = new UploadedFile($this->tmpPath, 'photo.png', 'image/png', null, true);
        $filenameHash = md5($file->getFilename());
        $expectedRandom = 'ABCDEFGHIJKLMNO';
        $expectedName = $filenameHash . $expectedRandom;

        $resizer = $this->createMock(AdsImageResizer::class);
        $resizer
            ->expects(self::once())
            ->method('resizeOnFly')
            ->with($file, $expectedName . '.png')
            ->willReturn('resized/' . $expectedName . '.png');

        $randomGenerator = $this->createMock(RandomCodeGenerator::class);
        $randomGenerator
            ->expects(self::once())
            ->method('random')
            ->with(15)
            ->willReturn($expectedRandom);

        $parser = new ResizeImageRequestParser($resizer, $randomGenerator);

        $result = $parser->parse($file);

        self::assertInstanceOf(ResizeImageResult::class, $result);
        self::assertSame('/uploads/tmp_images/resized/' . $expectedName . '.png', $result->file);
        self::assertSame('resized/' . $expectedName . '.png', $result->originalFilePath);
        self::assertSame($expectedName, $result->fileName);
        self::assertFalse($result->isMain);
        self::assertTrue($result->isImage);
    }
}
