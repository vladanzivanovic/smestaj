<?php

namespace SiteBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * AdsAdditionalInfo
 *
 * @ORM\Table(name="adsadditionalinfo")
 * @ORM\Entity(repositoryClass="SiteBundle\Repository\AdsAdditionalInfoRepository")
 */
class AdsAdditionalInfo
{
    /**
     * @var int
     *
     * @ORM\Column(name="Id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="Description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var int
     *
     * @ORM\Column(name="CapacityMin", type="integer", nullable=true)
     */
    private $capacitymin;

    /**
     * @var int
     *
     * @ORM\Column(name="CapacityMax", type="integer", nullable=true)
     */
    private $capacitymax;

    /**
     * @var Ads
     *
     * @ORM\ManyToOne(targetEntity="SiteBundle\Entity\Ads", inversedBy="adsadditionalinfo")
     * @ORM\JoinColumn(name="AdsId", nullable=true,referencedColumnName="Id")
     */
    private $adsid;

    /**
     * @var Media
     *
     * @ORM\OneToMany(targetEntity="SiteBundle\Entity\Media", mappedBy="adsinfoid", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $mediainfo;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->mediainfo = new ArrayCollection();
    }


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return AdsAdditionalInfo
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set capacitymin
     *
     * @param integer $capacitymin
     *
     * @return AdsAdditionalInfo
     */
    public function setCapacitymin($capacitymin)
    {
        $this->capacitymin = $capacitymin;

        return $this;
    }

    /**
     * Get capacitymin
     *
     * @return int
     */
    public function getCapacitymin()
    {
        return $this->capacitymin;
    }

    /**
     * Set capacitymax
     *
     * @param integer $capacitymax
     *
     * @return AdsAdditionalInfo
     */
    public function setCapacitymax($capacitymax)
    {
        $this->capacitymax = $capacitymax;

        return $this;
    }

    /**
     * Get capacitymax
     *
     * @return int
     */
    public function getCapacitymax()
    {
        return $this->capacitymax;
    }

    /**
     * Add medium
     *
     * @param \SiteBundle\Entity\Media $medium
     *
     * @return AdsAdditionalInfo
     */
    public function addMedia(\SiteBundle\Entity\Media $medium)
    {
        $this->mediainfo[] = $medium;

        return $this;
    }

    /**
     * Remove medium
     *
     * @param \SiteBundle\Entity\Media $medium
     */
    public function removeMedia(\SiteBundle\Entity\Media $medium)
    {
        $this->mediainfo->removeElement($medium);
    }

    /**
     * Get mediainfo
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMedia()
    {
        return $this->mediainfo;
    }

    /**
     * Set adsid
     *
     * @param \SiteBundle\Entity\Ads $adsid
     *
     * @return AdsAdditionalInfo
     */
    public function setAdsid(\SiteBundle\Entity\Ads $adsid = null)
    {
        $this->adsid = $adsid;

        return $this;
    }

    /**
     * Get adsid
     *
     * @return \SiteBundle\Entity\Ads
     */
    public function getAdsid()
    {
        return $this->adsid;
    }
}
