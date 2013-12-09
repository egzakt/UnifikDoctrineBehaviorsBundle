<?php

namespace Flexy\DoctrineBehaviorsBundle\Model\Taggable;

use Doctrine\Common\Collections\ArrayCollection;

use Flexy\DoctrineBehaviorsBundle\Entity\Tag;

/**
 * Taggable trait
 *
 * Should be in the entity that needs to be translated.
 */
trait Taggable
{
    /**
     * @var ArrayCollection $tags
     *
     * Will be mapped to the Taggable entity
     * by TaggableListener
     */
    protected $tags;

    /**
     * Add tags
     *
     * @param  Tag $tags
     * @return mixed
     */
    public function addTag(Tag $tags)
    {
        $this->tags[] = $tags;

        return $this;
    }

    /**
     * Set Tags
     *
     * @param ArrayCollection $tags
     */
    public function setTags(ArrayCollection $tags)
    {
        $this->tags = $tags;
    }

    /**
     * Remove tags
     *
     * @param Tag $tags
     */
    public function removeTag(Tag $tags)
    {
        $this->tags->removeElement($tags);
    }

    /**
     * Get tags
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Get the list of internal tags
     *
     * @return array
     */
    public function getInternalTags()
    {
        $internalTags = array();

        foreach($this->tags as $tag) {
            if (substr($tag->getName(), 0, 1) == '_') {
                $internalTags[] = $tag;
            }
        }

        return $internalTags;
    }
}
