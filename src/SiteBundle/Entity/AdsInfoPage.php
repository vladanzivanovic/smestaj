<?php

declare(strict_types=1);

namespace SiteBundle\Entity;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use SiteBundle\Repository\AdsInfoPageRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: AdsInfoPageRepository::class)]
#[ORM\Table(name: 'ads_info_page')]
#[ORM\HasLifecycleCallbacks]
class AdsInfoPage implements EntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Ads::class)]
    #[ORM\JoinColumn(name: 'linked_ads_id', referencedColumnName: 'Id', unique: true, nullable: false, onDelete: 'CASCADE')]
    private Ads $linkedAds;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Regex('/^[a-z0-9-]+$/')]
    private string $slug;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $published = false;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(groups: ['Default', 'publish'])]
    private string $propertyName = '';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $taglineRs = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $taglineEn = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $shortDescriptionRs = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $shortDescriptionEn = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $welcomeMessageRs = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $welcomeMessageEn = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    #[Assert\NotBlank(groups: ['publish'])]
    private ?string $hostFirstName = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    #[Assert\NotBlank(groups: ['publish'])]
    private ?string $hostLastName = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $hostMobile = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Url(requireTld: true)]
    private ?string $instagramUrl = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Url(requireTld: true)]
    private ?string $facebookUrl = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    #[Assert\Regex(pattern: '/^\+?[0-9 ()-]{7,30}$/', message: 'WhatsApp telefon mora biti u formatu +381 60 1234567.')]
    private ?string $whatsappPhone = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    #[Assert\Regex(pattern: '/^\+?[0-9 ()-]{7,30}$/', message: 'Viber telefon mora biti u formatu +381 60 1234567.')]
    private ?string $viberPhone = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Url(requireTld: true)]
    private ?string $bookingUrl = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Url(requireTld: true)]
    private ?string $airbnbUrl = null;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $googleReviewInput = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\NotBlank(groups: ['publish'])]
    private ?string $addressStreet = null;

    #[ORM\Column(type: 'string', length: 30, nullable: true)]
    private ?string $addressPostalCode = null;

    #[ORM\Column(type: 'string', length: 150, nullable: true)]
    #[Assert\NotBlank(groups: ['publish'])]
    private ?string $addressCity = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $googleMapsLat = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $googleMapsLng = null;

    #[ORM\Column(type: 'time_immutable', nullable: true)]
    #[Assert\NotBlank(groups: ['publish'])]
    private ?DateTimeInterface $checkInTime = null;

    #[ORM\Column(type: 'time_immutable', nullable: true)]
    #[Assert\NotBlank(groups: ['publish'])]
    private ?DateTimeInterface $checkOutTime = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    #[Assert\Length(max: 100)]
    private ?string $wifiUsername = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    #[Assert\Length(max: 100)]
    private ?string $wifiPassword = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $houseRules = null;

    /**
     * @var Collection<int, AdsInfoPageImage>
     */
    #[ORM\OneToMany(mappedBy: 'infoPage', targetEntity: AdsInfoPageImage::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    #[Assert\Count(min: 1, groups: ['publish'])]
    private Collection $images;

    /**
     * @var Collection<int, AdsInfoHasTag>
     */
    #[ORM\OneToMany(mappedBy: 'infoPage', targetEntity: AdsInfoHasTag::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $hasTags;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Gedmo\Timestampable(on: 'create')]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Gedmo\Timestampable(on: 'update')]
    private ?DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->hasTags = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLinkedAds(): Ads
    {
        return $this->linkedAds;
    }

    public function setLinkedAds(Ads $linkedAds): self
    {
        $this->linkedAds = $linkedAds;

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

    public function isPublished(): bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): self
    {
        $this->published = $published;

        return $this;
    }

    public function getPropertyName(): string
    {
        return $this->propertyName;
    }

    public function setPropertyName(string $propertyName): self
    {
        $this->propertyName = $propertyName;

        return $this;
    }

    public function getTaglineRs(): ?string
    {
        return $this->taglineRs;
    }

    public function setTaglineRs(?string $taglineRs): self
    {
        $this->taglineRs = $taglineRs;

        return $this;
    }

    public function getTaglineEn(): ?string
    {
        return $this->taglineEn;
    }

    public function setTaglineEn(?string $taglineEn): self
    {
        $this->taglineEn = $taglineEn;

        return $this;
    }

    public function getShortDescriptionRs(): ?string
    {
        return $this->shortDescriptionRs;
    }

    public function setShortDescriptionRs(?string $shortDescriptionRs): self
    {
        $this->shortDescriptionRs = $shortDescriptionRs;

        return $this;
    }

    public function getShortDescriptionEn(): ?string
    {
        return $this->shortDescriptionEn;
    }

    public function setShortDescriptionEn(?string $shortDescriptionEn): self
    {
        $this->shortDescriptionEn = $shortDescriptionEn;

        return $this;
    }

    public function getWelcomeMessageRs(): ?string
    {
        return $this->welcomeMessageRs;
    }

    public function setWelcomeMessageRs(?string $welcomeMessageRs): self
    {
        $this->welcomeMessageRs = $welcomeMessageRs;

        return $this;
    }

    public function getWelcomeMessageEn(): ?string
    {
        return $this->welcomeMessageEn;
    }

    public function setWelcomeMessageEn(?string $welcomeMessageEn): self
    {
        $this->welcomeMessageEn = $welcomeMessageEn;

        return $this;
    }

    public function getHostFirstName(): ?string
    {
        return $this->hostFirstName;
    }

    public function setHostFirstName(?string $hostFirstName): self
    {
        $this->hostFirstName = $hostFirstName;

        return $this;
    }

    public function getHostLastName(): ?string
    {
        return $this->hostLastName;
    }

    public function setHostLastName(?string $hostLastName): self
    {
        $this->hostLastName = $hostLastName;

        return $this;
    }

    public function getHostMobile(): ?string
    {
        return $this->hostMobile;
    }

    public function setHostMobile(?string $hostMobile): self
    {
        $this->hostMobile = $hostMobile;

        return $this;
    }

    public function getInstagramUrl(): ?string
    {
        return $this->instagramUrl;
    }

    public function setInstagramUrl(?string $instagramUrl): self
    {
        $this->instagramUrl = $instagramUrl;

        return $this;
    }

    public function getFacebookUrl(): ?string
    {
        return $this->facebookUrl;
    }

    public function setFacebookUrl(?string $facebookUrl): self
    {
        $this->facebookUrl = $facebookUrl;

        return $this;
    }

    public function getWhatsappPhone(): ?string
    {
        return $this->whatsappPhone;
    }

    public function setWhatsappPhone(?string $whatsappPhone): self
    {
        $this->whatsappPhone = $whatsappPhone;

        return $this;
    }

    public function getViberPhone(): ?string
    {
        return $this->viberPhone;
    }

    public function setViberPhone(?string $viberPhone): self
    {
        $this->viberPhone = $viberPhone;

        return $this;
    }

    public function getBookingUrl(): ?string
    {
        return $this->bookingUrl;
    }

    public function setBookingUrl(?string $bookingUrl): self
    {
        $this->bookingUrl = $bookingUrl;

        return $this;
    }

    public function getAirbnbUrl(): ?string
    {
        return $this->airbnbUrl;
    }

    public function setAirbnbUrl(?string $airbnbUrl): self
    {
        $this->airbnbUrl = $airbnbUrl;

        return $this;
    }

    public function getGoogleReviewInput(): ?string
    {
        return $this->googleReviewInput;
    }

    public function setGoogleReviewInput(?string $googleReviewInput): self
    {
        $this->googleReviewInput = $googleReviewInput;

        return $this;
    }

    public function getAddressStreet(): ?string
    {
        return $this->addressStreet;
    }

    public function setAddressStreet(?string $addressStreet): self
    {
        $this->addressStreet = $addressStreet;

        return $this;
    }

    public function getAddressPostalCode(): ?string
    {
        return $this->addressPostalCode;
    }

    public function setAddressPostalCode(?string $addressPostalCode): self
    {
        $this->addressPostalCode = $addressPostalCode;

        return $this;
    }

    public function getAddressCity(): ?string
    {
        return $this->addressCity;
    }

    public function setAddressCity(?string $addressCity): self
    {
        $this->addressCity = $addressCity;

        return $this;
    }

    public function getGoogleMapsLat(): ?float
    {
        return $this->googleMapsLat;
    }

    public function setGoogleMapsLat(?float $googleMapsLat): self
    {
        $this->googleMapsLat = $googleMapsLat;

        return $this;
    }

    public function getGoogleMapsLng(): ?float
    {
        return $this->googleMapsLng;
    }

    public function setGoogleMapsLng(?float $googleMapsLng): self
    {
        $this->googleMapsLng = $googleMapsLng;

        return $this;
    }

    public function getCheckInTime(): ?DateTimeInterface
    {
        return $this->checkInTime;
    }

    public function setCheckInTime(?DateTimeInterface $checkInTime): self
    {
        $this->checkInTime = $checkInTime;

        return $this;
    }

    public function getCheckOutTime(): ?DateTimeInterface
    {
        return $this->checkOutTime;
    }

    public function setCheckOutTime(?DateTimeInterface $checkOutTime): self
    {
        $this->checkOutTime = $checkOutTime;

        return $this;
    }

    public function getWifiUsername(): ?string
    {
        return $this->wifiUsername;
    }

    public function setWifiUsername(?string $wifiUsername): self
    {
        $this->wifiUsername = $wifiUsername;

        return $this;
    }

    public function getWifiPassword(): ?string
    {
        return $this->wifiPassword;
    }

    public function setWifiPassword(?string $wifiPassword): self
    {
        $this->wifiPassword = $wifiPassword;

        return $this;
    }

    public function getHouseRules(): ?string
    {
        return $this->houseRules;
    }

    public function setHouseRules(?string $houseRules): self
    {
        $this->houseRules = $houseRules;

        return $this;
    }

    /**
     * @return Collection<int, AdsInfoPageImage>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(AdsInfoPageImage $image): self
    {
        if (false === $this->images->contains($image)) {
            $this->images->add($image);
            $image->setInfoPage($this);
        }

        return $this;
    }

    public function removeImage(AdsInfoPageImage $image): self
    {
        if (true === $this->images->contains($image)) {
            $this->images->removeElement($image);
        }

        return $this;
    }

    /**
     * @return Collection<int, AdsInfoHasTag>
     */
    public function getHasTags(): Collection
    {
        return $this->hasTags;
    }

    public function addHasTag(AdsInfoHasTag $hasTag): self
    {
        if (false === $this->hasTags->contains($hasTag)) {
            $this->hasTags->add($hasTag);
            $hasTag->setInfoPage($this);
        }

        return $this;
    }

    public function removeHasTag(AdsInfoHasTag $hasTag): self
    {
        if (true === $this->hasTags->contains($hasTag)) {
            $this->hasTags->removeElement($hasTag);
        }

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Returns the value of a per-locale paired field (e.g. shortDescriptionRs/En) for the active locale.
     * Falls back to the Serbian variant when an unsupported locale is requested.
     */
    public function getCanonicalLocaleField(string $base, string $locale): ?string
    {
        $key = $base . '_' . strtolower($locale);

        return match ($key) {
            'tagline_rs' => $this->taglineRs,
            'tagline_en' => $this->taglineEn,
            'shortDescription_rs' => $this->shortDescriptionRs,
            'shortDescription_en' => $this->shortDescriptionEn,
            'welcomeMessage_rs' => $this->welcomeMessageRs,
            'welcomeMessage_en' => $this->welcomeMessageEn,
            default => match ($base) {
                'tagline' => $this->taglineRs,
                'shortDescription' => $this->shortDescriptionRs,
                'welcomeMessage' => $this->welcomeMessageRs,
                default => null,
            },
        };
    }

    #[Assert\Callback(groups: ['publish'])]
    public function validatePublishContactChannels(ExecutionContextInterface $context): void
    {
        if (null === $this->hostMobile) {
            $context->buildViolation('Host mobile is required when publishing.')
                ->atPath('hostMobile')
                ->addViolation();
        }

        if (null === $this->facebookUrl && null === $this->instagramUrl) {
            $context->buildViolation('At least one of Facebook or Instagram URL is required when publishing.')
                ->atPath('facebookUrl')
                ->addViolation();
        }
    }
}
