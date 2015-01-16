<?php

namespace Unifik\DoctrineBehaviorsBundle\ORM\Taggable;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;

/**
 * Taggable Doctrine2 Listener
 *
 * Listens to loadClassMetadata and adds n-n relation between a Taggable entity and the Tag entity.
 * This listener also manage adding/removing tags before flushing.
 */
class TaggableListener implements EventSubscriber
{
    /**
     * @var TagManager
     */
    protected $tagManager;

    /**
     * Constructor
     *
     * @param TagManager $tagManager
     */
    public function __construct(TagManager $tagManager)
    {
        $this->tagManager = $tagManager;
    }

    /**
     * Checks whether provided entity is supported.
     *
     * @param \ReflectionClass $reflClass
     *
     * @return bool
     */
    private function isEntitySupported(\ReflectionClass $reflClass)
    {
        $traitNames = [];

        while ($reflClass) {
            $traitNames = array_merge($traitNames, $reflClass->getTraitNames());
            $reflClass = $reflClass->getParentClass();
        }

        return in_array('Unifik\DoctrineBehaviorsBundle\Model\Taggable\Taggable', $traitNames);
    }

    /**
     * Load the Taggings of this entity
     *
     * @param LifecycleEventArgs $args
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $em            = $args->getEntityManager();
        $entity        = $args->getObject();
        $classMetadata = $em->getClassMetadata(get_class($entity));

        if (null === $classMetadata->reflClass) {
            return;
        }

        // Add relation if this entity is supported
        if ($this->isEntitySupported($classMetadata->reflClass)) {
            $this->tagManager->loadTagging($args->getObject());
        }
    }

    /**
     * Delete the Taggings of this entity
     *
     * @param LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $em            = $args->getEntityManager();
        $entity        = $args->getObject();
        $classMetadata = $em->getClassMetadata(get_class($entity));

        if (null === $classMetadata->reflClass) {
            return;
        }

        // Add relation if this entity is supported
        if ($this->isEntitySupported($classMetadata->reflClass)) {
            $this->tagManager->deleteTagging($args->getObject());
        }
    }

    /**
     * Returns list of events, that this listener is listening to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Events::postLoad,
            Events::preRemove
        ];
    }
} 