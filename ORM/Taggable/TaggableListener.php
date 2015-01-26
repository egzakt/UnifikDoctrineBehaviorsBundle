<?php

namespace Unifik\DoctrineBehaviorsBundle\ORM\Taggable;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * Taggable Doctrine2 Listener
 *
 * Listens to loadClassMetadata and adds n-n relation between a Taggable entity and the Tag entity.
 * This listener also manage adding/removing tags before flushing.
 */
class TaggableListener implements EventSubscriber
{
    const TAGS_TIMESTAMP = 'tagsUpdatedAt';

    /**
     * @var TagManager
     */
    protected $tagManager;

    /**
     * @var ArrayCollection
     */
    protected $entitiesToSave;

    /**
     * @var bool
     */
    protected $needToFlush;

    /**
     * Constructor
     *
     * @param TagManager $tagManager
     */
    public function __construct(TagManager $tagManager)
    {
        $this->tagManager = $tagManager;
        $this->entitiesToSave = new ArrayCollection();
        $this->needToFlush = false;
    }

    /**
     * Mark an entity to be saved on postFlush
     *
     * @param $entity
     */
    public function addEntityToSave($entity)
    {
        if (!$this->entitiesToSave->contains($entity)) {
            $this->entitiesToSave->add($entity);
        }
    }

    /**
     * Set Need To Flush
     *
     * @param boolean $needToFlush
     * @return TaggableListener
     */
    public function setNeedToFlush($needToFlush)
    {
        $this->needToFlush = $needToFlush;

        return $this;
    }

    /**
     * Get the Reflection Class of an entity
     *
     * @param $entity
     * @return null|\ReflectionClass
     */
    public function getReflClass($entity)
    {
        if (is_object($entity)) {
            try {
                $classMetadata = $this->tagManager->getEm()->getClassMetadata(get_class($entity));

                return $classMetadata->reflClass;
            } catch (\Exception $e) {}
        }

        return null;
    }

    /**
     * Load Class Metadata
     *
     * Add a Tags Timestamp field that will be used to trigger the prePersist and preUpdate listeners
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

            // Add the Tags Timestamp field
            $this->mapTagsTimestamp($classMetadata);
        }
    }

    /**
     * Add a Tags Timestamp field to a Taggable entity
     *
     * @param ClassMetadata $classMetadata
     */
    protected function mapTagsTimestamp(ClassMetadata $classMetadata)
    {
        if (!$classMetadata->hasField(self::TAGS_TIMESTAMP)) {
            $classMetadata->mapField([
                'fieldName' => self::TAGS_TIMESTAMP,
                'type' => 'datetime',
                'nullable' => true
            ]);
        }
    }

    /**
     * Gets called before inserts
     *
     * @param LifecycleEventArgs $eventArgs
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getObject();

        // If it's a Taggable entity, mark the entity to be saved
        if ($this->isEntitySupported($this->getReflClass($entity))) {
            $this->addEntityToSave($entity);
        }
    }

    /**
     * Gets called before updates
     *
     * @param PreUpdateEventArgs $eventArgs
     */
    public function preUpdate(PreUpdateEventArgs $eventArgs)
    {
        $entity = $eventArgs->getObject();

        // If it's a Taggable entity, mark the entity to be saved
        if ($this->isEntitySupported($this->getReflClass($entity))) {
            $this->addEntityToSave($entity);
        }
    }

    /**
     * Load the Taggings of this entity
     *
     * @param LifecycleEventArgs $args
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        // If it's a Taggable entity, set a closure to lazy load the tags
        if ($this->isEntitySupported($this->getReflClass($entity))) {
            $tagManager = $this->tagManager;

            $tagReference = function() use ($tagManager, $entity) {
                $tagManager->loadTagging($entity);
            };

            $entity->setTagReference($tagReference);
        }
    }

    /**
     * Save the Taggings on PostFlush
     *
     * @param PostFlushEventArgs $eventArgs
     */
    public function postFlush(PostFlushEventArgs $eventArgs)
    {
        if ($this->needToFlush) {
            $this->needToFlush = false;

            foreach ($this->entitiesToSave as $entity) {
                $this->tagManager->saveTagging($entity);
            }
        }
    }

    /**
     * Delete the Taggings of this entity
     *
     * @param LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        // If it's a Taggable entity, delete the tags
        if ($this->isEntitySupported($this->getReflClass($entity))) {
            $this->tagManager->deleteTagging($args->getObject());
        }
    }

    /**
     * Checks whether provided entity is supported.
     *
     * @param \ReflectionClass|null $reflClass
     *
     * @return bool
     */
    public function isEntitySupported($reflClass)
    {
        if ($reflClass) {
            $traitNames = [];

            while ($reflClass) {
                $traitNames = array_merge($traitNames, $reflClass->getTraitNames());
                $reflClass = $reflClass->getParentClass();
            }

            return in_array('Unifik\DoctrineBehaviorsBundle\Model\Taggable\Taggable', $traitNames);
        }

        return false;
    }

    /**
     * Returns list of events, that this listener is listening to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Events::loadClassMetadata,
            Events::postLoad,
            Events::prePersist,
            Events::preUpdate,
            Events::preRemove,
            Events::postFlush
        ];
    }
} 