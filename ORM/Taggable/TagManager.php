<?php

namespace Unifik\DoctrineBehaviorsBundle\ORM\Taggable;

use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * This service is used to manage the tags
 */
class TagManager
{
    /**
     * @var RegistryInterface
     */
    protected $registry;

    /**
     * Constructor
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Get Em
     *
     * @return EntityManager
     */
    public function getEm()
    {
        return $this->registry->getEntityManager();
    }
} 