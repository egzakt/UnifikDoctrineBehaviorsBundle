<?php

namespace Unifik\DoctrineBehaviorsBundle\Model\Taggable;

use Doctrine\Common\Collections\ArrayCollection;
use Unifik\DoctrineBehaviorsBundle\Entity\Tag;

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
     * @var \DateTime
     */
    protected $tagsUpdatedAt;

    /**
     * Get Tags
     *
     * @return ArrayCollection
     */
    public function getTags()
    {
        if ($this->tags === null) {
            $this->tags = new ArrayCollection();
        }

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
        $this->setTagsUpdatedAt(new \DateTime());
    }

    /**
     * Add Tag
     *
     * @param Tag $tag
     * @return Taggable
     */
    public function addTag($tag)
    {
        $this->tags->add($tag);
        $this->setTagsUpdatedAt(new \DateTime());

        return $this;
    }

    /**
     * Get Tags Updated At
     *
     * @return \DateTime
     */
    public function getTagsUpdatedAt()
    {
        return $this->tagsUpdatedAt;
    }

    /**
     * Set Tags Updated At
     *
     * @param \DateTime $tagsUpdatedAt
     *
     * @return Taggable
     */
    public function setTagsUpdatedAt($tagsUpdatedAt)
    {
        $this->tagsUpdatedAt = $tagsUpdatedAt;

        return $this;
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
