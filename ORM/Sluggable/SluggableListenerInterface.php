<?php

namespace Flexy\DoctrineBehaviorsBundle\ORM\Sluggable;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

/**
 * Class SluggableListenerInterface
 */
interface SluggableListenerInterface
{
    /**
     * Returns the name of the entity having a slug field which to map the SluggableListener
     *
     * @return array
     */
    public function getEntityName();

    /**
     * Set Entity Name
     *
     * @param $entityName
     */
    public function setEntityName($entityName);

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
    public function getSelectQueryBuilder($slug, $entity, EntityManager $em);
}