<?php

declare(strict_types=1);

namespace AdminBundle\Handler;

use AdminBundle\Model\InfoPageEditModel;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use SiteBundle\Entity\Ads;
use SiteBundle\Entity\AdsInfoPage;
use SiteBundle\Entity\AdsInfoPageImage;
use SiteBundle\Entity\User;
use SiteBundle\Parser\InfoPageTagParser;
use SiteBundle\Repository\AdsInfoPageRepository;
use SiteBundle\Services\InfoPage\AdsCopyService;
use SiteBundle\Services\InfoPage\AdsInfoPageMainImageValidator;
use SiteBundle\Services\InfoPage\AdsInfoPagePublishValidator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Throwable;

final class InfoPageEditHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AdsInfoPageRepository $adsInfoPageRepository,
        private readonly AdsInfoPagePublishValidator $publishValidator,
        private readonly AdsInfoPageMainImageValidator $mainImageValidator,
        private readonly AdsCopyService $adsCopyService,
        private readonly InfoPageTagParser $tagParser,
        private readonly SluggerInterface $slugger,
        private readonly LoggerInterface $logger,
        private readonly Filesystem $filesystem,
        #[Autowire('%kernel.project_dir%')] private readonly string $projectDir,
        #[Autowire('%upload_image_dir%')] private readonly string $uploadImageDir,
    ) {
    }

    public function createFromAds(Ads $ads, bool $copy): AdsInfoPage
    {
        $entity = new AdsInfoPage();
        $entity->setLinkedAds($ads);

        if (true === $copy) {
            $this->adsCopyService->copyFromAds($ads, $entity);
        } else {
            $entity->setPropertyName($ads->getTitle());
        }

        $entity->setSlug($this->generateUniqueSlug($entity->getPropertyName(), null));

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return $entity;
    }

    /**
     * @return array{violations: array<string, string>}
     */
    public function update(AdsInfoPage $entity, InfoPageEditModel $model): array
    {
        $this->ensureSlug($entity);
        $this->tagParser->parse($entity, $model->tags);
        $this->reconcileImages($entity, $model->imageStates);

        $mainImageViolations = $this->mainImageValidator->validate($entity);

        if (0 < count($mainImageViolations)) {
            return ['violations' => $mainImageViolations];
        }

        if (true === $model->requestedPublish) {
            $entity->setPublished(true);

            $violations = $this->publishValidator->validate($entity);

            if (0 < count($violations)) {
                $entity->setPublished(false);
                $this->entityManager->flush();

                return ['violations' => $violations];
            }
        } else {
            $entity->setPublished(false);
        }

        $this->entityManager->flush();

        return ['violations' => []];
    }

    /**
     * @return array{ok: bool, published: bool, violations: array<string, string>}
     */
    public function togglePublish(AdsInfoPage $entity, bool $target): array
    {
        if (false === $target) {
            $entity->setPublished(false);
            $this->entityManager->flush();

            return ['ok' => true, 'published' => false, 'violations' => []];
        }

        $entity->setPublished(true);

        $violations = $this->publishValidator->validate($entity);

        if (0 < count($violations)) {
            $entity->setPublished(false);
            $this->entityManager->flush();

            return ['ok' => false, 'published' => false, 'violations' => $violations];
        }

        $this->entityManager->flush();

        return ['ok' => true, 'published' => true, 'violations' => []];
    }

    public function hardDelete(AdsInfoPage $entity, User $actor): void
    {
        $id = $entity->getId();
        $slug = $entity->getSlug();
        $label = $entity->getPropertyName();

        $absolutePaths = [];

        foreach ($entity->getImages() as $image) {
            $filename = $image->getFilename();

            if (null === $filename || '' === $filename) {
                continue;
            }

            $absolutePaths[] = rtrim($this->projectDir, '/') . '/web/' . trim($this->uploadImageDir, '/') . '/info-pages/' . $filename;
        }

        $context = [
            'action' => 'hard_delete',
            'entity' => 'AdsInfoPage',
            'entityId' => $id,
            'entityLabel' => $label,
            'entitySlug' => $slug,
            'actorId' => $actor->getId(),
            'actorHandle' => $actor->getUserIdentifier(),
            'timestamp' => (new DateTimeImmutable())->format(DateTimeInterface::ATOM),
            'imageCount' => count($absolutePaths),
        ];

        try {
            $this->logger->error('AdsInfoPage hard delete', $context);
        } catch (Throwable $e) {
            throw new RuntimeException('Audit log write failed; hard delete aborted.', 0, $e);
        }

        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        foreach ($absolutePaths as $absolutePath) {
            try {
                $this->filesystem->remove($absolutePath);
            } catch (Throwable $e) {
                $this->logger->warning('AdsInfoPage hard delete: file unlink failed', [
                    'path' => $absolutePath,
                    'errorMessage' => $e->getMessage(),
                ]);
            }
        }
    }

    private function ensureSlug(AdsInfoPage $entity): void
    {
        if ('' !== $entity->getSlug()) {
            return;
        }

        $entity->setSlug($this->generateUniqueSlug($entity->getPropertyName(), $entity->getId()));
    }

    private function generateUniqueSlug(string $source, ?int $ignoreId): string
    {
        $base = $this->slugger->slug($source)->lower()->toString();

        if ('' === $base) {
            $base = 'info';
        }

        $candidate = $base;
        $suffix = 1;

        while (true === $this->slugTaken($candidate, $ignoreId)) {
            $suffix++;
            $candidate = $base . '-' . $suffix;
        }

        return $candidate;
    }

    private function slugTaken(string $candidate, ?int $ignoreId): bool
    {
        $existing = $this->adsInfoPageRepository->findOneBySlug($candidate);

        if (null === $existing) {
            return false;
        }

        if (null !== $ignoreId && $existing->getId() === $ignoreId) {
            return false;
        }

        return true;
    }

    /**
     * Reconciles the dropzone state submitted from InfoPageEditHandler.js with
     * the persisted AdsInfoPageImage collection. Mirrors the proven Ads flow at
     * SiteBundle\Services\Ads\AdsImageService::setImage. Each `$imageStates`
     * row dispatches into one of three branches:
     *
     *   - existing + `deleted: true`  → attach final-path UploadedFile, flag
     *                                   deleted, remove from collection. The
     *                                   orphan-removal cascade flushes the
     *                                   DELETE; AdsInfoPageFileUploadListener
     *                                   ::postRemove unlinks the file on disk.
     *   - existing + not deleted      → flip isMain only. No file work.
     *   - new (id === null) + valid   → instantiate AdsInfoPageImage, set
     *                                   originalName/filename/position/isMain,
     *                                   attach UploadedFile pointing at the
     *                                   resize-staged file under web/uploads/tmp/.
     *                                   The post-persist listener moves it to
     *                                   web/uploads/images/info-pages/<filename>.
     *
     * Rows that don't fit any branch (new with missing or unsafe
     * originalFilePath) are logged and skipped — matches the Ads flow's
     * FileNotFoundException tolerance.
     *
     * @param array<int, array{id: ?int, isMain: bool, fileName: ?string, originalFilePath: ?string, deleted: bool}> $imageStates
     */
    private function reconcileImages(AdsInfoPage $entity, array $imageStates): void
    {
        $existingById = [];

        foreach ($entity->getImages() as $image) {
            $id = $image->getId();

            if (null !== $id) {
                $existingById[(int) $id] = $image;
            }
        }

        $newRowStates = [];

        foreach ($imageStates as $state) {
            $stateId = $state['id'];

            if (null !== $stateId && true === $state['deleted']) {
                $existing = $existingById[$stateId] ?? null;

                if (false === $existing instanceof AdsInfoPageImage) {
                    continue;
                }

                $filename = $existing->getFilename() ?? '';

                $absolutePath = rtrim($this->projectDir, '/')
                    . '/web/'
                    . trim($this->uploadImageDir, '/')
                    . '/info-pages/'
                    . $filename;

                $existing->setFile(new UploadedFile($absolutePath, $filename, null, null, true));
                $existing->setDeleted(true);
                $entity->removeImage($existing);

                continue;
            }

            if (null !== $stateId) {
                $existing = $existingById[$stateId] ?? null;

                if (false === $existing instanceof AdsInfoPageImage) {
                    continue;
                }

                $existing->setIsMain($state['isMain']);

                continue;
            }

            $newRowStates[] = $state;
        }

        $nextPosition = $entity->getImages()->count();

        foreach ($newRowStates as $state) {
            $originalFilePath = $state['originalFilePath'];

            if (null === $originalFilePath || false === $this->validateTmpPath($originalFilePath)) {
                $this->logger->warning('AdsInfoPage reconcileImages: skipped new image row with missing or unsafe originalFilePath', [
                    'fileName' => $state['fileName'],
                    'originalFilePath' => $originalFilePath,
                    'infoPageId' => $entity->getId(),
                ]);

                continue;
            }

            $fileName = $state['fileName'] ?? 'image';

            $image = new AdsInfoPageImage();
            $image->setOriginalName($fileName);
            $image->setFilename($this->buildUploadFilename($fileName));
            $image->setPosition($nextPosition);
            $image->setIsMain($state['isMain']);
            $image->setFile(new UploadedFile($originalFilePath, $fileName, null, null, true));

            $entity->addImage($image);

            $nextPosition++;
        }
    }

    /**
     * Defensive boundary check on the client-supplied originalFilePath value.
     * The resize endpoint emits paths like 'uploads/tmp/<md5+rnd>.<ext>' —
     * anything else is rejected to prevent traversal / absolute / scheme tricks
     * from reaching the UploadedFile constructor.
     */
    private function validateTmpPath(string $path): bool
    {
        if ('' === $path) {
            return false;
        }

        if (true === str_contains($path, '..')) {
            return false;
        }

        if (true === str_starts_with($path, '/')) {
            return false;
        }

        if (true === str_contains($path, '://')) {
            return false;
        }

        if (false === str_starts_with($path, 'uploads/tmp/')) {
            return false;
        }

        return true;
    }

    private function buildUploadFilename(string $originalName): string
    {
        $info = pathinfo($originalName);
        $base = $info['filename'] ?? 'image';
        $extension = isset($info['extension']) ? '.' . $info['extension'] : '';

        return $this->slugger->slug($base)->lower()->toString() . '_' . bin2hex(random_bytes(4)) . $extension;
    }
}
