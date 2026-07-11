<?php

declare(strict_types=1);

namespace SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use SiteBundle\Repository\AdsInfoHasTagRepository;

#[ORM\Entity(repositoryClass: AdsInfoHasTagRepository::class)]
#[ORM\Table(name: 'ads_info_has_tag')]
#[ORM\Index(name: 'IDX_aiht_info_page', columns: ['info_page_id'])]
#[ORM\Index(name: 'IDX_aiht_tag', columns: ['tag_id'])]
#[ORM\UniqueConstraint(name: 'uniq_info_page_tag', columns: ['info_page_id', 'tag_id'])]
class AdsInfoHasTag implements EntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: AdsInfoPage::class, inversedBy: 'hasTags')]
    #[ORM\JoinColumn(name: 'info_page_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private AdsInfoPage $infoPage;

    #[ORM\ManyToOne(targetEntity: Tag::class)]
    #[ORM\JoinColumn(name: 'tag_id', referencedColumnName: 'id', nullable: false, onDelete: 'RESTRICT')]
    private Tag $tag;

    #[ORM\Column(type: 'string', length: 100)]
    private string $value = '1';

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

    public function getTag(): Tag
    {
        return $this->tag;
    }

    public function setTag(Tag $tag): self
    {
        $this->tag = $tag;

        return $this;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }
}
