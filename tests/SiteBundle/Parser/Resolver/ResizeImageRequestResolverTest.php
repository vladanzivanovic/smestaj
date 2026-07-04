<?php

declare(strict_types=1);

namespace SiteBundle\Tests\Parser\Resolver;

use PHPUnit\Framework\TestCase;
use SiteBundle\Dto\Ads\ResizeImageRequest;
use SiteBundle\Parser\Resolver\ResizeImageRequestResolver;
use SiteBundle\Parser\ResizeImageRequestParser;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class ResizeImageRequestResolverTest extends TestCase
{
    private ResizeImageRequestResolver $resolver;
    private string $tmpPath = '';

    protected function setUp(): void
    {
        $this->resolver = new ResizeImageRequestResolver(new ResizeImageRequestParser());
        $this->tmpPath = (string) tempnam(sys_get_temp_dir(), 'rir');
        file_put_contents($this->tmpPath, 'binary-content');
    }

    protected function tearDown(): void
    {
        if ('' !== $this->tmpPath && true === file_exists($this->tmpPath)) {
            @unlink($this->tmpPath);
        }
    }

    public function testReturnsEmptyForUnrelatedType(): void
    {
        $argument = new ArgumentMetadata('value', 'int', false, false, null);

        $resolved = iterator_to_array(
            $this->resolver->resolve(new Request(), $argument),
            false,
        );

        self::assertSame([], $resolved);
    }

    public function testYieldsResizeImageRequestForMatchingType(): void
    {
        $file = new UploadedFile($this->tmpPath, 'x.png', 'image/png', null, true);

        $request = new Request();
        $request->files->set('tmp_image', $file);

        $argument = new ArgumentMetadata('imageRequest', ResizeImageRequest::class, false, false, null);

        $resolved = iterator_to_array($this->resolver->resolve($request, $argument), false);

        self::assertCount(1, $resolved);
        self::assertInstanceOf(ResizeImageRequest::class, $resolved[0]);
        self::assertSame($file, $resolved[0]->tmpImage);
    }

    public function testYieldsNullWhenFileMissing(): void
    {
        $request = new Request();
        $argument = new ArgumentMetadata('imageRequest', ResizeImageRequest::class, false, false, null);

        $resolved = iterator_to_array($this->resolver->resolve($request, $argument), false);

        self::assertCount(1, $resolved);
        self::assertNull($resolved[0]);
    }
}
