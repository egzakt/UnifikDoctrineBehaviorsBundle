<?php

namespace Flexy\DoctrineBehaviorsBundle\Model\Blameable;

/**
 * Blameable trait.
 *
 * Should be used inside entity where you need to track which user created or updated it
 */
trait Blameable
{
    /**
     * Will be mapped to either string or user entity
     * by BlameableListener
     */
    protected $createdBy;

    /**
     * Will be mapped to either string or user entity
     * by BlameableListener
     */
    protected $updatedBy;

    /**
     * Will be mapped to either string or user entity
     * by BlameableListener
     */
    protected $deletedBy;

    /**
     * Set Created By
     *
     * @param mixed $user The user representation
     */
    public function setCreatedBy($user)
    {
        $this->createdBy = $user;
    }

    /**
     * Set Updated By
     *
     * @param mixed $user The user representation
     */
    public function setUpdatedBy($user)
    {
        $this->updatedBy = $user;
    }

    /**
     * Set Deleted By
     *
     * @param mixed $user The user representation
     */
    public function setDeletedBy($user)
    {
        $this->deletedBy = $user;
    }

    /**
     * Get Created By
     *
     * @return mixed The user who created entity
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Get Updated By
     *
     * @return mixed The user who last updated entity
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * Get Deleted By
     *
     * @return mixed The user who removed entity
     */
    public function getDeletedBy()
    {
        return $this->deletedBy;
    }

    /**
     * Return true to tell that this entity is Blameable
     *
     * @return bool
     */
    public function isBlameable()
    {
        return true;
    }
}
