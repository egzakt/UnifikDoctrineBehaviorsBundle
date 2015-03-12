<?php

namespace Unifik\DoctrineBehaviorsBundle\ORM\Metadatable;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;

/**
 * Metadatable Doctrine2 Listener
 *
 * Listens to loadClassMetadata and adds Metadata fields
 * to the entity.
 */
class MetadatableListener implements EventSubscriber
{
    const META_TITLE = 'metaTitle';

    const META_TITLE_OVERRIDE = 'metaTitleOverride';

    const META_DESCRIPTION = 'metaDescription';

    const META_KEYWORDS = 'metaKeywords';

    /**
     * @var array $entities
     */
    protected $entities;

    /**
     * Maps some fields when loading the Class Metadata.
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

            $this->mapMetaTitle($classMetadata);
            $this->mapMetaTitleOverride($classMetadata);
            $this->mapMetaDescription($classMetadata);
            $this->mapMetaKeywords($classMetadata);
        }
    }

    /**
     * Catch the new Metadatable entities, they will be updated on postFlush
     *
     * @param LifecycleEventArgs $eventArgs
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $entity = $eventArgs->getEntity();
        $classMetadata = $em->getClassMetadata(get_class($entity));

        // Check if it's Translatable is Timestampable
        if ($this->isEntitySupported($classMetadata->reflClass)) {

            // Update the updatedAt
            $this->entities[] = $entity;
        }
    }

    /**
     * Listens to the onFlush event.
     *
     * @param PostFlushEventArgs $eventArgs
     */
    public function postFlush(PostFlushEventArgs $eventArgs)
    {
        // If we got new Metadatable entities
        if (count($this->entities)) {

            $em = $eventArgs->getEntityManager();

            // Loop through Metadatable entities marked as insertion
            foreach($this->entities as $entity) {

                $classMetadata = $em->getClassMetadata(get_class($entity));

                // Set the Meta Title property only if not manually set in the form
                if (!$classMetadata->getReflectionProperty(self::META_TITLE)->getValue($entity)) {

                    // Get the toString (on the Translatable entity if applicable)
                    if ($classMetadata->reflClass->hasProperty('translatable')) {
                        $name = $entity->getTranslatable()->__toString();
                    } else {
                        $name = $entity->__toString();
                    }

                    $classMetadata->getReflectionProperty(self::META_TITLE)->setValue($entity, $name);
                    $em->persist($entity);
                }
            }

            $this->entities = [];

            $em->flush();
        }
    }

    /**
     * Add a "metaTitle" field
     *
     * @param ClassMetadata $classMetadata
     */
    protected function mapMetaTitle(ClassMetadata $classMetadata)
    {
        if (!$classMetadata->hasField(self::META_TITLE)) {
            $classMetadata->mapField([
                'fieldName' => self::META_TITLE,
                'type' => 'string',
                'length' => 255,
                'nullable' => true
            ]);
        }
    }

    /**
     * Add a "metaTitleOverride" field
     *
     * @param ClassMetadata $classMetadata
     */
    protected function mapMetaTitleOverride(ClassMetadata $classMetadata)
    {
        if (!$classMetadata->hasField(self::META_TITLE_OVERRIDE)) {
            $classMetadata->mapField([
                'fieldName' => self::META_TITLE_OVERRIDE,
                'type' => 'boolean',
                'nullable' => true,
                'default' => false
            ]);
        }
    }

    /**
     * Add a "metaDescription" field
     *
     * @param ClassMetadata $classMetadata
     */
    protected function mapMetaDescription(ClassMetadata $classMetadata)
    {
        if (!$classMetadata->hasField(self::META_DESCRIPTION)) {
            $classMetadata->mapField([
                'fieldName' => self::META_DESCRIPTION,
                'type' => 'string',
                'length' => 255,
                'nullable' => true
            ]);
        }
    }

    /**
     * Add a "metaKeywords" field
     *
     * @param ClassMetadata $classMetadata
     */
    protected function mapMetaKeywords(ClassMetadata $classMetadata)
    {
        if (!$classMetadata->hasField(self::META_KEYWORDS)) {
            $classMetadata->mapField([
                'fieldName' => self::META_KEYWORDS,
                'type' => 'string',
                'length' => 255,
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
        $traitNames = [];

        while ($reflClass) {
            $traitNames = array_merge($traitNames, $reflClass->getTraitNames());
            $reflClass = $reflClass->getParentClass();
        }

        return in_array('Unifik\DoctrineBehaviorsBundle\Model\Metadatable\Metadatable', $traitNames);
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
            Events::prePersist,
            Events::postFlush
        ];
    }
} 