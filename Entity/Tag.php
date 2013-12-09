<?php

namespace Flexy\DoctrineBehaviorsBundle\Entity;

use Flexy\SystemBundle\Lib\BaseEntity;
use Flexy\DoctrineBehaviorsBundle\Model as FlexyORMBehaviors;

/**
 * Tag Entity
 */
class Tag extends BaseEntity
{
    use FlexyORMBehaviors\Timestampable\Timestampable;
    use FlexyORMBehaviors\Sluggable\Sluggable;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var boolean
     */
    private $active;

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
     * Get the backend route
     *
     * @param string $suffix
     *
     * @return string
     */
    public function getRouteBackend($suffix = 'edit')
    {
        return 'flexy_system_backend_tag_' . $suffix;
    }

    /**
     * Get params for the backend route
     *
     * @param array $params Additional parameters
     *
     * @return array
     */
    public function getRouteBackendParams($params = array())
    {
        $defaults = array(
            'tagId' => $this->id ? $this->id : 0,
        );

        $params = array_merge($defaults, $params);

        return $params;
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
     * Set class
     *
     * @param string $class
     * @return Tag
     */
    public function setClass($class)
    {
        $this->class = $class;
    
        return $this;
    }

    /**
     * Get class
     *
     * @return string 
     */
    public function getClass()
    {
        return $this->class;
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
     * Set active
     *
     * @param boolean $active
     * @return Tag
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Returns the list of sluggable fields
     *
     * @return array
     */
    public function getSluggableFields()
    {
        return array('name');
    }
}
