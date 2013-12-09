<?php

namespace Flexy\DoctrineBehaviorsBundle\ORM\Taggable;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManager;

/**
 * Taggable Doctrine2 listener.
 *
 * Provides mapping for taggable entities.
 */
class TaggableListener implements EventSubscriber
{
    /**
     * Adds mapping to the taggable entity.
     *
     * @param LoadClassMetadataEventArgs $eventArgs The event arguments
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $classMetadata = $eventArgs->getClassMetadata();

        if (null === $classMetadata->reflClass) {
            return;
        }

        if ($this->isTaggable($classMetadata)) {
            $this->mapTaggable($classMetadata, $eventArgs->getEntityManager());
        }
    }

    /**
     * Map Taggable to an entity
     *
     * @param ClassMetadata $classMetadata
     * @param EntityManager $em
     */
    protected function mapTaggable(ClassMetadata $classMetadata, EntityManager $em)
    {
        if (!$classMetadata->hasAssociation('tags')) {
            $class = strtolower($this->getClassnameWithoutNamespace($classMetadata));

            $classMetadata->mapManyToMany([
                'fieldName' => 'tags',
                'targetEntity' => 'Flexy\DoctrineBehaviorsBundle\Entity\Tag',
                'inversedBy' => $class . 's',
                'cascade' => ['persist', 'merge', 'remove'],
                'joinTable' => array(
                    'name' => $class . '_tag',
                    'joinColumns' => array(array('name' => $class . '_id', 'referencedColumnName' => 'id')),
                    'inverseJoinColumns' => array(array('name' => 'tag_id', 'referencedColumnName' => 'id')),
                )
            ]);
        }
    }

    /**
     * Get the class name from a fully-qualified namespace
     *
     * @param ClassMetadata $classMetadata
     *
     * @return string
     */
    private function getClassnameWithoutNamespace(ClassMetadata $classMetadata)
    {
        return str_replace($classMetadata->namespace . '\\', '', $classMetadata->name);
    }

    /**
     * Checks if entity is taggable
     *
     * @param ClassMetadata $classMetadata
     *
     * @return boolean
     */
    protected function isTaggable(ClassMetadata $classMetadata)
    {
        $traitNames = $classMetadata->reflClass->getTraitNames();

        return in_array('Flexy\DoctrineBehaviorsBundle\Model\Taggable\Taggable', $traitNames)
                && $classMetadata->reflClass->hasProperty('tags');
    }

    /**
     * Returns hash of events, that this listener is bound to.
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
