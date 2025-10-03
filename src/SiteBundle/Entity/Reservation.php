<?php

namespace SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Reservation
 *
 * @ORM\Table(name="reservation", indexes={@ORM\Index(name="ReservationAdsId", columns={"AdsId"})})
 * @ORM\Entity(repositoryClass="SiteBundle\Repository\ReservationRepository")
 */
class Reservation
{
    const NOTIFICATION_ANY = 0;
    const NOTIFICATION_SMS = 1;
    const NOTIFICATION_EMAIL = 2;
    const NOTIFICATION_PHONE = 3;

    /**
     * @ORM\Column(name="Id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="Note", type="text", length=65535, nullable=true)
     */
    private ?string $note;

    /**
     * @ORM\Column(name="SysModifiedTime", type="datetime", nullable=false)
     * @Gedmo\Timestampable(on="create")
     */
    private \DateTimeInterface $sysmodifiedtime;

    /**
     * @ORM\Column(name="AdultNumber", type="integer", nullable=true)
     */
    private int $adultnumber = 0;

    /**
     * @ORM\Column(name="ChildrenNumber", type="integer", nullable=true)
     */
    private ?int $childrennumber = 0;

    /**
     * @ORM\Column(name="CheckIn", type="date")
     */
    private \DateTimeInterface $checkin;

    /**
     * @ORM\Column(name="CheckOut", type="date")
     */
    private \DateTimeInterface $checkout;

    /**
     * @ORM\ManyToOne(targetEntity="Ads", inversedBy="reservations")
     * @ORM\JoinColumn(name="AdsId", referencedColumnName="Id", nullable=false)
     */
    private Ads $adsid;

    /**
     * @ORM\Column(name="FirstName", type="string", length=100)
     */
    private string $firstname;

    /**
     * @ORM\Column(name="LastName", type="string", length=100)
     */
    private string $lastname;

    /**
     * @ORM\Column(name="Email", type="string", length=255, nullable=true);
     */
    private ?string $email;

    /**
     * @ORM\Column(name="Mobile", type="string", length=100);
     */
    private string $mobile;

    /**
     * @ORM\Column(name="Viber", type="string", length=100, nullable=true);
     */
    private ?string $viber;

    /**
     * @ORM\Column(type="smallint", length=1, nullable=false)
     */
    private int $notificationType;

    public function getId(): int
    {
        return $this->id;
    }

    public function setNote(?string $note): self
    {
        $this->note = $note;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setSysmodifiedtime(\DateTimeInterface $sysmodifiedtime): self
    {
        $this->sysmodifiedtime = $sysmodifiedtime;

        return $this;
    }

    public function getSysmodifiedtime(): \DateTimeInterface
    {
        return $this->sysmodifiedtime;
    }

    public function setAdultnumber(int $adultnumber): self
    {
        $this->adultnumber = $adultnumber;

        return $this;
    }

    public function getAdultnumber(): int
    {
        return $this->adultnumber;
    }

    public function setChildrennumber(?int $childrennumber): self
    {
        $this->childrennumber = $childrennumber;

        return $this;
    }

    public function getChildrennumber(): ?int
    {
        return $this->childrennumber;
    }

    public function setCheckin(\DateTimeInterface $checkin): self
    {
        $this->checkin = $checkin;

        return $this;
    }

    public function getCheckin(): \DateTimeInterface
    {
        return $this->checkin;
    }

    public function setCheckout(\DateTimeInterface $checkout): self
    {
        $this->checkout = $checkout;

        return $this;
    }

    public function getCheckout(): \DateTimeInterface
    {
        return $this->checkout;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setMobile(string $mobile): self
    {
        $this->mobile = $mobile;

        return $this;
    }

    public function getMobile(): string
    {
        return $this->mobile;
    }

    public function setViber(?string $viber): self
    {
        $this->viber = $viber;

        return $this;
    }

    public function getViber(): ?string
    {
        return $this->viber;
    }

    public function setAdsid(Ads $adsId = null): self
    {
        $this->adsid = $adsId;

        return $this;
    }

    public function getAdsid(): Ads
    {
        return $this->adsid;
    }

    public function setNotificationType(int $notificationType): Reservation
    {
        $this->notificationType = $notificationType;

        return $this;
    }

    public function getNotificationType(): int
    {
        return $this->notificationType;
    }
}
