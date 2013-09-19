<?php

namespace Egzakt\DoctrineBehaviorsBundle\ORM\Timestampable;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * Timestampable listener.
 *
 * Adds mapping to the timestampable entities.
 */
class TimestampableListener implements EventSubscriber
{
    const UPDATED_AT_FIELD = 'updatedAt';

    const CREATED_AT_FIELD = 'createdAt';

    /**
     * Adds callback and map new updatedAt and createdAt fields to the entity.
     *
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $classMetadata = $eventArgs->getClassMetadata();

        if (null === $classMetadata->reflClass) {
            return;
        }

        if ($this->isEntitySupported($classMetadata)) {

            // Map new fields if necessary
            $this->mapCreatedAt($classMetadata);
            $this->mapUpdatedAt($classMetadata);

            // Add callbacks
            $classMetadata->addLifecycleCallback('updateTimestamps', Events::prePersist);
            $classMetadata->addLifecycleCallback('updateTimestamps', Events::preUpdate);
        }
    }

    /**
     * When a new Translation entity is persisted, check if it's Translatable is Timestampable.
     * If so, update the updatedAt property.
     *
     * @param LifecycleEventArgs $eventArgs
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $entity = $eventArgs->getEntity();
        $classMetadata = $em->getClassMetadata(get_class($entity));

        // Check if it's Translatable is Timestampable
        if ($this->isTranslatableSupported($classMetadata, $em)) {

            // Update the updatedAt
            $entity->getTranslatable()->setUpdatedAt(new \DateTime('now'));;
        }
    }

    /**
     * Check if a Translation entity is persisted and is Timestampable.
     * If so, we update the Translatable entity updatedAt field with the current timestamp
     *
     * @param OnFlushEventArgs $eventArgs
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $unitOfWork = $em->getUnitOfWork();

        // Loop through the updated entities
        foreach ($unitOfWork->getScheduledEntityUpdates() AS $entity) {

            $classMetadata = $em->getClassMetadata(get_class($entity));

            // If the entity is a Translation and it's Translatable entity is Timestampable
            if ($this->isTranslatableSupported($classMetadata, $em)) {

                // Create a new changeSets : array('updatedAt' => array(oldValue, newValue))
                $changeSets = array('updatedAt' => array($entity->getTranslatable()->getUpdatedAt(), new \DateTime('now')));

                // Apply the changeSets to this entity as Extra Update because
                // recomputing the changes on the entity will override the changes
                // made on this entity BEFORE the onFlush method (form values)
                $unitOfWork->scheduleExtraUpdate($entity->getTranslatable(), $changeSets);
            }
        }
    }

    /**
     * Add a "updatedAt" field to a timestampable entity
     *
     * @param ClassMetadata $classMetadata
     */
    protected function mapUpdatedAt(ClassMetadata $classMetadata)
    {
        if (!$classMetadata->hasField(self::UPDATED_AT_FIELD)) {
            $classMetadata->mapField([
                'fieldName' => self::UPDATED_AT_FIELD,
                'type' => 'datetime',
                'nullable' => true
            ]);
        }
    }

    /**
     * Add a "createddAt" field to a timestampable entity
     *
     * @param ClassMetadata $classMetadata
     */
    protected function mapCreatedAt(ClassMetadata $classMetadata)
    {
        if (!$classMetadata->hasField(self::CREATED_AT_FIELD)) {
            $classMetadata->mapField([
                'fieldName' => self::CREATED_AT_FIELD,
                'type' => 'datetime',
                'nullable' => true
            ]);
        }
    }

    /**
     * Get Subscribed Events
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Events::loadClassMetadata,
            Events::prePersist,
            Events::onFlush
        ];
    }

    /**
     * Checks whether provided entity is supported.
     *
     * @param ClassMetadata $classMetadata The metadata
     *
     * @return Boolean
     */
    private function isEntitySupported(ClassMetadata $classMetadata)
    {
        $traitNames = $classMetadata->reflClass->getTraitNames();

        return in_array('Egzakt\DoctrineBehaviorsBundle\Model\Timestampable\Timestampable', $traitNames);
    }

    /**
     * Checks whether provided entity is a Translation entity and it's Translatable entity is Timestampable.
     *
     * @param ClassMetadata $classMetadata
     * @param EntityManager $em
     *
     * @return bool
     */
    protected function isTranslatableSupported(ClassMetadata $classMetadata, EntityManager $em)
    {
        $traitNames = $classMetadata->reflClass->getTraitNames();

        $isTranslation = in_array('Egzakt\DoctrineBehaviorsBundle\Model\Translatable\Translation', $traitNames)
                && $classMetadata->reflClass->hasProperty('translatable');

        // If it's a Translation entity
        if ($isTranslation) {

            // Check if it's Translatable entity is Timestampable
            $translatableClass = str_replace('Translation', '', $classMetadata->reflClass->getName());
            $translatableClassMetadata = $em->getClassMetadata($translatableClass);

            return $this->isEntitySupported($translatableClassMetadata);
        }

        return false;
    }
}
