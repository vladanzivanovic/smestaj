<?php

declare(strict_types=1);

namespace AdminBundle\Tests\Handler;

use AdminBundle\Handler\InfoPageEditHandler;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use RuntimeException;
use SiteBundle\Entity\AdsInfoPage;
use SiteBundle\Entity\AdsInfoPageImage;
use SiteBundle\Entity\User;
use SiteBundle\Parser\InfoPageTagParser;
use SiteBundle\Repository\AdsInfoPageRepository;
use SiteBundle\Services\InfoPage\AdsCopyService;
use SiteBundle\Services\InfoPage\AdsInfoPageMainImageValidator;
use SiteBundle\Services\InfoPage\AdsInfoPagePublishValidator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\String\Slugger\SluggerInterface;

final class InfoPageEditHandlerHardDeleteTest extends TestCase
{
    private const PROJECT_DIR = '/tmp/smestaj-test';
    private const UPLOAD_DIR = 'uploads/images';
    private const ACTOR_ID = 42;
    private const ACTOR_HANDLE = 'admin@example.test';

    public function testHardDeleteLogsErrorBeforeRemoveAndUnlinksFiles(): void
    {
        $callLog = [];

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $logger = $this->createMock(LoggerInterface::class);
        $filesystem = $this->createMock(Filesystem::class);

        $entity = $this->buildEntity(7, 'Vila Test', 'vila-test', ['a.jpg', 'b.jpg']);
        $actor = $this->buildActor();

        $logger->expects(self::once())
            ->method('error')
            ->with(
                self::equalTo('AdsInfoPage hard delete'),
                self::callback(function (array $context): bool {
                    self::assertSame('hard_delete', $context['action']);
                    self::assertSame('AdsInfoPage', $context['entity']);
                    self::assertSame(7, $context['entityId']);
                    self::assertSame('Vila Test', $context['entityLabel']);
                    self::assertSame('vila-test', $context['entitySlug']);
                    self::assertSame(self::ACTOR_ID, $context['actorId']);
                    self::assertSame(self::ACTOR_HANDLE, $context['actorHandle']);
                    self::assertArrayHasKey('timestamp', $context);
                    self::assertSame(2, $context['imageCount']);

                    return true;
                }),
            )
            ->willReturnCallback(static function () use (&$callLog): void {
                $callLog[] = 'logger.error';
            });

        $entityManager->expects(self::once())
            ->method('remove')
            ->with(self::identicalTo($entity))
            ->willReturnCallback(static function () use (&$callLog): void {
                $callLog[] = 'em.remove';
            });

        $entityManager->expects(self::once())
            ->method('flush')
            ->willReturnCallback(static function () use (&$callLog): void {
                $callLog[] = 'em.flush';
            });

        $expectedPaths = [
            self::PROJECT_DIR . '/web/' . self::UPLOAD_DIR . '/info-pages/a.jpg',
            self::PROJECT_DIR . '/web/' . self::UPLOAD_DIR . '/info-pages/b.jpg',
        ];

        $unlinked = [];
        $filesystem->expects(self::exactly(2))
            ->method('remove')
            ->willReturnCallback(static function (string $path) use (&$callLog, &$unlinked): void {
                $callLog[] = 'fs.remove:' . $path;
                $unlinked[] = $path;
            });

        $handler = $this->buildHandler($entityManager, $logger, $filesystem);

        $handler->hardDelete($entity, $actor);

        self::assertSame(
            [
                'logger.error',
                'em.remove',
                'em.flush',
                'fs.remove:' . $expectedPaths[0],
                'fs.remove:' . $expectedPaths[1],
            ],
            $callLog,
            'Expected log -> remove -> flush -> unlink ordering.',
        );

        self::assertSame($expectedPaths, $unlinked);
    }

    public function testHardDeleteAbortsWhenLoggerThrows(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $logger = $this->createMock(LoggerInterface::class);
        $filesystem = $this->createMock(Filesystem::class);

        $entity = $this->buildEntity(7, 'Vila Test', 'vila-test', ['a.jpg', 'b.jpg']);
        $actor = $this->buildActor();

        $logger->expects(self::once())
            ->method('error')
            ->willThrowException(new RuntimeException('logger down'));

        $entityManager->expects(self::never())->method('remove');
        $entityManager->expects(self::never())->method('flush');
        $filesystem->expects(self::never())->method('remove');

        $handler = $this->buildHandler($entityManager, $logger, $filesystem);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Audit log write failed; hard delete aborted.');

        $handler->hardDelete($entity, $actor);
    }

    private function buildHandler(
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        Filesystem $filesystem,
    ): InfoPageEditHandler {
        return new InfoPageEditHandler(
            $entityManager,
            $this->stubFinalDependency(AdsInfoPageRepository::class),
            $this->stubFinalDependency(AdsInfoPagePublishValidator::class),
            $this->stubFinalDependency(AdsInfoPageMainImageValidator::class),
            $this->stubFinalDependency(AdsCopyService::class),
            $this->stubFinalDependency(InfoPageTagParser::class),
            $this->createMock(SluggerInterface::class),
            $logger,
            $filesystem,
            self::PROJECT_DIR,
            self::UPLOAD_DIR,
        );
    }

    /**
     * @param array<int, string> $filenames
     */
    private function buildEntity(int $id, string $propertyName, string $slug, array $filenames): AdsInfoPage
    {
        $entity = new AdsInfoPage();
        $entity->setPropertyName($propertyName);
        $entity->setSlug($slug);

        $reflection = new ReflectionClass(AdsInfoPage::class);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($entity, $id);

        foreach ($filenames as $filename) {
            $image = new AdsInfoPageImage();
            $image->setFilename($filename);
            $entity->addImage($image);
        }

        return $entity;
    }

    private function buildActor(): User
    {
        $actor = $this->createMock(User::class);
        $actor->method('getId')->willReturn(self::ACTOR_ID);
        $actor->method('getUserIdentifier')->willReturn(self::ACTOR_HANDLE);

        return $actor;
    }

    /**
     * Final collaborators of InfoPageEditHandler that `hardDelete()` never touches.
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
}
