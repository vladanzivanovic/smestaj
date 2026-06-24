<?php

declare(strict_types=1);

namespace SiteBundle\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'ads_info_page_image')]
final class AdsInfoPageImage implements EntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: AdsInfoPage::class, inversedBy: 'images')]
    #[ORM\JoinColumn(name: 'info_page_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private AdsInfoPage $infoPage;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $filename = null;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $originalName = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $position = 0;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isMain = false;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Gedmo\Timestampable(on: 'create')]
    private ?DateTimeImmutable $createdAt = null;

    #[Assert\File(maxSize: '2M', mimeTypes: ['image/jpeg', 'image/png', 'image/gif'])]
    private ?UploadedFile $file = null;

    private bool $isDeleted = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInfoPage(): AdsInfoPage
    {
        return $this->infoPage;
    }

    public function setInfoPage(AdsInfoPage $infoPage): self
    {
        $this->infoPage = $infoPage;

        return $this;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(?string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function getOriginalName(): ?string
    {
        return $this->originalName;
    }

    public function setOriginalName(?string $originalName): self
    {
        $this->originalName = $originalName;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function isMain(): bool
    {
        return $this->isMain;
    }

    public function setIsMain(bool $isMain): self
    {
        $this->isMain = $isMain;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    public function setFile(?UploadedFile $file): self
    {
        $this->file = $file;

        return $this;
    }

    public function isDeleted(): bool
    {
        return $this->isDeleted;
    }

    public function setDeleted(bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->filename;
    }
}
