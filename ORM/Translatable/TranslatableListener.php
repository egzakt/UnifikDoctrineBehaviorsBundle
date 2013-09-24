<?php

namespace Egzakt\DoctrineBehaviorsBundle\ORM\Translatable;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
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
     * Checks if entity is translatable
     *
     * @param ClassMetadata $classMetadata
     *
     * @return boolean
     */
    protected function isTranslatable(ClassMetadata $classMetadata)
    {
        $traitNames = $classMetadata->reflClass->getTraitNames();

        return in_array('Egzakt\DoctrineBehaviorsBundle\Model\Translatable\Translatable', $traitNames)
                && $classMetadata->reflClass->hasProperty('translations');
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
        $traitNames = $classMetadata->reflClass->getTraitNames();

        return in_array('Egzakt\DoctrineBehaviorsBundle\Model\Translatable\Translation', $traitNames)
                && $classMetadata->reflClass->hasProperty('translatable');
    }

    /**
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
     * Gets called on Flush of the Entity Manager
     *
     * Remove the null Translation entities automatically created by the Translation trait.
     *
     * @param OnFlushEventArgs $eventArgs
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $unitOfWork = $em->getUnitOfWork();

        // Loop through entities marked as insertion
        foreach($unitOfWork->getScheduledEntityInsertions() as $entity) {

            $classMetadata = $em->getClassMetadata(get_class($entity));

            if ($this->isTranslation($classMetadata)) {

                // Get the entity's changeSet
                $changeSet = $unitOfWork->getEntityChangeSet($entity);
                $emptyEntity = true;

                // Loop through each field
                foreach($changeSet as $field => $data) {

                    // [0] = old value, [1] = new value
                    // Check if a field, other than translatable and locale, changed
                    if (null !== $data[1] && !in_array($field, ['translatable', 'locale'])) {
                        $emptyEntity = false;
                        break;
                    }
                }

                // Only Locale and Translatable properties changed, remove this entity from the uow
                // so it doesn't get persisted
                if ($emptyEntity) {
                    $unitOfWork->detach($entity);
                }
            }
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
     * Returns hash of events, that this listener is bound to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Events::loadClassMetadata,
            Events::postLoad,
            Events::onFlush
        ];
    }
}
