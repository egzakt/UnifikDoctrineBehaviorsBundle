<?php

namespace Unifik\DoctrineBehaviorsBundle\Form\ChoiceList;

use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityLoaderInterface;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;

/**
 * Entity loader used in taggable type.
 */
class TaggableEntityLoader implements EntityLoaderInterface
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var array
     */
    protected $options;

    /**
     * Constructor
     *
     * @param EntityManager $entityManager
     * @param $options
     */
    public function __construct(EntityManager $entityManager, $options)
    {
        $this->entityManager = $entityManager;
        $this->options = $options;
    }

    /**
     * Get the Form QueryBuilder
     *
     * @return mixed
     */
    protected function getQueryBuilder()
    {
        $em = $this->entityManager;

        $type = $this->options['use_global_tags'] ? null : $this->options['resource_type'];

        return $em->getRepository('UnifikDoctrineBehaviorsBundle:Tag')
                ->getTagsQueryBuilder($type, $this->options['locale']);
    }

    /**
     * Returns an array of entities that are valid choices in the corresponding choice list.
     *
     * @return array The entities.
     */
    public function getEntities()
    {
        return $this->getQueryBuilder()->getQuery()->getResult();
    }

    /**
     * Returns an array of entities matching the given identifiers.
     *
     * @param string $identifier The identifier field of the object. This method
     *                           is not applicable for fields with multiple
     *                           identifiers.
     * @param array  $values     The values of the identifiers.
     *
     * @return array The entities.
     */
    public function getEntitiesByIds($identifier, array $values)
    {
        $queryBuilder = $this->getQueryBuilder();

        $where = $queryBuilder
            ->expr()
            ->in('t.'.$identifier, ':ids');

        $queryBuilder
            ->andWhere($where)
            ->setParameter('ids', $values, Connection::PARAM_INT_ARRAY);

        return $queryBuilder->getQuery()->getResult();
    }
}
