<?php

declare(strict_types=1);

namespace AdminBundle\Handler;

use AdminBundle\Model\InfoPageEditModel;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
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
        $this->reconcileImages($entity, $model->imageDeleteIds, $model->imageUploads, $model->imageStates);

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
     * @param array<int, int>                                              $deleteIds
     * @param array<int, UploadedFile>                                     $uploads
     * @param array<int, array{id: ?int, isMain: bool, fileName: ?string}> $imageStates
     */
    private function reconcileImages(AdsInfoPage $entity, array $deleteIds, array $uploads, array $imageStates): void
    {
        $persistedIds = [];

        foreach ($entity->getImages() as $image) {
            $persistedIds[(int) $image->getId()] = true;
        }

        $submittedExistingIds = [];
        $hasNewItems = false;

        foreach ($imageStates as $state) {
            if (null === $state['id']) {
                $hasNewItems = true;

                continue;
            }

            $submittedExistingIds[$state['id']] = true;
        }

        $isCaseB = (false === $hasNewItems)
            && (0 === count($deleteIds))
            && (0 === count($uploads))
            && (0 === count(array_diff_key($persistedIds, $submittedExistingIds)))
            && (0 === count(array_diff_key($submittedExistingIds, $persistedIds)));

        if (true === $isCaseB) {
            $persistedById = [];

            foreach ($entity->getImages() as $image) {
                $persistedById[(int) $image->getId()] = $image;
            }

            foreach ($imageStates as $state) {
                $stateId = $state['id'];

                if (null === $stateId) {
                    continue;
                }

                if (false === array_key_exists($stateId, $persistedById)) {
                    continue;
                }

                $persistedById[$stateId]->setIsMain($state['isMain']);
            }

            return;
        }

        $toRemove = new ArrayCollection();

        foreach ($entity->getImages() as $image) {
            if (true === in_array((int) $image->getId(), $deleteIds, true)) {
                $image->setDeleted(true);
                $toRemove->add($image);
            }
        }

        foreach ($toRemove as $image) {
            $entity->removeImage($image);
        }

        $position = $entity->getImages()->count();

        /** @var array<string, AdsInfoPageImage> $newByOriginalName */
        $newByOriginalName = [];

        foreach ($uploads as $upload) {
            $image = new AdsInfoPageImage();
            $image->setFile($upload);
            $image->setOriginalName($upload->getClientOriginalName());
            $image->setFilename($this->buildUploadFilename($upload->getClientOriginalName()));
            $image->setPosition($position);
            $entity->addImage($image);
            $newByOriginalName[$upload->getClientOriginalName()] = $image;
            $position++;
        }

        $survivingById = [];

        foreach ($entity->getImages() as $image) {
            $id = $image->getId();

            if (null !== $id) {
                $survivingById[(int) $id] = $image;
            }
        }

        foreach ($imageStates as $state) {
            if (null !== $state['id'] && true === array_key_exists($state['id'], $survivingById)) {
                $survivingById[$state['id']]->setIsMain($state['isMain']);

                continue;
            }

            if (null !== $state['fileName'] && true === array_key_exists($state['fileName'], $newByOriginalName)) {
                $newByOriginalName[$state['fileName']]->setIsMain($state['isMain']);
            }
        }
    }

    private function buildUploadFilename(string $originalName): string
    {
        $info = pathinfo($originalName);
        $base = $info['filename'] ?? 'image';
        $extension = isset($info['extension']) ? '.' . $info['extension'] : '';

        return $this->slugger->slug($base)->lower()->toString() . '_' . bin2hex(random_bytes(4)) . $extension;
    }
}
