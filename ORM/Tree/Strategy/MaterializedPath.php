<?php

namespace Unifik\DoctrineBehaviorsBundle\ORM\Tree\Strategy;

use Doctrine\ORM\Mapping\ClassMetadata,
    Doctrine\ORM\Event\OnFlushEventArgs,
    Doctrine\ORM\Events,
    Doctrine\ORM\Mapping\MappingException;

use Unifik\DoctrineBehaviorsBundle\Model\Tree\MaterializedPath as Node;

class MaterializedPath implements StrategyInterface
{
    const   PATH_FIELD = 'materializedPath',
            PARENT_FIELD = 'parent',
            CHILDREN_FIELD = 'children',
            ORDERING_FIELD = 'ordering',
            HASH_LENGTH = 6;

    /**
     * @param ClassMetadata $classMetadata
     * @throws MappingException
     */
    public function mapFields(ClassMetadata $classMetadata)
    {
        if (!$classMetadata->hasField(self::PATH_FIELD)) {
            $classMetadata->mapField([
                'fieldName' => self::PATH_FIELD,
                'type' => 'string',
                'length' => 255,
                'nullable' => true
            ]);
        }

        if (!$classMetadata->hasAssociation(self::PARENT_FIELD)) {
            $classMetadata->mapManyToOne([
                'fieldName' => self::PARENT_FIELD,
                'targetEntity' => $classMetadata->getName(),
                'inversedBy' => self::CHILDREN_FIELD,
                'joinColumns' => [[
                    'name' => self::PARENT_FIELD.'_id',
                    'referencedColumnName' => 'id',
                    'onDelete' => 'CASCADE'
                ]]
            ]);
        }

        if (!$classMetadata->hasAssociation(self::CHILDREN_FIELD)) {
            $mapping = [
                'fieldName' => self::CHILDREN_FIELD,
                'targetEntity' => $classMetadata->getName(),
                'mappedBy' => self::PARENT_FIELD
            ];

            if ($classMetadata->hasField(self::ORDERING_FIELD)) {
                $mapping['orderBy'] = [self::ORDERING_FIELD => 'ASC'];
            }

            $classMetadata->mapOneToMany($mapping);
        }

        $classMetadata->addLifecycleCallback('initNode', Events::prePersist);
        $classMetadata->addLifecycleCallback('initNode', Events::preFlush);
    }

    /**
     * @param OnFlushEventArgs $eventArgs
     * @param Node $entity
     * @return bool
     */
    public function updateTree(OnFlushEventArgs $eventArgs, $entity)
    {
        $em = $eventArgs->getEntityManager();
        $unitOfWork = $em->getUnitOfWork();
        $classMetadata = $em->getClassMetadata(get_class($entity));

        $changeSet = $unitOfWork->getEntityChangeSet($entity);

        $regeneratePath = false;

        if ($this->parentFieldChanged($changeSet)) {
            $regeneratePath = true;
        }

        $oldPath = $classMetadata->getReflectionProperty(self::PATH_FIELD)->getValue($entity);

        if (!$unitOfWork->isScheduledForInsert($entity) && !$regeneratePath && '__empty__' != $oldPath) {
            // No need for update
            return false;
        }

        $id = $classMetadata->getReflectionProperty('id')->getValue($entity);
        $hash = $this->generateHash($id);

        $parentPath = '';
        $parent = $classMetadata->getReflectionProperty(self::PARENT_FIELD)->getValue($entity);

        if ($parent) {
            $parentPath = $classMetadata->getReflectionProperty(self::PATH_FIELD)->getValue($parent);
        }

        $newPath = $parentPath . $hash;

        // Set the final path
        $classMetadata->getReflectionProperty(self::PATH_FIELD)->setValue($entity, $newPath);
        $unitOfWork->propertyChanged($entity, self::PATH_FIELD, $oldPath, $newPath);

        // Recompute changeSet
        $unitOfWork->recomputeSingleEntityChangeSet($classMetadata, $entity);

        return true;
    }

    /**
     * @param array $changeSet
     * @return bool
     */
    protected function parentFieldChanged(array $changeSet)
    {
        if (isset($changeSet[self::PARENT_FIELD])) {
            return true;
        }

        return false;
    }

    /**
     * Convert base10 to base62, return length standardized string
     *
     * @param $id
     * @return string
     */
    protected function generateHash($id)
    {
        return str_pad(gmp_strval(gmp_init($id, 10), 62), self::HASH_LENGTH, '0', STR_PAD_LEFT);
    }
}