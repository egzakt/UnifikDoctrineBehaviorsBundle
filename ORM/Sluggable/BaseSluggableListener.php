<?php

namespace Egzakt\DoctrineBehaviorsBundle\ORM\Sluggable;

use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;

use Doctrine\Common\EventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Sluggable listener.
 *
 * Adds mapping to sluggable entities and the slug field to the ClassMetadata
 */
abstract class BaseSluggableListener implements SluggableListenerInterface, EventSubscriber
{

    /**
     * @var ClassAnalyzer
     */
    protected $classAnalyzer;

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

        if ($this->isEntitySupported($classMetadata->reflClass)) {

            // Add the slug field is necessary
            $this->mapSlug($classMetadata);
        }
    }

    /**
     * Pre Persist
     *
     * Gets called before Inserts
     *
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $reflClass = new \ReflectionClass($args->getEntity());

        if ($this->isEntitySupported($reflClass)) {

        }
    }

    /**
     * Gets called before Updates
     *
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        $reflClass = new \ReflectionClass($args->getEntity());

        if ($this->isEntitySupported($reflClass)) {

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
     * Is Entity Supported
     *
     * Checks whether provided entity is supported.
     *
     * @param \ReflectionClass $reflClass
     *
     * @return bool
     */
    protected function isEntitySupported(\ReflectionClass $reflClass)
    {
        return $this->getClassAnalyzer()->hasTrait($reflClass, 'Egzakt\DoctrineBehaviorsBundle\Model\Sluggable\Sluggable');
    }

    /**
     * Get Class Analyzer
     *
     * @return ClassAnalyzer
     */
    protected function getClassAnalyzer()
    {
        return $this->classAnalyzer;
    }

    /**
     * Get Sluggable Fields
     *
     * Returns the list of sluggable fields
     *
     * @return array
     */
    public function getSluggableFields()
    {
        return array();
    }

    /**
     * Get Slug Delemiter
     *
     * Returns the slug delemiter
     *
     * @return string
     */
    public function getSlugDelimiter()
    {
        return '-';
    }
}
