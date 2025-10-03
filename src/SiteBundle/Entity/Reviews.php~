<?php

namespace SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Reviews
 *
 * @ORM\Table(name="reviews", indexes={@ORM\Index(name="ReviewsAdsId", columns={"AdsId"})})
 * @ORM\Entity(repositoryClass="SiteBundle\Repository\ReviewsRepository")
 */
class Reviews
{
    /**
     * @var integer
     *
     * @ORM\Column(name="Id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="Title", type="string", length=300, nullable=true)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="Description", type="text", length=65535, nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="NickName", type="string", length=150, nullable=true)
     */
    private $nickname;

    /**
     * @var integer
     *
     * @ORM\Column(name="UserId", type="integer", nullable=true)
     */
    private $userid;

    /**
     * @var boolean
     *
     * @ORM\Column(name="IsActive", type="boolean", nullable=true)
     */
    private $isactive = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="SysCreatedTime", type="datetime", nullable=true)
     */
    private $syscreatedtime;

    /**
     * @var \Ads
     *
     * @ORM\ManyToOne(targetEntity="Ads")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="AdsId", referencedColumnName="Id")
     * })
     */
    private $adsid;



    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Reviews
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Reviews
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
     * Set nickname
     *
     * @param string $nickname
     *
     * @return Reviews
     */
    public function setNickname($nickname)
    {
        $this->nickname = $nickname;

        return $this;
    }

    /**
     * Get nickname
     *
     * @return string
     */
    public function getNickname()
    {
        return $this->nickname;
    }

    /**
     * Set userid
     *
     * @param integer $userid
     *
     * @return Reviews
     */
    public function setUserid($userid)
    {
        $this->userid = $userid;

        return $this;
    }

    /**
     * Get userid
     *
     * @return integer
     */
    public function getUserid()
    {
        return $this->userid;
    }

    /**
     * Set isactive
     *
     * @param boolean $isactive
     *
     * @return Reviews
     */
    public function setIsactive($isactive)
    {
        $this->isactive = $isactive;

        return $this;
    }

    /**
     * Get isactive
     *
     * @return boolean
     */
    public function getIsactive()
    {
        return $this->isactive;
    }

    /**
     * Set syscreatedtime
     *
     * @param \DateTime $syscreatedtime
     *
     * @return Reviews
     */
    public function setSyscreatedtime($syscreatedtime)
    {
        $this->syscreatedtime = $syscreatedtime;

        return $this;
    }

    /**
     * Get syscreatedtime
     *
     * @return \DateTime
     */
    public function getSyscreatedtime()
    {
        return $this->syscreatedtime;
    }

    /**
     * Set adsid
     *
     * @param \SiteBundle\Entity\Ads $adsid
     *
     * @return Reviews
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
