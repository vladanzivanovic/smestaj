<?php

namespace SiteBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Category
 *
 *
 * @ORM\Table(name="category")
 * @ORM\Entity(repositoryClass="SiteBundle\Repository\CategoryRepository")
 */
class Category implements EntityInterface
{
    use ResourceTrait;

    /**
     * @ORM\Column(name="Name", type="string", length=250, nullable=false)
     */
    private ?string $name = null;

    /**
     * @ORM\Column(name="Alias", type="string", length=250, nullable=false)
     */
    private ?string $alias = null;

    /**
     * @ORM\Column(name="Image", type="string", length=250, nullable=true)
     */
    private ?string $image = null;

    /**
     * @ORM\Column(name="SysCreatedTime", type="datetime", nullable=false)
     */
    private \DateTimeInterface $syscreatedtime;

    /**
     * @ORM\Column(name="SysModifyTime", type="datetime", nullable=false)
     */
    private \DateTimeInterface $sysmodifytime;

    /**
     * @ORM\ManyToOne(targetEntity="SiteBundle\Entity\User")
     * @ORM\JoinColumn(name="SysCreatedUserId", referencedColumnName="Id")
     */
    private ?UserInterface $syscreateduserid = null;

    /**
     * @ORM\ManyToOne(targetEntity="SiteBundle\Entity\User")
     * @ORM\JoinColumn(name="SysModifyUserId", referencedColumnName="Id")
     */
    private ?UserInterface $sysmodifyuserid = null;

    /**
     * @ORM\Column(name="Status", type="smallint")
     */
    private int $status;

    /**
     * @ORM\OneToMany(targetEntity="SiteBundle\Entity\Ads", mappedBy="categoryId")
     */
    private Collection $adsid;

    /**
     * @ORM\ManyToOne(targetEntity="SiteBundle\Entity\Category", inversedBy="childCategory")
     * @ORM\JoinColumn(name="ParentId", referencedColumnName="Id")
     */
    private ?Category $parent = null;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="SiteBundle\Entity\Category", mappedBy="parent")
     */
    private Collection $childCategory;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->adsid = new ArrayCollection();
        $this->childCategory = new ArrayCollection();
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setAlias(string $alias): self
    {
        $this->alias = $alias;

        return $this;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }

    public function setImage($image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setSyscreatedtime(\DateTimeInterface $syscreatedtime): self
    {
        $this->syscreatedtime = $syscreatedtime;

        return $this;
    }

    public function getSyscreatedtime(): \DateTime
    {
        return $this->syscreatedtime;
    }

    public function setSysmodifytime(\DateTimeInterface $sysmodifytime): self
    {
        $this->sysmodifytime = $sysmodifytime;

        return $this;
    }

    public function getSysmodifytime(): \DateTimeInterface
    {
        return $this->sysmodifytime;
    }

    public function setSyscreateduserid(UserInterface $syscreateduserid): self
    {
        $this->syscreateduserid = $syscreateduserid;

        return $this;
    }

    public function getSyscreateduserid(): ?UserInterface
    {
        return $this->syscreateduserid;
    }

    public function setSysmodifyuserid(UserInterface $sysmodifyuserid): self
    {
        $this->sysmodifyuserid = $sysmodifyuserid;

        return $this;
    }

    public function getSysmodifyuserid(): ?UserInterface
    {
        return $this->sysmodifyuserid;
    }

    public function addAdsid(Ads $adsid): self
    {
        $this->adsid[] = $adsid;

        return $this;
    }

    public function removeAdsid(Ads $adsid): void
    {
        $this->adsid->removeElement($adsid);
    }

    public function getadsid(): Collection
    {
        return $this->adsid;
    }

    public function setParent(?Category $parent = null): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function getParent(): ?Category
    {
        return $this->parent;
    }

    public function addChildCategory(Category $childCategory): self
    {
        $this->childCategory[] = $childCategory;

        return $this;
    }

    public function removeChildCategory(Category $childCategory): void
    {
        $this->childCategory->removeElement($childCategory);
    }

    public function getChildCategory(): Collection
    {
        return $this->childCategory;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getStatus(): int
    {
        return $this->status;
    }
}
