<?php

namespace SiteBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Adshastags
 *
 * @ORM\Table(name="ads_has_tags")
 * @ORM\Entity(repositoryClass="SiteBundle\Repository\AdshastagsRepository")
 */
class Adshastags
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Ads
     *
     * @ORM\ManyToOne(targetEntity="SiteBundle\Entity\Ads", inversedBy="hasTags")
     * @ORM\JoinColumn(name="ads", referencedColumnName="Id", nullable=true)
     */
    private $ads;

    /**
     * @var Tag
     *
     * @ORM\ManyToOne(targetEntity="SiteBundle\Entity\Tag", inversedBy="hasTag")
     * @ORM\JoinColumn(name="tag", referencedColumnName="", nullable=true)
     */
    private $tag;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="string", length=100)
     */
    private $value;


    public function __construct()
    {
        $this->ads = new ArrayCollection();
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
     * Set ads
     *
     * @param Ads $ads
     *
     * @return Adshastags
     */
    public function setAds(Ads $ads)
    {
        $this->ads = $ads;

        return $this;
    }

    /**
     * Get ads
     *
     * @return Ads
     */
    public function getAds()
    {
        return $this->ads;
    }

    /**
     * Set tag
     *
     * @param Tag $tag
     *
     * @return Adshastags
     */
    public function setTag(Tag $tag)
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * Get tag
     *
     * @return Tag
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * Set value
     *
     * @param string $value
     *
     * @return Adshastags
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}
