<?php

namespace Egzakt\DoctrineBehaviorsBundle\ORM\Sluggable;

use Doctrine\Common\EventArgs;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\QueryBuilder;

/**
 * Sluggable listener.
 *
 * Adds mapping to sluggable entities and the slug field to the ClassMetadata
 */
abstract class BaseSluggableListener implements EventSubscriber
{

    const SLUG_FIELD = 'slug';

    /**
     * @var string
     */
    protected $entityName;

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
     * Gets called before Inserts
     *
     * @param LifecycleEventArgs $eventArgs
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        $em = $eventArgs->getEntityManager();
        $classMetadata = $em->getClassMetadata(get_class($entity));

        if ($this->isEntitySupported($classMetadata->reflClass)) {

            // Allows identifier fields to be slugged as usual
            $this->allowIdentifierSlug($classMetadata, $entity);
        }
    }

    /**
     * Gets called on Flush of the Entity Manager
     *
     * @param OnFlushEventArgs $eventArgs
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $unitOfWork = $em->getUnitOfWork();

        // Inserts
        $this->generateSlug($unitOfWork->getScheduledEntityInsertions(), $eventArgs, $em);

        // Updates
        $this->generateSlug($unitOfWork->getScheduledEntityUpdates(), $eventArgs, $em);
    }

    /**
     * Allows identifier fields to be slugged as usual
     *
     * @param $classMetadata
     * @param $entity
     */
    protected function allowIdentifierSlug($classMetadata, $entity)
    {
        if ($classMetadata->isIdentifier(self::SLUG_FIELD)) {
            $classMetadata->getReflectionProperty(self::SLUG_FIELD)->setValue($entity, '__id__');
        }
    }

    /**
     * Loop through the entities to check if they are supported.
     * If so, generate a slug!
     *
     * @param array $entities
     * @param OnFlushEventArgs $eventArgs
     * @param EntityManager $em
     */
    protected function generateSlug(array $entities, OnFlushEventArgs $eventArgs, EntityManager $em)
    {
        foreach ($entities as $entity) {

            $classMetadata = $em->getClassMetadata(get_class($entity));

            if ($this->isEntitySupported($classMetadata->reflClass)) {
                $this->doGenerateSlug($eventArgs, $entity);
            }
        }
    }

    /**
     * Add a "slug" field to a sluggable entity
     *
     * @param ClassMetadata $classMetadata
     */
    protected function mapSlug(ClassMetadata $classMetadata)
    {
        if (!$classMetadata->hasField(self::SLUG_FIELD)) {
            $classMetadata->mapField([
                'fieldName' => self::SLUG_FIELD,
                'type' => 'string',
                'length' => 255,
                'nullable' => false
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
    protected function isEntitySupported(\ReflectionClass $reflClass)
    {
        $traitNames = $reflClass->getTraitNames();

        return in_array('Egzakt\DoctrineBehaviorsBundle\Model\Sluggable\Sluggable', $traitNames)
                && $reflClass->name == $this->getEntityName();
    }

    /**
     * Get Entity Name
     *
     * @return string
     *
     * @throws \Exception
     */
    public function getEntityName()
    {
        if (!$this->entityName) {
            throw new \Exception('The «' . get_class($this) . '» service definition have a missing parameter "entity".');
        }

        return $this->entityName;
    }

    /**
     * Set Entity Name
     *
     * @param $entityName
     */
    public function setEntityName($entityName)
    {
        $this->entityName = $entityName;
    }

    /**
     * Returns the Select QueryBuilder that will check for a similar slug in the table
     * The slug will be valid when the Query returns 0 rows.
     *
     * @param string $slug
     * @param mixed $entity
     * @param EntityManager $em
     *
     * @return QueryBuilder
     */
    public function getSelectQueryBuilder($slug, $entity, EntityManager $em)
    {
        $classMetadata = $em->getClassMetadata(get_class($entity));

        $translation = false;

        // Check if it's a translation entity
        if ($this->isTranslation($classMetadata)) {

            $translation = true;
            $translatable = $entity->getTranslatable();
        }

        // Basic Query
        $queryBuilder = $em->createQueryBuilder()
                ->select('DISTINCT(o.slug)')
                ->from($classMetadata->name, 'o')
                ->where('o.slug = :slug')
                ->setParameter(self::SLUG_FIELD, $slug);

        // Don't find the slug of the current entity
        // If it's a translation entity, check with the Translatable entity ID
        if ($translation) {

            // On update only
            if ($em->getUnitOfWork()->isScheduledForUpdate($entity)) {
                $queryBuilder->innerJoin('o.translatable', 't')
                        ->andWhere('t.id <> :id')
                        ->setParameter('id', $translatable->getId());
            }

        // Not a translation, check with the current entity ID
        } else {

            // On update only
            if ($em->getUnitOfWork()->isScheduledForUpdate($entity)) {
                $queryBuilder->andWhere('o.id <> :id')
                    ->setParameter('id', $entity->getId());
            }
        }

        // Support the Translatable behavior
        if ($classMetadata->reflClass->hasMethod('getLocale') && $classMetadata->reflClass->hasProperty('translatable')) {
            $queryBuilder->andWhere('o.locale = :locale')
                    ->setParameter('locale', $entity->getLocale());
        }

        return $queryBuilder;
    }

    /**
     * Return true if it's a Translation entity
     *
     * @param ClassMetadata $classMetadata
     *
     * @return bool
     */
    protected function isTranslation(ClassMetadata $classMetadata)
    {
        return $classMetadata->reflClass->hasProperty('translatable');
    }

    /**
     * Generates the slug based on the entity configured via a Trait
     *
     * @param OnFlushEventArgs $eventArgs
     * @param $entity
     *
     * @return bool
     *
     * @throws \UnexpectedValueException
     */
    protected function doGenerateSlug(OnFlushEventArgs $eventArgs, $entity)
    {
        $slugField = self::SLUG_FIELD;

        $em = $eventArgs->getEntityManager();
        $unitOfWork = $em->getUnitOfWork();
        $classMetadata = $em->getClassMetadata(get_class($entity));

        $changeSet = $unitOfWork->getEntityChangeSet($entity);
        $isInsert = $unitOfWork->isScheduledForInsert($entity);

        $slug = $classMetadata->getReflectionProperty($slugField)->getValue($entity);

        // Get the sluggable fields
        $sluggableFields = $entity->getSluggableFields();

        // Must have at least one field to slug
        if (0 === count($sluggableFields)) {
            throw new \UnexpectedValueException(get_class($entity) . ' getSluggableFields() method should return at least one field.');
        }

        $regenerateSlug = false;

        if ($entity->getRegenerateOnUpdate() && $this->sluggableFieldsChanged($entity, $changeSet)) {
            $regenerateSlug = true;
        }

        // New entity or empty slug
        if (!$isInsert && !$regenerateSlug && (!isset($changeSet[$slugField]) || $slug === '__id__')) {
            return false;
        }

        // Must fetch the old slug from changeSet since $entity holds the new version
        $oldSlug = isset($changeSet[$slugField]) ? $changeSet[$slugField][0] : $slug;
        $needToChangeSlug = false;

        // If slug is null or set to empty, regenerate it, or needs an update
        if (empty($slug) || $slug === '__id__' || !isset($changeSet[$slugField]) || $regenerateSlug) {

            $slug = '';

            // Loop through sluggable fields to build the slug
            foreach ($sluggableFields as $sluggableField) {

                if (isset($changeSet[$sluggableField]) || isset($changeSet[$slugField])) {
                    $needToChangeSlug = true;
                }

                $slug .= $classMetadata->getReflectionProperty($sluggableField)->getValue($entity) . ' ';
            }

        } else {

            // Slug was set manually
            $needToChangeSlug = true;
        }

        // Slug need to be changed, do further processing
        if ($needToChangeSlug) {

            // Throw an error on empty slug
            if (!strlen(trim($slug))) {
                throw new \UnexpectedValueException('Sluggable expects to have at least one usable (non-empty) field from the following: [ ' . implode($sluggableFields, ',') .' ]');
            }

            // Urlize the slug
            $slug = $this->urlize($slug, $entity->getSlugDelimiter());

            if ($entity->getIsSlugUnique()) {
                $slug = $this->makeUniqueSlug($slug, $entity, $em);
            }
            
            // Set the final slug
            $classMetadata->getReflectionProperty($slugField)->setValue($entity, $slug);
            $unitOfWork->propertyChanged($entity, $slugField, $oldSlug, $slug);

            // Recompute changeSet
            $unitOfWork->recomputeSingleEntityChangeSet($classMetadata, $entity);

        }
    }

    /**
     * Determines whether one or more sluggable fields changed or not
     *
     * @param $entity
     * @param array $changeSet
     *
     * @return bool
     */
    protected function sluggableFieldsChanged($entity, array $changeSet)
    {
        // Loop through sluggable fields
        foreach ($entity->getSluggableFields() as $sluggableField) {

            // Check if one of these changed
            if (isset($changeSet[$sluggableField])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns an urlized version of a string
     *
     * @param $sluggableText
     * @param $slugDelemiter
     *
     * @return mixed
     */
    protected function urlize($sluggableText, $slugDelemiter)
    {
        $urlized = strtolower(trim(preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', iconv('UTF-8', 'ASCII//TRANSLIT', $sluggableText)), $slugDelemiter));
        $urlized = preg_replace("/[\/_|+ -]+/", $slugDelemiter, $urlized);
        $urlized = trim($urlized, '-');

        return $urlized;
    }

    /**
     * Generate a unique slug for an Entity based on the specified Query
     *
     * @param $slug
     * @param $entity
     * @param EntityManager $em
     *
     * @return mixed
     */
    protected function makeUniqueSlug($slug, $entity, EntityManager $em)
    {
        $exposant = 0;
        $uniqueSlug = $slug;

        do {

            if ($exposant) {
                $uniqueSlug = $slug . '-' . $exposant;
            }

            $result = $this->getSelectQueryBuilder($uniqueSlug, $entity, $em)->getQuery()->getArrayResult();

            $exposant++;

        } while (count($result) > 0);

        return $uniqueSlug;
    }

}
