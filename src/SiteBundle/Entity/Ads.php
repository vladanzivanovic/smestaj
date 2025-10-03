<?php

namespace SiteBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\Groups;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Ads
 *
 * @ORM\Table(
 *     name="ads",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="alias", columns={"alias"})},
 *     indexes={@ORM\Index(name="AdsCategoryId", columns={"CategoryId"}), @ORM\Index(name="AdsCityId", columns={"CityId"})}
 * )
 * @ORM\Entity(repositoryClass="SiteBundle\Repository\AdsRepository")
 */
class Ads implements EntityInterface
{
    use ResourceTrait;

    const AD_NUMBER_CLICKED = 'ad_number_clicked';

    const PRICE_TYPE_PRE_SEASON = 'price_pre_season';

    const PRICE_TYPE_SEASON = 'price_season';

    const PRICE_TYPE_POST_SEASON = 'price_post_season';

    /**
     * @var string
     *
     * @Groups({"adsGroup"})
     *
     * @ORM\Column(name="Title", type="string", length=250, nullable=false)
     */
    private string $title;

    /**
     * @var string
     *
     * @Groups({"adsGroup"})
     *
     * @ORM\Column(name="Alias", type="string", length=250, nullable=false)
     * @Gedmo\Slug(fields={"title"}, updatable=false)
     */
    private string $alias;

    /**
     * @Groups({"adsGroup"})
     *
     * @ORM\Column(name="Description", type="text", nullable=false)
     */
    private ?string $description = null;

    /**
     * @Groups({"adsGroup"})
     *
     * @ORM\Column(name="ShortDescription", type="text", length=65535, nullable=false)
     */
    private ?string $shortDescription = null;

    /**
     * @var integer
     *
     * @Groups({"adsGroup"})
     *
     * @ORM\Column(name="PrePriceFrom", type="integer", nullable=false)
     */
    private int $prepricefrom;

    /**
     * @var integer
     *
     * @Groups({"adsGroup"})
     *
     * @ORM\Column(name="PrePriceTo", type="integer", nullable=true)
     */
    private int $prepriceto;

    /**
     * @Groups({"adsGroup"})
     *
     * @ORM\Column(name="PriceFrom", type="integer", nullable=false)
     */
    private ?int $priceFrom = null;

    /**
     * @Groups({"adsGroup"})
     *
     * @ORM\Column(name="PriceTo", type="integer", nullable=true)
     */
    private ?int $priceto = null;

    /**
     * @var integer
     *
     * @Groups({"adsGroup"})
     *
     * @ORM\Column(name="PostPriceFrom", type="integer", nullable=false)
     */
    private $postpricefrom;

    /**
     * @var integer
     *
     * @Groups({"adsGroup"})
     *
     * @ORM\Column(name="PostPriceTo", type="integer", nullable=true)
     */
    private $postpriceto;

    /**
     * @Groups({"adsGroup"})
     *
     * @ORM\Column(name="SysCreatedTime", type="datetime", nullable=false)
     */
    private ?\DateTimeInterface $sysCreatedTime = null;

    /**
     *
     * @Groups({"adsGroup"})
     *
     * @ORM\Column(name="SysModifyTime", type="datetime", nullable=false)
     */
    private \DateTimeInterface $sysModifyTime;

    /**
     * @var User
     *
     * @Groups({"adsGroup"})
     *
     * @ORM\ManyToOne(targetEntity="SiteBundle\Entity\User", inversedBy="sysCreatedAds")
     * @ORM\JoinColumn(name="SysCreatedUserId", referencedColumnName="Id")
     * @Serializer\Exclude(if="true")
     */
    private User $sysCreatedUserId;

    /**
     * @Groups({"adsGroup"})
     *
     * @ORM\ManyToOne(targetEntity="SiteBundle\Entity\User")
     * @ORM\JoinColumn(name="SysModifyUserId", referencedColumnName="Id")
     * @Serializer\Exclude(if="true")
     */
    private User $sysModifyUserId;

    /**
     * @Groups({"adsGroup"})
     *
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="adsid")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="CategoryId", referencedColumnName="Id")
     * })
     */
    private Category $categoryId;

    /**
     * @Groups({"adsGroup"})
     *
     * @ORM\ManyToOne(targetEntity="City")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="CityId", referencedColumnName="Id")
     * })
     */
    private City $cityId;

    /**
     * @Groups({"adsYouTube"})
     * @Serializer\Exclude(if="true")
     * @ORM\OneToMany(targetEntity="SiteBundle\Entity\Youtubeinfo", mappedBy="adsid", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $youtubeIds;

    /**
     * @Groups({"adsMedia"})
     * @Serializer\Exclude(if="true")
     * @ORM\OneToMany(targetEntity="SiteBundle\Entity\Media", mappedBy="adsid", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $media;

    /**
     * @var $status
     * @Groups({"adsGroup"})
     * @ORM\Column(name="Status", type="smallint", nullable=true, options={"default": 0})
     */
    private $status;

    /**
     * @Groups({"adsReservations"})
     * @Serializer\Exclude(if="true")
     * @ORM\OneToMany(targetEntity="SiteBundle\Entity\Reservation", mappedBy="adsid")
     */
    private $reservations;

    /**
     * @var integer
     * @ORM\Column(name="PublicPrice", type="smallint", nullable=true, options={"default": 0})
     */
    private $publicprice;

    /**
     * @ORM\OneToMany(targetEntity="SiteBundle\Entity\AdsAdditionalInfo", mappedBy="adsid", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $adsadditionalinfo;

    /**
     * @ORM\OneToOne (targetEntity="SiteBundle\Entity\Contact", inversedBy="ad", cascade={"persist"})
     * @ORM\JoinColumn(name="contact", referencedColumnName="Id", nullable=false)
     * @Assert\Valid(groups={"SetAd", "SetAdAdmin"})
     */
    private ?Contact $contact = null;

    /**
     * @ORM\ManyToOne(targetEntity="SiteBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(referencedColumnName="Id", nullable=true)
     * @Assert\Valid(groups={"SetAd"})
     */
    private ?UserInterface $owner;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="SiteBundle\Entity\Adshastags", mappedBy="ads", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $hasTags;

    /**
     * @ORM\OneToMany(targetEntity="SiteBundle\Entity\AdsPayedDate", mappedBy="ads", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private Collection $payedDate;

    /**
     * @var float|null
     * @ORM\Column(type="float", nullable=true)
     * @Assert\NotBlank(message="fields.required", groups={"SetAdminAd"})
     */
    private ?float $lat = null;

    /**
     * @var float|null
     * @ORM\Column(type="float", nullable=true)
     * @Assert\NotBlank(message="fields.required", groups={"SetAdminAd"})
     */
    private ?float $lng = null;

    /**
     * @var string|null
     * @ORM\Column(nullable=true)
     * @Assert\NotBlank(message="fields.required", groups={"SetAdminAd"})
     */
    private ?string $address = null;

    /**
     * @var integer
     * @ORM\Column(type="integer", nullable=false, options={"default": 0})
     */
    private int $phoneNumberCounter = 0;

    /**
     * @var bool
     */
    private bool $sendEmail = true;

    /**
     * @ORM\Column(nullable=true, type="string", length=250)
     */
    private ?string $website = null;

    /**
     * @ORM\Column(nullable=true, type="string", length=250)
     */
    private ?string $facebook = null;


    /**
     * @ORM\Column(nullable=true, type="string", length=250)
     */
    private ?string $instagram = null;


    public function __construct()
    {
        $this->youtubeIds = new ArrayCollection();
        $this->media = new ArrayCollection();
        $this->reservations = new ArrayCollection();
        $this->adsadditionalinfo = new ArrayCollection();
        $this->hasTags = new ArrayCollection();
        $this->payedDate = new ArrayCollection();
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setAlias(string $alias): void
    {
        $this->alias = $alias;
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setShortDescription(string $shortDescription): void
    {
        $this->shortDescription = $shortDescription;
    }

    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    public function setPriceFrom(int $priceFrom): void
    {
        $this->priceFrom = $priceFrom;
    }

    public function getPriceFrom(): ?int
    {
        return $this->priceFrom;
    }

    public function setPriceTo(int $priceto): void
    {
        $this->priceto = $priceto;
    }

    public function getPriceTo(): int
    {
        return $this->priceto;
    }

    public function setSyscreatedTime(\DateTimeInterface $sysCreatedTime): void
    {
        $this->sysCreatedTime = $sysCreatedTime;
    }

    public function getSysCreatedTime(): ?\DateTimeInterface
    {
        return $this->sysCreatedTime;
    }

    public function setSysModifyTime(\DateTimeInterface $sysModifyTime): void
    {
        $this->sysModifyTime = $sysModifyTime;
    }

    public function getSysModifyTime(): \DateTimeInterface
    {
        return $this->sysModifyTime;
    }

    public function setSysCreatedUserId(User $sysCreatedUserId): void
    {
        $this->sysCreatedUserId = $sysCreatedUserId;
    }

    public function getSysCreatedUserId(): User
    {
        return $this->sysCreatedUserId;
    }

    public function setSysModifyUserId(User $sysModifyUserId): void
    {
        $this->sysModifyUserId = $sysModifyUserId;
    }

    public function getSysModifyUserId(): User
    {
        return $this->sysModifyUserId;
    }

    public function setCategoryId(Category $categoryId): void
    {
        $this->categoryId = $categoryId;
    }

    public function getCategoryId(): Category
    {
        return $this->categoryId;
    }

    public function setCityId(City $cityId): void
    {
        $this->cityId = $cityId;
    }

    public function getCityId(): City
    {
        return $this->cityId;
    }

    /**
     * Add youtubeIds
     *
     * @param Youtubeinfo $youtube
     *
     * @return Ads
     */
    public function addYoutube(Youtubeinfo $youtube): self
    {
        if (!$this->youtubeIds->contains($youtube)) {
            $this->youtubeIds[] = $youtube;

            $youtube->setAdsid($this);
        }

        return $this;
    }

    /**
     * Remove youtubeIds
     *
     * @param Youtubeinfo $youtube
     */
    public function removeYoutube(Youtubeinfo $youtube)
    {
        $this->youtubeIds->removeElement($youtube);
    }

    public function getYoutube(): Collection
    {
        return $this->youtubeIds;
    }

    public function addMedia(Media $media): void
    {
        if (false === $this->hasMedia($media)) {
            $this->media->add($media);

            $media->setAdsid($this);
        }
    }

    public function removeMedia(Media $media): void
    {
        if (true === $this->hasMedia($media)) {
            $this->media->removeElement($media);

            $media->setAdsid(null);
        }
    }

    /**
     * @return Collection|Media[]
     */
    public function getMedia(): Collection
    {
        return $this->media;
    }

    public function getMainImage(): ?Media
    {
        $mainImage = $this->media->filter(function (Media $media) {
            return true === $media->getIsmain();
        });

        return 0 < $mainImage->count() ? $mainImage->first() : null;
    }

    public function hasMedia(Media $media): bool
    {
        return $this->media->contains($media);
    }

    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * Add reservation
     *
     * @param Reservation $reservation
     *
     * @return Ads
     */
    public function addReservation(Reservation $reservation)
    {
        $this->reservations[] = $reservation;

        return $this;
    }

    /**
     * Remove reservation
     *
     * @param Reservation $reservation
     */
    public function removeReservation(Reservation $reservation)
    {
        $this->reservations->removeElement($reservation);
    }

    /**
     * Get reservations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReservations()
    {
        return $this->reservations;
    }

    /**
     * Set publicprice
     *
     * @param integer $publicprice
     *
     * @return Ads
     */
    public function setPublicprice($publicprice)
    {
        $this->publicprice = $publicprice;

        return $this;
    }

    /**
     * Get publicprice
     *
     * @return integer
     */
    public function getPublicprice()
    {
        return $this->publicprice;
    }

    public function setContact(User $contact = null): void
    {
        $this->contact = $contact;
    }

    public function getContact(): ?Contact
    {
        return $this->contact;
    }

    public function setOwner(?UserInterface $user): self
    {
        $this->owner = $user;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    /**
     * Add adsadditionalinfo
     *
     * @param AdsAdditionalInfo $adsadditionalinfo
     *
     * @return Ads
     */
    public function addAdsadditionalinfo(AdsAdditionalInfo $adsadditionalinfo)
    {
        $this->adsadditionalinfo[] = $adsadditionalinfo;

        return $this;
    }

    /**
     * Remove adsadditionalinfo
     *
     * @param AdsAdditionalInfo $adsadditionalinfo
     */
    public function removeAdsadditionalinfo(AdsAdditionalInfo $adsadditionalinfo)
    {
        $this->adsadditionalinfo->removeElement($adsadditionalinfo);
    }

    /**
     * Get adsadditionalinfo
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAdsadditionalinfo()
    {
        return $this->adsadditionalinfo;
    }

    /**
     * Add hasTag
     *
     * @param \SiteBundle\Entity\Adshastags $hasTag
     *
     * @return Ads
     */
    public function addHasTag(\SiteBundle\Entity\Adshastags $hasTag)
    {
        $this->hasTags[] = $hasTag;

        return $this;
    }

    /**
     * Remove hasTag
     *
     * @param \SiteBundle\Entity\Adshastags $hasTag
     */
    public function removeHasTag(\SiteBundle\Entity\Adshastags $hasTag)
    {
        $this->hasTags->removeElement($hasTag);
    }

    public function getHasTags(): Collection
    {
        return $this->hasTags;
    }

    /**
     * Set prepricefrom
     *
     * @param integer $prepricefrom
     *
     * @return Ads
     */
    public function setPrepricefrom($prepricefrom)
    {
        $this->prepricefrom = $prepricefrom;

        return $this;
    }

    /**
     * Get prepricefrom
     *
     * @return integer
     */
    public function getPrepricefrom()
    {
        return $this->prepricefrom;
    }

    /**
     * Set prepriceto
     *
     * @param integer $prepriceto
     *
     * @return Ads
     */
    public function setPrepriceto($prepriceto)
    {
        $this->prepriceto = $prepriceto;

        return $this;
    }

    /**
     * Get prepriceto
     *
     * @return integer
     */
    public function getPrepriceto()
    {
        return $this->prepriceto;
    }

    /**
     * Set postpricefrom
     *
     * @param integer $postpricefrom
     *
     * @return Ads
     */
    public function setPostpricefrom($postpricefrom)
    {
        $this->postpricefrom = $postpricefrom;

        return $this;
    }

    /**
     * Get postpricefrom
     *
     * @return integer
     */
    public function getPostpricefrom()
    {
        return $this->postpricefrom;
    }

    /**
     * Set postpriceto
     *
     * @param integer $postpriceto
     *
     * @return Ads
     */
    public function setPostpriceto($postpriceto)
    {
        $this->postpriceto = $postpriceto;

        return $this;
    }

    /**
     * Get postpriceto
     *
     * @return integer
     */
    public function getPostpriceto()
    {
        return $this->postpriceto;
    }

    /**
     * @param AdsPayedDate $payedDate
     *
     * @return $this
     */
    public function addPayedType(AdsPayedDate $payedDate): Ads
    {
        if (false === $this->payedDate->contains($payedDate)) {
            $this->payedDate[] = $payedDate;

            $payedDate->setAds($this);
        }

        return $this;
    }

    public function getPayedTypes(): Collection
    {
        return $this->payedDate;
    }

    public function getPayedTypesByStatus(int $status): Collection
    {
        return $this->payedDate->filter(function (AdsPayedDate $payedDate) use ($status) {
            return $payedDate->getStatus() === $status;
        });
    }

    public function getActivePayment(): ?AdsPayedDate
    {
        $activePayments = $this->getPayedTypesByStatus(EntityStatusInterface::STATUS_ACTIVE);

        return false === $activePayments->isEmpty() ? $activePayments->first() : null;
    }

    public function getLastPayment(): ?AdsPayedDate
    {
        $payments = $this->payedDate;

        return false === $payments->isEmpty() ? $payments->last() : null;
    }

    public function removePayedType(AdsPayedDate $payedDate): Ads
    {
        if (true === $this->payedDate->contains($payedDate)) {
            $this->payedDate->removeElement($payedDate);
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getPhoneNumberCounter(): int
    {
        return $this->phoneNumberCounter;
    }

    /**
     * @param int $phoneNumberCounter
     *
     * @return Ads
     */
    public function setPhoneNumberCounter(int $phoneNumberCounter): Ads
    {
        $this->phoneNumberCounter = $phoneNumberCounter;

        return $this;
    }

    /**
     * @return bool
     */
    public function getSendEmail(): bool
    {
        return $this->sendEmail;
    }

    /**
     * @param bool $sendEmail
     *
     * @return Ads
     */
    public function setSendEmail(bool $sendEmail): Ads
    {
        $this->sendEmail = $sendEmail;

        return $this;
    }

    /**
     * @param float|null $lat
     *
     * @return Ads
     */
    public function setLat(?float $lat): Ads
    {
        $this->lat = $lat;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getLat(): ?float
    {
        return $this->lat;
    }

    /**
     * @param float|null $lng
     *
     * @return Ads
     */
    public function setLng(?float $lng): Ads
    {
        $this->lng = $lng;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getLng(): ?float
    {
        return $this->lng;
    }

    /**
     * @param string|null $address
     *
     * @return Ads
     */
    public function setAddress(?string  $address): Ads
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return string | null
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setWebsite(?string $website): self
    {
        $this->website = $website;

        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setFacebook(?string $facebook): self
    {
        $this->facebook = $facebook;

        return $this;
    }

    public function getFacebook(): ?string
    {
        return $this->facebook;
    }

    public function setInstagram(?string $instagram): self
    {
        $this->instagram = $instagram;

        return $this;
    }

    public function getInstagram(): ?string
    {
        return $this->instagram;
    }
}
