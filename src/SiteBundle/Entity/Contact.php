<?php

namespace SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Entity(repositoryClass="SiteBundle\Repository\ContactRepository")
 */
class Contact extends User implements EntityInterface
{
    /**
     * @ORM\ManyToOne(targetEntity="SiteBundle\Entity\City")
     * @ORM\JoinColumn(name="city", referencedColumnName="Id")
     * @Assert\NotBlank(message="fields.required", groups={"SetAd", "SetAdAdmin"})
     */
    private ?City $city = null;

    /**
     * @ORM\Column(type="string", length=300, nullable=true)
     * @Assert\NotBlank(message="fields.required", groups={"SetAd", "SetAdAdmin"})
     */
    private ?string $address = null;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private ?string $telephone = null;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     * @Assert\NotBlank(message="fields.required", groups={"SetAd", "SetAdAdmin"})
     */
    private ?string $mobilePhone = null;


    /**
     * @ORM\Column(nullable=true, type="string", length=100)
     */
    private ?string $viber = null;

    /**
     * @ORM\Column(nullable=true, type="string", length=250)
     * @Assert\Email(message="fields.email", groups={"SetAd", "SetAdAdmin"}, mode="loose")
     */
    private ?string $contactEmail = null;

    /**
     * @ORM\OneToOne (targetEntity="SiteBundle\Entity\Ads", mappedBy="contact")
     */
    private ?Ads $ad;

    public function __construct()
    {
        parent::__construct();

        $this->setStatus(EntityStatusInterface::STATUS_PENDING);
    }

    public function setAddress(?string $address): void
    {
        $this->address = $address;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setTelephone(?string $telephone): void
    {
        $this->telephone = $telephone;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setMobilePhone(?string $mobilePhone): void
    {
        $this->mobilePhone = $mobilePhone;
    }

    public function getMobilePhone(): ?string
    {
        return $this->mobilePhone;
    }

    public function setCity(?City $city): void
    {
        $this->city = $city;
    }

    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setViber(?string $viber): void
    {
        $this->viber = $viber;
    }

    public function getViber(): ?string
    {
        return $this->viber;
    }

    public function setAd(?Ads $ad): void
    {
        $this->ad = $ad;
    }

    public function getAd(): ?Ads
    {
        return $this->ad;
    }

    public function setContactEmail(?string $contactEmail): void
    {
        $this->contactEmail = $contactEmail;
    }

    public function getContactEmail(): ?string
    {
        return $this->contactEmail;
    }
}
