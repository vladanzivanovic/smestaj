<?php

namespace SiteBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\Table;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="SiteBundle\Repository\TagRepository")
 * @Table(name="tag",indexes={@Index(name="tag_type_id", columns={"tag_type_id"})})
 */
class Tag implements EntityInterface
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private ?int $id = null;

    /**
     * @var string
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="name", type="string")
     */
    private string $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Slug(fields={"name"}, updatable=false)
     */
    private ?string $slug = null;

    /**
     * @var string
     *
     * @ORM\Column(name="icon", type="string", length=50)
     */
    private string $icon;

    /**
     * @var TagType
     * @ORM\ManyToOne(targetEntity="SiteBundle\Entity\TagType")
     * @JoinColumn(name="tag_type_id", referencedColumnName="id")
     */
    private TagType $tagType;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="SiteBundle\Entity\Adshastags", mappedBy="tag")
     */
    private Collection $hasTag;

    public function __construct()
    {
        $this->hasTag = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setTagType(TagType $tagType): self
    {
        $this->tagType = $tagType;

        return $this;
    }

    public function getTagType(): TagType
    {
        return $this->tagType;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    public function setIcon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function addHasTag(Adshastags $hasTag): self
    {
        if ($this->hasTag->contains($hasTag)) {
            $this->hasTag[] = $hasTag;
            $hasTag->setTag($this);
        }

        return $this;
    }

    public function removeHasTag(Adshastags $hasTag)
    {
        if ($this->hasTag->contains($hasTag)) {
            $this->hasTag->removeElement($hasTag);

            if ($hasTag->getTag() === $this) {
                $hasTag->setTag(null);
            }
        }
    }

    public function getHasTag(): Collection
    {
        return $this->hasTag;
    }

    /**
     * @return string|null
     */
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    /**
     * @param string|null $slug
     */
    public function setSlug(?string $slug): void
    {
        $this->slug = $slug;
    }
}
