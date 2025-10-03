<?php

namespace SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Stars
 *
 * @ORM\Table(name="stars", indexes={@ORM\Index(name="StarsReviewId", columns={"ReviewId"})})
 * @ORM\Entity
 */
class Stars
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
     * @var boolean
     *
     * @ORM\Column(name="Profesional", type="boolean", nullable=false)
     */
    private $profesional;

    /**
     * @var boolean
     *
     * @ORM\Column(name="Talent", type="boolean", nullable=false)
     */
    private $talent;

    /**
     * @var boolean
     *
     * @ORM\Column(name="Accomodation", type="boolean", nullable=false)
     */
    private $accomodation;

    /**
     * @var boolean
     *
     * @ORM\Column(name="Recommend", type="boolean", nullable=false)
     */
    private $recommend;

    /**
     * @var \Reviews
     *
     * @ORM\ManyToOne(targetEntity="Reviews")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ReviewId", referencedColumnName="Id")
     * })
     */
    private $reviewid;



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
     * Set profesional
     *
     * @param boolean $profesional
     *
     * @return Stars
     */
    public function setProfesional($profesional)
    {
        $this->profesional = $profesional;

        return $this;
    }

    /**
     * Get profesional
     *
     * @return boolean
     */
    public function getProfesional()
    {
        return $this->profesional;
    }

    /**
     * Set talent
     *
     * @param boolean $talent
     *
     * @return Stars
     */
    public function setTalent($talent)
    {
        $this->talent = $talent;

        return $this;
    }

    /**
     * Get talent
     *
     * @return boolean
     */
    public function getTalent()
    {
        return $this->talent;
    }

    /**
     * Set accomodation
     *
     * @param boolean $accomodation
     *
     * @return Stars
     */
    public function setAccomodation($accomodation)
    {
        $this->accomodation = $accomodation;

        return $this;
    }

    /**
     * Get accomodation
     *
     * @return boolean
     */
    public function getAccomodation()
    {
        return $this->accomodation;
    }

    /**
     * Set recommend
     *
     * @param boolean $recommend
     *
     * @return Stars
     */
    public function setRecommend($recommend)
    {
        $this->recommend = $recommend;

        return $this;
    }

    /**
     * Get recommend
     *
     * @return boolean
     */
    public function getRecommend()
    {
        return $this->recommend;
    }

    /**
     * Set reviewid
     *
     * @param \SiteBundle\Entity\Reviews $reviewid
     *
     * @return Stars
     */
    public function setReviewid(\SiteBundle\Entity\Reviews $reviewid = null)
    {
        $this->reviewid = $reviewid;

        return $this;
    }

    /**
     * Get reviewid
     *
     * @return \SiteBundle\Entity\Reviews
     */
    public function getReviewid()
    {
        return $this->reviewid;
    }
}
