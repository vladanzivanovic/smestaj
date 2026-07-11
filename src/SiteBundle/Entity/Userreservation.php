<?php

declare(strict_types=1);

namespace SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'userreservation')]
#[ORM\Index(name: 'IDX_78F0167835F944', columns: ['CityId'])]
#[ORM\Entity(repositoryClass: \SiteBundle\Repository\UserRepository::class)]
class Userreservation
{
    /**
     * @var integer
     */
    #[ORM\Column(name: 'Id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * @var string
     */
    #[ORM\Column(name: 'FirstName', type: 'string', length: 200, nullable: false)]
    private $firstname;

    /**
     * @var string
     */
    #[ORM\Column(name: 'LastName', type: 'string', length: 200, nullable: false)]
    private $lastname;

    /**
     * @var string
     */
    #[ORM\Column(name: 'Email', type: 'string', length: 250, nullable: true)]
    private $email;

    /**
     * @var string
     */
    #[ORM\Column(name: 'Telephone', type: 'string', length: 250, nullable: true)]
    private $telephone;

    /**
     * @var string
     */
    #[ORM\Column(name: 'MobilePhone', type: 'string', length: 200, nullable: true)]
    private $mobilephone;

    /**
     * @var string
     */
    #[ORM\Column(name: 'Address', type: 'string', length: 250, nullable: false)]
    private $address;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'client', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'UserId', nullable: true, referencedColumnName: 'Id')]
    private $userid;

    #[ORM\ManyToOne(targetEntity: City::class)]
    #[ORM\JoinColumn(name: 'CityId', referencedColumnName: 'Id')]
    private $cityid;

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
     * Set firstname
     *
     * @param string $firstname
     *
     * @return Userreservation
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get firstname
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     *
     * @return Userreservation
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get lastname
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Userreservation
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set telephone
     *
     * @param string $telephone
     *
     * @return Userreservation
     */
    public function setTelephone($telephone)
    {
        $this->telephone = $telephone;

        return $this;
    }

    /**
     * Get telephone
     *
     * @return string
     */
    public function getTelephone()
    {
        return $this->telephone;
    }

    /**
     * Set mobilephone
     *
     * @param string $mobilephone
     *
     * @return Userreservation
     */
    public function setMobilephone($mobilephone)
    {
        $this->mobilephone = $mobilephone;

        return $this;
    }

    /**
     * Get mobilephone
     *
     * @return string
     */
    public function getMobilephone()
    {
        return $this->mobilephone;
    }

    /**
     * Set address
     *
     * @param string $address
     *
     * @return Userreservation
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set userid
     *
     * @param User $userid
     *
     * @return Userreservation
     */
    public function setUserid($userid)
    {
        $this->userid = $userid;

        return $this;
    }

    /**
     * Get userid
     *
     * @return User
     */
    public function getUserid()
    {
        return $this->userid;
    }

    /**
     * Set cityid
     *
     * @param \SiteBundle\Entity\City $cityid
     *
     * @return Userreservation
     */
    public function setCityid(?City $cityid = null)
    {
        $this->cityid = $cityid;

        return $this;
    }

    /**
     * Get cityid
     *
     * @return \SiteBundle\Entity\City
     */
    public function getCityid()
    {
        return $this->cityid;
    }
}
