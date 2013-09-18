<?php

namespace Egzakt\DoctrineBehaviorsBundle\Model\Sluggable;

/**
 * Sluggable trait
 *
 * This trait is used to activate the Sluggable Listener and configure the Sluggable behavior
 */
trait Sluggable
{
    /**
     * @var string $slug
     */
    protected $slug;

    /**
     * Returns the entity's slug.
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set Slug
     *
     * @param string $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * Returns whether the slug is unique or not.
     *
     * @return bool
     */
    public function getIsSlugUnique()
    {
        return true;
    }

    /**
     * Returns the slug delemiter
     *
     * @return string
     */
    public function getSlugDelimiter()
    {
        return '-';
    }

    /**
     * Returns the list of sluggable fields
     *
     * @return array
     */
    abstract public function getSluggableFields();

    /**
     * Determines whether the slug gets regenerated on sluggable fields update or not
     *
     * @return bool
     */
    public function getRegenerateOnUpdate()
    {
        return true;
    }
}
