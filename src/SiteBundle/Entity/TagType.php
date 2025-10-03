<?php

namespace SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * TagType.
 * @ORM\Entity(repositoryClass="SiteBundle\Repository\TagTypeRepository")
 * @ORM\Table(name="tag_type")
 */
class TagType
{

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="name", type="string")
     */
    private $name;

    /**
     * @var string
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="label", type="string")
     */
    private $label;

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return TagType
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set label.
     *
     * @param string $label
     *
     * @return TagType
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get label.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
