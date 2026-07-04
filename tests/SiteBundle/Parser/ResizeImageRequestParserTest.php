<?php

declare(strict_types=1);

namespace SiteBundle\Tests\Parser;

use PHPUnit\Framework\TestCase;
use SiteBundle\Dto\Ads\ResizeImageRequest;
use SiteBundle\Parser\ResizeImageRequestParser;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

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

    public function testReturnsDtoWhenFilePresent(): void
    {
        $parser = new ResizeImageRequestParser();
        $file = new UploadedFile($this->tmpPath, 'x.png', 'image/png', null, true);

        $request = new Request();
        $request->files->set('tmp_image', $file);

        $dto = $parser->fromRequest($request);

        self::assertInstanceOf(ResizeImageRequest::class, $dto);
        self::assertSame($file, $dto->tmpImage);
    }

    public function testReturnsNullWhenFileMissing(): void
    {
        $parser = new ResizeImageRequestParser();
        $request = new Request();

        self::assertNull($parser->fromRequest($request));
    }
}
