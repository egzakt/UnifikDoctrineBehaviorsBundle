<?php

namespace Egzakt\DoctrineBehaviorsBundle\ORM\Translatable;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * Translatable Doctrine2 listener.
 *
 * Provides mapping for translatable entities and their translations.
 */
class TranslatableListener implements EventSubscriber
{
    /**
     * @var callable $currentLocaleCallable
     */
    private $currentLocaleCallable;

    /**
     * Constructor
     *
     * @param callable $currentLocaleCallable
     */
    public function __construct(callable $currentLocaleCallable = null)
    {
        $this->currentLocaleCallable = $currentLocaleCallable;
    }

    /**
     * Load Class Metadata
     *
     * Adds mapping to the translatable and translations.
     *
     * @param LoadClassMetadataEventArgs $eventArgs The event arguments
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $classMetadata = $eventArgs->getClassMetadata();

        if (null === $classMetadata->reflClass) {
            return;
        }

        if ($this->isTranslatable($classMetadata)) {
            $this->mapTranslatable($classMetadata);
        }

        if ($this->isTranslation($classMetadata)) {
            $this->mapTranslation($classMetadata);
        }

        if ($this->isTranslation($classMetadata)) {
            $this->mapLocale($classMetadata);
        }
    }

    /**
     * Map Translatable
     *
     * @param ClassMetadata $classMetadata
     */
    protected function mapTranslatable(ClassMetadata $classMetadata)
    {
        if (!$classMetadata->hasAssociation('translations')) {
            $classMetadata->mapOneToMany([
                'fieldName'     => 'translations',
                'mappedBy'      => 'translatable',
                'indexBy'       => 'locale',
                'cascade'       => ['persist', 'merge', 'remove'],
                'targetEntity'  => $classMetadata->name.'Translation',
                'orphanRemoval' => true
            ]);
        }
    }

    /**
     * Map Translation
     *
     * @param ClassMetadata $classMetadata
     */
    protected function mapTranslation(ClassMetadata $classMetadata)
    {
        if (!$classMetadata->hasAssociation('translatable')) {
            $classMetadata->mapManyToOne([
                'fieldName'    => 'translatable',
                'inversedBy'   => 'translations',
                'joinColumns'  => [[
                    'name'                 => 'translatable_id',
                    'referencedColumnName' => 'id',
                    'onDelete'             => 'CASCADE'
                ]],
                'targetEntity' => substr($classMetadata->name, 0, -11)
            ]);
        }

        $name = $classMetadata->getTableName().'_unique_translation';
        if (!$this->hasUniqueTranslationConstraint($classMetadata, $name)) {
            $classMetadata->setPrimaryTable([
                'uniqueConstraints' => [[
                    'name'    => $name,
                    'columns' => ['translatable_id', 'locale' ]
                ]],
            ]);
        }
    }

    /**
     * Map Locale
     *
     * Add a "locale" field to a Translation entity
     *
     * @param ClassMetadata $classMetadata
     */
    protected function mapLocale(ClassMetadata $classMetadata)
    {
        if (!$classMetadata->hasField('locale')) {
            $classMetadata->mapField([
                'fieldName' => 'locale',
                'type' => 'string',
                'length' => 5
            ]);
        }
    }

    /**
     * Has Unique Translation Constraint
     *
     * @param ClassMetadata $classMetadata
     * @param $name
     *
     * @return bool|void
     */
    protected function hasUniqueTranslationConstraint(ClassMetadata $classMetadata, $name)
    {
        if (!isset($classMetadata->table['uniqueConstraints'])) {
            return;
        }

        $constraints = array_filter($classMetadata->table['uniqueConstraints'], function($constraint) use ($name) {
            return $name === $constraint['name'];
        });

        return 0 !== count($constraints);
    }

    /**
     * Is Translatable
     *
     * Checks if entity is translatable
     *
     * @param ClassMetadata $classMetadata
     * @param bool          $isRecursive   true to check for parent classes until found
     *
     * @return boolean
     */
    protected function isTranslatable(ClassMetadata $classMetadata, $isRecursive = false)
    {
        return $classMetadata->reflClass->hasProperty('translations');
    }

    /**
     * Is Translation
     *
     * @param ClassMetadata $classMetadata
     *
     * @return boolean
     */
    protected function isTranslation(ClassMetadata $classMetadata)
    {
        return $classMetadata->reflClass->hasProperty('translatable');
    }

    /**
     * Post Load
     *
     * Sets the current locale on entities loaded from the EntityManager
     *
     * @param LifecycleEventArgs $eventArgs
     */
    public function postLoad(LifecycleEventArgs $eventArgs)
    {
        $em            = $eventArgs->getEntityManager();
        $entity        = $eventArgs->getEntity();
        $classMetadata = $em->getClassMetadata(get_class($entity));

        if (!$classMetadata->reflClass->hasMethod('setCurrentLocale')) {
            return;
        }

        if ($locale = $this->getCurrentLocale()) {
            $entity->setCurrentLocale($locale);
        }
    }

    /**
     * Get Current Locale
     *
     * @return string
     */
    protected function getCurrentLocale()
    {
        if ($currentLocaleCallable = $this->currentLocaleCallable) {
            return $currentLocaleCallable();
        }
    }

    /**
     * Get Subscribed Events
     *
     * Returns hash of events, that this listener is bound to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Events::loadClassMetadata,
            Events::postLoad,
        ];
    }
}
