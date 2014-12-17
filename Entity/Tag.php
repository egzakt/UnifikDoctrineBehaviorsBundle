<?php

namespace Unifik\DoctrineBehaviorsBundle\Entity;

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
     * To String
     *
     * @return string
     */
    public function __toString()
    {
        if (false == $this->id) {
            return 'New Tag';
        }

        if ($name = $this->getName()) {
            return $name;
        }

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
     * Returns the list of sluggable fields
     *
     * @return array
     */
    public function getSluggableFields()
    {
        return ['name'];
    }
}
