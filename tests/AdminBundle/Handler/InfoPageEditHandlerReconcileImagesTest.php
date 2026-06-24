<?php

declare(strict_types=1);

namespace AdminBundle\Tests\Handler;

use AdminBundle\Handler\InfoPageEditHandler;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use SiteBundle\Entity\AdsInfoPage;
use SiteBundle\Entity\AdsInfoPageImage;
use SiteBundle\Parser\InfoPageTagParser;
use SiteBundle\Repository\AdsInfoPageRepository;
use SiteBundle\Services\InfoPage\AdsCopyService;
use SiteBundle\Services\InfoPage\AdsInfoPageMainImageValidator;
use SiteBundle\Services\InfoPage\AdsInfoPagePublishValidator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\AsciiSlugger;

final class InfoPageEditHandlerReconcileImagesTest extends TestCase
{
    private const UPLOAD_IMAGE_DIR = 'uploads/images';

    private string $fixtureRoot;
    private string $originalCwd;

    /**
     * @var array<int, AdsInfoPageImage>
     */
    private array $entityImageById = [];

    protected function setUp(): void
    {
        $this->fixtureRoot = sys_get_temp_dir() . '/smestaj-reconcile-test-' . uniqid('', true);

        mkdir($this->fixtureRoot . '/uploads/tmp', 0775, true);
        mkdir($this->fixtureRoot . '/web/' . self::UPLOAD_IMAGE_DIR . '/info-pages', 0775, true);

        file_put_contents($this->fixtureRoot . '/uploads/tmp/abc.jpg', 'x');
        file_put_contents($this->fixtureRoot . '/uploads/tmp/new.jpg', 'x');
        file_put_contents($this->fixtureRoot . '/web/' . self::UPLOAD_IMAGE_DIR . '/info-pages/a.jpg', 'x');
        file_put_contents($this->fixtureRoot . '/web/' . self::UPLOAD_IMAGE_DIR . '/info-pages/b.jpg', 'x');

        $this->originalCwd = (string) getcwd();
        chdir($this->fixtureRoot);

        $this->entityImageById = [];
    }

    protected function tearDown(): void
    {
        chdir($this->originalCwd);
        $this->rmrf($this->fixtureRoot);
    }

    public function testValidateTmpPathRejectsPathTraversal(): void
    {
        $handler = $this->buildHandler($this->createMock(LoggerInterface::class));

        $reflection = new ReflectionClass(InfoPageEditHandler::class);
        $method = $reflection->getMethod('validateTmpPath');
        $method->setAccessible(true);

        self::assertFalse($method->invoke($handler, ''), 'empty string must be rejected');
        self::assertFalse($method->invoke($handler, 'uploads/tmp/../escape.jpg'), 'paths containing .. must be rejected');
        self::assertFalse($method->invoke($handler, '/uploads/tmp/foo.jpg'), 'absolute paths must be rejected');
        self::assertFalse($method->invoke($handler, 'https://example.com/foo.jpg'), 'paths with :// must be rejected');
        self::assertFalse($method->invoke($handler, 'uploads/other/foo.jpg'), 'paths outside uploads/tmp/ must be rejected');
        self::assertTrue($method->invoke($handler, 'uploads/tmp/foo.jpg'), 'paths under uploads/tmp/ must be accepted');
    }

    public function testNewResizedRowIsAddedWithFileAttached(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('warning');

        $entity = new AdsInfoPage();
        $handler = $this->buildHandler($logger);

        $this->invokeReconcile($handler, $entity, [
            [
                'id' => null,
                'isMain' => true,
                'fileName' => 'abc.jpg',
                'originalFilePath' => 'uploads/tmp/abc.jpg',
                'deleted' => false,
            ],
        ]);

        self::assertCount(1, $entity->getImages());

        $image = $entity->getImages()->first();
        self::assertInstanceOf(AdsInfoPageImage::class, $image);
        self::assertInstanceOf(UploadedFile::class, $image->getFile());
        self::assertNotNull($image->getFilename());
        self::assertNotSame('', $image->getFilename());
        self::assertTrue($image->isMain());
        self::assertSame(0, $image->getPosition());
        self::assertSame('abc.jpg', $image->getOriginalName());
    }

    public function testNewRowWithInvalidPathIsSkipped(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('warning');

        $entity = new AdsInfoPage();
        $handler = $this->buildHandler($logger);

        $this->invokeReconcile($handler, $entity, [
            [
                'id' => null,
                'isMain' => false,
                'fileName' => 'evil.jpg',
                'originalFilePath' => '../etc/passwd',
                'deleted' => false,
            ],
        ]);

        self::assertCount(0, $entity->getImages());
    }

    public function testDeletedExistingRowMarksDeletedAndRemovesFromCollection(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('warning');

        $entity = $this->buildEntity([
            7 => ['filename' => 'a.jpg', 'isMain' => true],
        ]);
        $handler = $this->buildHandler($logger);

        $this->invokeReconcile($handler, $entity, [
            [
                'id' => 7,
                'isMain' => false,
                'fileName' => 'a.jpg',
                'originalFilePath' => null,
                'deleted' => true,
            ],
        ]);

        self::assertCount(0, $entity->getImages());

        $deletedImage = $this->imageById(7);
        self::assertTrue($deletedImage->isDeleted());
        self::assertInstanceOf(UploadedFile::class, $deletedImage->getFile());
    }

    public function testChangeMainOnlyFlipsIsMainOnExisting(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('warning');

        $entity = $this->buildEntity([
            7 => ['filename' => 'a.jpg', 'isMain' => true],
            8 => ['filename' => 'b.jpg', 'isMain' => false],
        ]);
        $handler = $this->buildHandler($logger);

        $this->invokeReconcile($handler, $entity, [
            ['id' => 7, 'isMain' => false, 'fileName' => 'a.jpg', 'originalFilePath' => null, 'deleted' => false],
            ['id' => 8, 'isMain' => true, 'fileName' => 'b.jpg', 'originalFilePath' => null, 'deleted' => false],
        ]);

        self::assertCount(2, $entity->getImages());
        self::assertFalse($this->imageById(7)->isMain());
        self::assertTrue($this->imageById(8)->isMain());
    }

    public function testCombinedAddDeleteAndMainSwap(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('warning');

        $entity = $this->buildEntity([
            7 => ['filename' => 'a.jpg', 'isMain' => true],
            8 => ['filename' => 'b.jpg', 'isMain' => false],
        ]);
        $handler = $this->buildHandler($logger);

        $this->invokeReconcile($handler, $entity, [
            ['id' => 7, 'isMain' => false, 'fileName' => 'a.jpg', 'originalFilePath' => null, 'deleted' => true],
            ['id' => 8, 'isMain' => false, 'fileName' => 'b.jpg', 'originalFilePath' => null, 'deleted' => false],
            [
                'id' => null,
                'isMain' => true,
                'fileName' => 'new.jpg',
                'originalFilePath' => 'uploads/tmp/new.jpg',
                'deleted' => false,
            ],
        ]);

        self::assertCount(2, $entity->getImages());

        $image7 = $this->imageById(7);
        self::assertTrue($image7->isDeleted(), 'deleted image must have isDeleted flag set');
        self::assertFalse($entity->getImages()->contains($image7), 'deleted image must be removed from collection');

        self::assertFalse($this->imageById(8)->isMain());

        $newImage = null;
        foreach ($entity->getImages() as $image) {
            if (null === $image->getId()) {
                $newImage = $image;

                break;
            }
        }

        self::assertInstanceOf(AdsInfoPageImage::class, $newImage);
        self::assertTrue($newImage->isMain());
        self::assertInstanceOf(UploadedFile::class, $newImage->getFile());
    }

    public function testEmptyImageStatesIsNoOp(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('warning');

        $entity = $this->buildEntity([
            7 => ['filename' => 'a.jpg', 'isMain' => true],
            8 => ['filename' => 'b.jpg', 'isMain' => false],
        ]);
        $handler = $this->buildHandler($logger);

        $this->invokeReconcile($handler, $entity, []);

        self::assertCount(2, $entity->getImages());
        self::assertTrue($this->imageById(7)->isMain());
        self::assertFalse($this->imageById(8)->isMain());
    }

    private function buildHandler(LoggerInterface $logger): InfoPageEditHandler
    {
        return new InfoPageEditHandler(
            $this->createMock(EntityManagerInterface::class),
            $this->stubFinalDependency(AdsInfoPageRepository::class),
            $this->stubFinalDependency(AdsInfoPagePublishValidator::class),
            $this->stubFinalDependency(AdsInfoPageMainImageValidator::class),
            $this->stubFinalDependency(AdsCopyService::class),
            $this->stubFinalDependency(InfoPageTagParser::class),
            new AsciiSlugger(),
            $logger,
            $this->createMock(Filesystem::class),
            $this->fixtureRoot,
            self::UPLOAD_IMAGE_DIR,
        );
    }

    /**
     * @param array<int, array{filename: string, isMain: bool}> $images
     */
    private function buildEntity(array $images): AdsInfoPage
    {
        $entity = new AdsInfoPage();
        $entity->setPropertyName('Test');
        $entity->setSlug('test');

        $position = 0;

        foreach ($images as $id => $spec) {
            $image = new AdsInfoPageImage();
            $image->setFilename($spec['filename']);
            $image->setOriginalName($spec['filename']);
            $image->setIsMain($spec['isMain']);
            $image->setPosition($position);

            $reflectionImage = new ReflectionClass(AdsInfoPageImage::class);
            $idProperty = $reflectionImage->getProperty('id');
            $idProperty->setAccessible(true);
            $idProperty->setValue($image, $id);

            $entity->addImage($image);
            $this->entityImageById[$id] = $image;

            $position++;
        }

        return $entity;
    }

    private function imageById(int $id): AdsInfoPageImage
    {
        return $this->entityImageById[$id];
    }

    /**
     * @param array<int, array{id: ?int, isMain: bool, fileName: ?string, originalFilePath: ?string, deleted: bool}> $imageStates
     */
    private function invokeReconcile(InfoPageEditHandler $handler, AdsInfoPage $entity, array $imageStates): void
    {
        $reflection = new ReflectionClass(InfoPageEditHandler::class);
        $method = $reflection->getMethod('reconcileImages');
        $method->setAccessible(true);
        $method->invoke($handler, $entity, $imageStates);
    }

    /**
     * Final collaborators of InfoPageEditHandler that `reconcileImages()` never touches.
     * PHPUnit 9 cannot mock final classes, so we instantiate without invoking the
     * constructor — the resulting object satisfies the constructor's type hint and
     * is never exercised by the code under test.
     *
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    private function stubFinalDependency(string $class): object
    {
        return (new ReflectionClass($class))->newInstanceWithoutConstructor();
    }

    private function rmrf(string $path): void
    {
        if (false === is_dir($path)) {
            if (true === file_exists($path)) {
                @unlink($path);
            }

            return;
        }

        $items = scandir($path);

        if (false === $items) {
            return;
        }

        foreach ($items as $item) {
            if ('.' === $item || '..' === $item) {
                continue;
            }

            $this->rmrf($path . '/' . $item);
        }

        @rmdir($path);
    }
}
