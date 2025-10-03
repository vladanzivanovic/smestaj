<?php

namespace SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * City
 *§
 * @ORM\Table(name="city")
 * @ORM\Entity(repositoryClass="SiteBundle\Repository\CityRepository")
 */
class City
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
     * @ORM\Column(name="Name", type="string", length=200, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="Alias", type="string", length=200, nullable=false)
     */
    private $alias;

    /**
     * @var string
     *
     * @ORM\Column(name="ZipCode", type="string", length=50, nullable=true)
     */
    private $zipcode;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", length=1, nullable=true)
     */
    private $showInHome = false;

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
     * @return City
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
     * @return City
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
     * Set zipcode
     *
     * @param string $zipcode
     *
     * @return City
     */
    public function setZipcode($zipcode)
    {
        $this->zipcode = $zipcode;

        return $this;
    }

    /**
     * Get zipcode
     *
     * @return string
     */
    public function getZipcode()
    {
        return $this->zipcode;
    }

    /**
     * @return bool
     */
    public function getShowInHome(): bool
    {
        return $this->showInHome;
    }

    /**
     * @param bool $showInHome
     *
     * @return City
     */
    public function setShowInHome(bool $showInHome): City
    {
        $this->showInHome = $showInHome;

        return $this;
    }
}
