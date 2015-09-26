<?php

namespace Unifik\DoctrineBehaviorsBundle\ORM\Tree\Strategy;

use Doctrine\ORM\Mapping\ClassMetadata,
    Doctrine\ORM\Event\OnFlushEventArgs;


/**
 * Class StrategyInterface
 */
interface StrategyInterface
{
    /**
     * Add Metadata fields to entity based on strategy
     *
     * @param ClassMetadata $classMetadata
     */
    public function mapFields(ClassMetadata $classMetadata);

    /**
     * Update tree node and his children
     *
     * @param OnFlushEventArgs $eventArgs
     * @param $entity
     */
    public function updateTree(OnFlushEventArgs $eventArgs, $entity);
}