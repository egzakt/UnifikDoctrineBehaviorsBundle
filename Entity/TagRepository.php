<?php

namespace Flexy\DoctrineBehaviorsBundle\Entity;

use Flexy\SystemBundle\Lib\BaseEntityRepository;

/**
 * TagRepository
 */
class TagRepository extends BaseEntityRepository
{
    /**
     * Get the criteria for the list of tags for a given entity
     *
     * @param $class
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getListByClassCriteria($class)
    {
        $queryBuilder = $this->createQueryBuilder('t')
                ->where('t.class = :class')
                ->setParameter('class', $class);

        return $queryBuilder;
    }

    /**
     * Find the list of internal tags for a given entity
     *
     * @param $class
     *
     * @return mixed
     */
    public function findInternals($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        $queryBuilder = $this->getListByClassCriteria($class)
                ->andWhere('t.name LIKE :internal')
                ->setParameter('internal', '_%');

        return $this->processQuery($queryBuilder);
    }
}
