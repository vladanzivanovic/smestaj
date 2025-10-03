<?php

namespace SiteBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * AdsPayedDate
 *
 * @ORM\Table(name="ads_payed_date")
 * @ORM\Entity(repositoryClass="SiteBundle\Repository\AdspayeddateRepository")
 */
class AdsPayedDate implements EntityInterface, EntityStatusInterface
{
    public const PAYMENT_PLAN_BASIC = 1;
    public const PAYMENT_PLAN_BUSINESS = 10;

    public const PAYMENT_PLAN_PREMIUM = 20;

    use ResourceTrait;
    use StatusTrait;

    /**
     * @ORM\ManyToOne(targetEntity="SiteBundle\Entity\Ads", inversedBy="payedDate")
     * @ORM\JoinColumn(name="ads", referencedColumnName="Id", nullable=true)
     */
    private ?Ads $ads;

    /**
     * @ORM\Column(type="datetime")
     */
    private \DateTimeInterface $date;

    /**
     * @ORM\Column(name="type", type="smallint", length=2)
     */
    private int $type = self::PAYMENT_PLAN_BASIC;

    public function setAds(?Ads $ads): self
    {
        $this->ads = $ads;

        return $this;
    }

    public function getAds(): Ads
    {
        return $this->ads;
    }

    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): void
    {
        $this->type = $type;
    }
}
