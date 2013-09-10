<?php

namespace Egzakt\DoctrineBehaviorsBundle\ORM\Sluggable;

use Knp\DoctrineBehaviors\ORM\Sluggable\SluggableListener as BaseSluggableListener;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs,
    Doctrine\Common\EventSubscriber,
    Doctrine\ORM\Mapping\ClassMetadata;

/**
 * Sluggable listener.
 *
 * Adds mapping to sluggable entities and the slug field to the ClassMetadata
 */
class SluggableListener extends BaseSluggableListener
{

    /**
     * Load Class Metadata
     *
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $classMetadata = $eventArgs->getClassMetadata();

        if (null === $classMetadata->reflClass) {
            return;
        }

        parent::loadClassMetadata($eventArgs);

        if ($this->isEntitySupported($classMetadata)) {
            $this->mapSlug($classMetadata);
        }
    }

    /**
     * Map Slug
     *
     * Add a "slug" field to a sluggable entity
     *
     * @param ClassMetadata $classMetadata
     */
    protected function mapSlug(ClassMetadata $classMetadata)
    {
        if (!$classMetadata->hasField('slug')) {
            $classMetadata->mapField([
                'fieldName' => 'slug',
                'type' => 'string',
                'length' => 255,
                'nullable' => false
            ]);
        }
    }

    /**
     * Checks whether provided entity is supported.
     *
     * This method replaces the original KnpLabs SluggableListener to support EgzaktDoctrineBehaviors Sluggable Traits
     *
     * @param ClassMetadata $classMetadata The metadata
     *
     * @return Boolean
     */
    protected function isEntitySupported(ClassMetadata $classMetadata)
    {
        return $this->getClassAnalyzer()->hasTrait($classMetadata->reflClass, 'Egzakt\DoctrineBehaviorsBundle\Model\Sluggable\Sluggable', $this->isRecursive);
    }
}
