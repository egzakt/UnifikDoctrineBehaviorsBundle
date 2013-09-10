<?php

namespace Egzakt\DoctrineBehaviorsBundle\Model\Sluggable;

/**
 * Sluggable trait.
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
     * Get Is Slug Unique
     *
     * Returns whether or not the slug is unique.
     *
     * @return bool
     */
    protected function getIsSlugUnique()
    {
        return true;
    }

}
