<?php

namespace Unifik\DoctrineBehaviorsBundle\ORM\Taggable;

use Doctrine\ORM\EntityManager;

/**
 * This service is used to manage the tags
 */
class TagManager
{
    /**
     * Constructor
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em = null)
    {
        $this->em = $em;
    }

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * Get Em
     *
     * @return EntityManager
     */
    public function getEm()
    {
        return $this->em;
    }

    /**
     * Set Em
     *
     * @param EntityManager $em
     * @return TagManager
     */
    public function setEm($em)
    {
        $this->em = $em;

        return $this;
    }
} 