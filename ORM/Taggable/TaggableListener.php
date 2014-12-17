<?php

namespace Unifik\DoctrineBehaviorsBundle\ORM\Taggable;

use Doctrine\Common\EventSubscriber;
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
     * Create a n-n relation between a Taggable entity and the Tag entity
     *
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $classMetadata = $eventArgs->getClassMetadata();

        if (null === $classMetadata->reflClass) {
            return;
        }

        if ($this->isEntitySupported($classMetadata->reflClass)) {

            $this->mapTag($classMetadata);
        }
    }

    /**
     * Map an entity with the Tag entity
     *
     * @param ClassMetadata $classMetadata
     */
    protected function mapTag(ClassMetadata $classMetadata)
    {
        if (!$classMetadata->hasAssociation('tags')) {

        }
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
     * Returns list of events, that this listener is listening to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Events::loadClassMetadata
        ];
    }
} 