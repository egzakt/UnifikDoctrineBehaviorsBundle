<?php

namespace Unifik\DoctrineBehaviorsBundle\Model\Taggable;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Taggable trait
 *
 * This trait is used to activate the Taggable Listener and configure the Taggable behavior
 */
trait Taggable
{
    /**
     * @var ArrayCollection
     */
    protected $tags;

    /**
     * Get Tags
     *
     * @return ArrayCollection
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Set Tags
     *
     * @param ArrayCollection $tags
     * @return Taggable
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    /**
     * Returns the type of the resource using this trait
     *
     * This method should return a string like 'blogpost'
     *
     * @return string
     */
    abstract public function getResourceType();
}
