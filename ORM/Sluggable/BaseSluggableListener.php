<?php

namespace Egzakt\DoctrineBehaviorsBundle\ORM\Sluggable;

use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;

use Doctrine\Common\EventArgs;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\QueryBuilder;

/**
 * Sluggable listener.
 *
 * Adds mapping to sluggable entities and the slug field to the ClassMetadata
 */
abstract class BaseSluggableListener implements EventSubscriber
{

    /**
     * @var ClassAnalyzer
     */
    protected $classAnalyzer;

    /**
     * @var EntityManager
     */
    protected $em;

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
     * @param LifecycleEventArgs $eventArgs
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $this->generateSlug($eventArgs);
    }

    /**
     * Gets called before Updates
     *
     * @param LifecycleEventArgs $eventArgs
     */
    public function preUpdate(LifecycleEventArgs $eventArgs)
    {
        $this->generateSlug($eventArgs);
    }

    protected function generateSlug(LifecycleEventArgs $eventArgs)
    {
        $reflClass = new \ReflectionClass($eventArgs->getEntity());

        if ($this->isEntitySupported($reflClass)) {
            $this->doGenerateSlug($eventArgs);
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
        return $this->getClassAnalyzer()->hasTrait($reflClass, 'Egzakt\DoctrineBehaviorsBundle\Model\Sluggable\Sluggable')
               &&
               ($reflClass->name == $this->getEntityName());
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
     * Get Select Query Builder
     *
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

        // Basic Query
        $queryBuilder = $em->createQueryBuilder()
                ->select('DISTINCT(o.slug)')
                ->from($classMetadata->name, 'o')
                ->where('o.slug = :slug')
                ->setParameter('slug', $slug);

        if ($entity->getId()) {
            $queryBuilder->andWhere('o.id <> :id')
                ->setParameter('id', $entity->getId());
        }

        // Support the Translatable behavior
        if ($this->getClassAnalyzer()->hasMethod($classMetadata->reflClass, 'getLocale')
            && $this->getClassAnalyzer()->hasProperty($classMetadata->reflClass, 'translatable')
        )
        {
            $queryBuilder->andWhere('o.locale = :locale')
                    ->setParameter('locale', $entity->getLocale());
        }

        return $queryBuilder;
    }

    /**
     * Do Generate Slug
     *
     * Generates the slug based on the entity configured via a Trait
     *
     * @param LifecycleEventArgs $eventArgs
     * @return bool
     *
     * @throws \UnexpectedValueException
     */
    protected function doGenerateSlug(LifecycleEventArgs $eventArgs)
    {
        $slugField = 'slug';
        $entity = $eventArgs->getEntity();

        $em = $eventArgs->getEntityManager();
        $unitOfWork = $em->getUnitOfWork();
        $classMetadata = $em->getClassMetadata(get_class($entity));

        $changeSet = $unitOfWork->getEntityChangeSet($entity);
        $isInsert = $unitOfWork->isScheduledForInsert($entity);

        $slug = $classMetadata->getReflectionProperty($slugField)->getValue($entity);

        // New entity or empty slug
        if (!$isInsert && (!isset($changeSet[$slugField]) || $slug === '__id__')) {
            return false;
        }

        // Must fetch the old slug from changeSet since $entity holds the new version
        $oldSlug = isset($changeSet[$slugField]) ? $changeSet[$slugField][0] : $slug;
        $needToChangeSlug = false;

        // If slug is null or set to empty, regenerate it, or needs an update
        if (empty($slug) || $slug === '__id__' || !isset($changeSet[$slugField])) {

            $slug = '';

            $sluggableFields = $entity->getSluggableFields();

            // Must have at least one field to slug
            if (0 === count($sluggableFields)) {
                throw new \UnexpectedValueException(get_class($entity) . ' getSluggableFields() method should return at least one field.');
            }

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
     * Urlize
     *
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
     * Make Unique Slug
     *
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
