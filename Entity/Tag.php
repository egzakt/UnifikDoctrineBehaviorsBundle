<?php

namespace Unifik\DoctrineBehaviorsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Unifik\DoctrineBehaviorsBundle\Model as UnifikORMBehaviors;

/**
 * Tag
 */
class Tag
{
    use UnifikORMBehaviors\Sluggable\Sluggable;
    use UnifikORMBehaviors\Timestampable\Timestampable;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $resourceType;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $taggings;

    /**
     * Constructor
     */
    public function __construct($name = null)
    {
        $this->setName($name);
        $this->taggings = new ArrayCollection();
    }

    public function __toString()
    {
        if (false == $this->id) {
            return 'New Tag';
        }

        if ($name = $this->getName()) {
            return $name;
        }

        // No translation found in the current locale
        return '';
    }

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
     * @return Tag
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
     * Set resourceType
     *
     * @param string $resourceType
     * @return Tag
     */
    public function setResourceType($resourceType)
    {
        $this->resourceType = $resourceType;

        return $this;
    }

    /**
     * Get resourceType
     *
     * @return string 
     */
    public function getResourceType()
    {
        return $this->resourceType;
    }

    /**
     * Set locale
     *
     * @param string $locale
     * @return Tag
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Get locale
     *
     * @return string 
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Add taggings
     *
     * @param \Unifik\DoctrineBehaviorsBundle\Entity\Tagging $taggings
     * @return Tag
     */
    public function addTagging(\Unifik\DoctrineBehaviorsBundle\Entity\Tagging $taggings)
    {
        $this->taggings[] = $taggings;

        return $this;
    }

    /**
     * Remove taggings
     *
     * @param \Unifik\DoctrineBehaviorsBundle\Entity\Tagging $taggings
     */
    public function removeTagging(\Unifik\DoctrineBehaviorsBundle\Entity\Tagging $taggings)
    {
        $this->taggings->removeElement($taggings);
    }

    /**
     * Get taggings
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTaggings()
    {
        return $this->taggings;
    }

    /**
     * Returns the list of sluggable fields
     *
     * @return array
     */
    public function getSluggableFields()
    {
        return ['name'];
    }
}
