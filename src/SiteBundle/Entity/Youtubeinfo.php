<?php

namespace SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Youtubeinfo
 *
 * @ORM\Table(name="youtubeinfo", indexes={@ORM\Index(name="YoutubeInfoAdsId", columns={"AdsId"})})
 * @ORM\Entity(repositoryClass="SiteBundle\Repository\YouTubeInfoRepository")
 */
class Youtubeinfo
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
     * @ORM\Column(name="YoutubeId", type="string", length=100, nullable=false)
     */
    private $youtubeid;

    /**
     * @var string
     *
     * @ORM\Column(name="Title", type="string", length=250, nullable=false)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="ChannelId", type="string", length=250, nullable=false)
     */
    private $channelid;

    /**
     * @var string
     *
     * @ORM\Column(name="ChanelTitle", type="string", length=250, nullable=false)
     */
    private $chaneltitle;

    /**
     * @var string
     *
     * @ORM\Column(name="Thumbnails", type="text", length=65535, nullable=false)
     */
    private $thumbnails;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="SysCreatedTime", type="datetime", nullable=true)
     */
    private $syscreatedtime;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="SiteBundle\Entity\User", inversedBy="Id")
     * @ORM\JoinColumn(name="SysCreatorId", referencedColumnName="Id")
     */
    private $syscreatorid;

    /**
     * @ORM\ManyToOne(targetEntity="Ads", inversedBy="youtubeIds")
     * @ORM\JoinColumn(name="AdsId", referencedColumnName="Id")
     */
    private Ads $adsid;



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
     * Set youtubeid
     *
     * @param string $youtubeid
     *
     * @return Youtubeinfo
     */
    public function setYoutubeid($youtubeid)
    {
        $this->youtubeid = $youtubeid;

        return $this;
    }

    /**
     * Get youtubeid
     *
     * @return string
     */
    public function getYoutubeid()
    {
        return $this->youtubeid;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Youtubeinfo
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
     * Set channelid
     *
     * @param string $channelid
     *
     * @return Youtubeinfo
     */
    public function setChannelid($channelid)
    {
        $this->channelid = $channelid;

        return $this;
    }

    /**
     * Get channelid
     *
     * @return string
     */
    public function getChannelid()
    {
        return $this->channelid;
    }

    /**
     * Set chaneltitle
     *
     * @param string $chaneltitle
     *
     * @return Youtubeinfo
     */
    public function setChaneltitle($chaneltitle)
    {
        $this->chaneltitle = $chaneltitle;

        return $this;
    }

    /**
     * Get chaneltitle
     *
     * @return string
     */
    public function getChaneltitle()
    {
        return $this->chaneltitle;
    }

    /**
     * Set thumbnails
     *
     * @param array $thumbnails
     *
     * @return Youtubeinfo
     */
    public function setThumbnails(array $thumbnails)
    {
        $this->thumbnails = json_encode($thumbnails);

        return $this;
    }

    /**
     * Get thumbnails
     *
     * @return string
     */
    public function getThumbnails()
    {
        return json_decode($this->thumbnails, true);
    }

    /**
     * Set syscreatedtime
     *
     * @param \DateTime $syscreatedtime
     *
     * @return Youtubeinfo
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
     * Set syscreatorid
     *
     * @param User $syscreatorid
     *
     * @return Youtubeinfo
     */
    public function setSyscreatorid(User $syscreatorid)
    {
        $this->syscreatorid = $syscreatorid;

        return $this;
    }

    /**
     * Get syscreatorid
     *
     * @return integer
     */
    public function getSyscreatorid()
    {
        return $this->syscreatorid;
    }

    /**
     * Set adsid
     *
     * @param \SiteBundle\Entity\Ads $adsid
     *
     * @return Youtubeinfo
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
