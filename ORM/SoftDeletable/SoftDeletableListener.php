<?php

namespace Flexy\DoctrineBehaviorsBundle\ORM\SoftDeletable;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;

/**
 * SoftDeletable Doctrine2 listener.
 *
 * Listens to onFlush event and marks SoftDeletable entities
 * as deleted instead of really removing them.
 */
class SoftDeletableListener implements EventSubscriber
{
    const DELETED_AT_FIELD = 'deletedAt';

    /**
     * Maps the "deletedAt" field is necessary when loading the Class Metadata.
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

            // Add the slug field is necessary
            $this->mapDeletedAt($classMetadata);
        }
    }

    /**
     * Listens to onFlush event.
     *
     * @param OnFlushEventArgs $args The event arguments
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $em  = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            $classMetadata = $em->getClassMetadata(get_class($entity));

            if ($this->isEntitySupported($classMetadata->reflClass)) {
                $oldValue = $entity->getDeletedAt();

                $entity->delete();
                $em->persist($entity);

                $uow->propertyChanged($entity, 'deletedAt', $oldValue, $entity->getDeletedAt());
                $uow->scheduleExtraUpdate($entity, [
                    'deletedAt' => [$oldValue, $entity->getDeletedAt()]
                ]);
            }
        }
    }

    /**
     * Add a "deletedAt" field to a SoftDeletable entity, if necessary
     *
     * @param ClassMetadata $classMetadata
     */
    protected function mapDeletedAt(ClassMetadata $classMetadata)
    {
        if (!$classMetadata->hasField(self::DELETED_AT_FIELD)) {
            $classMetadata->mapField([
                'fieldName' => self::DELETED_AT_FIELD,
                'type' => 'datetime',
                'nullable' => true
            ]);
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
        $traitNames = $reflClass->getTraitNames();

        return in_array('Flexy\DoctrineBehaviorsBundle\Model\SoftDeletable\SoftDeletable', $traitNames);
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
            Events::onFlush
        ];
    }
}
