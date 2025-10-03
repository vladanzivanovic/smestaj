<?php

namespace SiteBundle\Entity;

use App\Entity\Image;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Media
 *
 * @ORM\Table(name="media", indexes={@ORM\Index(name="MediaAdsId", columns={"AdsId"})})
 * @ORM\Entity(repositoryClass="SiteBundle\Repository\MediaRepository")
 */
class Media implements EntityInterface
{
    use ResourceTrait;

    /**
     * @ORM\Column(name="Name", type="string", length=500, nullable=false)
     */
    private string $name;

    /**
     * @ORM\Column(length=500, nullable=false)
     * @Gedmo\Slug(fields={"name"})
     */
    private string $slug;

    /**
     * @ORM\Column(length=500, nullable=false)
     */
    private string $originalName;

    /**
     * @ORM\Column(name="IsMain", type="boolean", nullable=false)
     */
    private bool $ismain = false;

    /**
     * @ORM\ManyToOne(targetEntity="Ads", inversedBy="me    dia")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="AdsId", referencedColumnName="Id")
     * })
     */
    private ?Ads $adsid = null;

    /**
     * @ORM\ManyToOne(targetEntity="SiteBundle\Entity\AdsAdditionalInfo", inversedBy="mediainfo")
     * @ORM\JoinColumn(name="AdsInfoId", referencedColumnName="Id")
     */
    private ?AdsAdditionalInfo $adsinfoid = null;

    /**
     * @Assert\Image(maxSize="10M")
     */
    private ?UploadedFile $file = null;

    private bool $isDeleted = false;

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setSyscreatedtime(\DateTimeInterface $syscreatedtime): self
    {
        $this->syscreatedtime = $syscreatedtime;

        return $this;
    }

    public function getSyscreatedtime(): \DateTimeInterface
    {
        return $this->syscreatedtime;
    }

    public function setSyscreatorid(User $syscreatorid): self
    {
        $this->syscreatorid = $syscreatorid;

        return $this;
    }

    public function getSyscreatorid(): User
    {
        return $this->syscreatorid;
    }

    public function setIsmain(bool $ismain): self
    {
        $this->ismain = $ismain;

        return $this;
    }

    public function getIsmain(): bool
    {
        return $this->ismain;
    }

    public function setAdsid(?Ads $adsid = null): self
    {
        $this->adsid = $adsid;

        return $this;
    }

    public function getAdsid(): ?Ads
    {
        return $this->adsid;
    }

    public function setAdsinfoid(?AdsAdditionalInfo $adsinfoid = null): self
    {
        $this->adsinfoid = $adsinfoid;

        return $this;
    }

    public function getAdsinfoid(): ?AdsAdditionalInfo
    {
        return $this->adsinfoid;
    }

    public function getOriginalName(): string
    {
        return $this->originalName;
    }

    public function setOriginalName(string $name): self
    {
        $this->originalName = $name;

        return $this;
    }

    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    public function setFile(UploadedFile $file): self
    {
        $this->file = $file;

        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDeleted(): bool
    {
        return $this->isDeleted;
    }

    /**
     * @param bool $isDeleted
     *
     * @return $this
     */
    public function setIsDeleted(bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }
}
