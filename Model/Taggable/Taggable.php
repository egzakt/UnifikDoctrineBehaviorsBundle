<?php

namespace Unifik\DoctrineBehaviorsBundle\Model\Taggable;

/**
 * Taggable trait
 *
 * This trait is used to activate the Taggable Listener and configure the Taggable behavior
 */
trait Taggable
{
    /**
     * Returns the type of the resource using this trait
     *
     * This method should return a string like 'blogpost'
     *
     * @return string
     */
    abstract public function getResourceType();

    /**
     * Define if the entity using this trait uses global tagging or resource type tagging.
     *
     * By default, any entity may use any tag. If you want to use only the tags related to a resource type,
     * return false;
     *
     * @return bool
     */
    public function isUsingGlobalTags()
    {
        return true;
    }
}
