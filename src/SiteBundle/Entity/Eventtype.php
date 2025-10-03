<?php

namespace SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Eventtype
 *
 * @ORM\Table(name="eventtype")
 * @ORM\Entity(repositoryClass="SiteBundle\Repository\EventTypeRepository")
 */
class Eventtype
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
     * @ORM\Column(name="Name", type="string", length=250, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="Alias", type="string", length=250, nullable=false)
     */
    private $alias;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="SysCreatedTime", type="datetime", nullable=false)
     */
    private $syscreatedtime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="SysModifiedTime", type="datetime", nullable=false)
     */
    private $sysmodifiedtime;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="SiteBundle\Entity\User", inversedBy="Id")
     * @ORM\JoinColumn(name="SysCreatorId", referencedColumnName="Id")
     */
    private $syscreatorid;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="SiteBundle\Entity\User", inversedBy="Id")
     * @ORM\JoinColumn(name="SysModifierId", referencedColumnName="Id")
     */
    private $sysmodifierid;

    /**
     * @ORM\OneToMany(targetEntity="SiteBundle\Entity\Reservation", mappedBy="eventtypeid")
     */
    private $reservations;


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
     * Set name
     *
     * @param string $name
     *
     * @return Eventtype
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set alias
     *
     * @param string $alias
     *
     * @return Eventtype
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * Get alias
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Set syscreatedtime
     *
     * @param \DateTime $syscreatedtime
     *
     * @return Eventtype
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
     * Set sysmodifiedtime
     *
     * @param \DateTime $sysmodifiedtime
     *
     * @return Eventtype
     */
    public function setSysmodifiedtime($sysmodifiedtime)
    {
        $this->sysmodifiedtime = $sysmodifiedtime;

        return $this;
    }

    /**
     * Get sysmodifiedtime
     *
     * @return \DateTime
     */
    public function getSysmodifiedtime()
    {
        return $this->sysmodifiedtime;
    }

    /**
     * Set syscreatorid
     *
     * @param integer $syscreatorid
     *
     * @return Eventtype
     */
    public function setSyscreatorid($syscreatorid)
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
     * Set sysmodifierid
     *
     * @param integer $sysmodifierid
     *
     * @return Eventtype
     */
    public function setSysmodifierid($sysmodifierid)
    {
        $this->sysmodifierid = $sysmodifierid;

        return $this;
    }

    /**
     * Get sysmodifierid
     *
     * @return integer
     */
    public function getSysmodifierid()
    {
        return $this->sysmodifierid;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->reservations = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add reservation
     *
     * @param \SiteBundle\Entity\Reservation $reservation
     *
     * @return Eventtype
     */
    public function addReservation(\SiteBundle\Entity\Reservation $reservation)
    {
        $this->reservations[] = $reservation;

        return $this;
    }

    /**
     * Remove reservation
     *
     * @param \SiteBundle\Entity\Reservation $reservation
     */
    public function removeReservation(\SiteBundle\Entity\Reservation $reservation)
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
}
