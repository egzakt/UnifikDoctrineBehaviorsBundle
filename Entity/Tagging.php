<?php

namespace Unifik\DoctrineBehaviorsBundle\Entity;

use Unifik\DoctrineBehaviorsBundle\Model as UnifikORMBehaviors;

/**
 * Tag
 */
class Tagging
{
    use UnifikORMBehaviors\Timestampable\Timestampable;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $resourceType;

    /**
     * @var integer
     */
    private $resourceId;

    /**
     * @var \Unifik\DoctrineBehaviorsBundle\Entity\Tag
     */
    private $tag;

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
     * Set resourceType
     *
     * @param string $resourceType
     * @return Tagging
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
     * Set resourceId
     *
     * @param integer $resourceId
     * @return Tagging
     */
    public function setResourceId($resourceId)
    {
        $this->resourceId = $resourceId;

        return $this;
    }

    /**
     * Get resourceId
     *
     * @return integer 
     */
    public function getResourceId()
    {
        return $this->resourceId;
    }

    /**
     * Set tag
     *
     * @param \Unifik\DoctrineBehaviorsBundle\Entity\Tag $tag
     * @return Tagging
     */
    public function setTag(\Unifik\DoctrineBehaviorsBundle\Entity\Tag $tag = null)
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * Get tag
     *
     * @return \Unifik\DoctrineBehaviorsBundle\Entity\Tag 
     */
    public function getTag()
    {
        return $this->tag;
    }
}
