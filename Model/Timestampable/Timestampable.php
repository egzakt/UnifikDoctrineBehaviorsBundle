<?php

namespace Egzakt\DoctrineBehaviorsBundle\Model\Timestampable;

/**
 * Timestampable trait.
 *
 * Should be used inside entity that needs to be timestamped.
 */
trait Timestampable
{
    /**
     * @var \DateTime $createdAt
     */
    protected $createdAt;

    /**
     * @var \DateTime $updatedAt
     */
    protected $updatedAt;

    /**
     * Returns createdAt value.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set Created At
     *
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Returns updatedAt value.
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set Updated At
     *
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Updates createdAt and updatedAt timestamps.
     *
     * Called on Doctrine prePersist and preUpdate events
     */
    public function updateTimestamps()
    {
        if (null === $this->createdAt) {
            $this->createdAt = new \DateTime('now');
        }

        $this->updatedAt = new \DateTime('now');
    }

    /**
     * Update the Translatable updatedAt on preUpdate
     */
    public function updateTranslatableTimestamps()
    {
        $this->getTranslatable()->setUpdatedAt(new \DateTime('now'));
    }
}
