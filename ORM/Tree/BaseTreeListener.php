<?php

namespace Unifik\DoctrineBehaviorsBundle\ORM\Tree;

use Doctrine\ORM\Mapping\ClassMetadata,
    Doctrine\Common\EventSubscriber,
    Doctrine\ORM\Event\LoadClassMetadataEventArgs,
    Doctrine\ORM\EntityManager,
    Doctrine\ORM\Query,
    Doctrine\ORM\Event\OnFlushEventArgs,
    Doctrine\ORM\Mapping\MappingException;

use Unifik\DoctrineBehaviorsBundle\ORM\Tree\Strategy\StrategyInterface;

/**
 * Abstract class BaseTreeListener.
 *
 * Adds mapping to the tree entities.
 */
abstract class BaseTreeListener implements EventSubscriber
{
    /**
     * @var string
     */
    protected $entityName;

    /**
     * @var array
     */
    protected $excludedEntities = [];

    /**
     * List all tree strategies with their respective trait
     *
     * @var array
     */
    protected $strategies = [
        'MaterializedPath' => 'Unifik\DoctrineBehaviorsBundle\Model\Tree\MaterializedPath'
    ];

    /**
     * Contains strategy class instances
     *
     * @var array
     */
    protected $strategiesInstances = [];

    /**
     * @param $strategy
     * @return StrategyInterface
     * @throws \Exception
     */
    private function getStrategy($strategy)
    {
        if (!isset($this->strategiesInstances[$strategy])) {
            $strategyClass = $this->getNamespace() . '\\Strategy\\' . $strategy;

            if (!class_exists($strategyClass)) {
                throw new \Exception('Class ' . $strategyClass . ' not found.');
            }

            $strategyClass = new $strategyClass();

            if (!($strategyClass instanceof StrategyInterface)) {
                throw new \Exception('Class ' . $strategyClass . ' must implement StrategyInterface.');
            }

            $this->strategiesInstances[$strategy] = $strategyClass;
        }

        return $this->strategiesInstances[$strategy];
    }

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

        foreach ($this->strategies as $strategy => $traitName) {
            if ($this->isEntitySupported($classMetadata->reflClass, $traitName)) {

                // Add the strategy fields if necessary
                $this->mapFields($classMetadata, $strategy);
            }
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
        $this->updateTree($unitOfWork->getScheduledEntityInsertions(), $eventArgs, $em);

        // Updates
        $this->updateTree($unitOfWork->getScheduledEntityUpdates(), $eventArgs, $em);
    }

    /**
     * Update tree with the right strategy
     *
     * @param array $entities
     * @param OnFlushEventArgs $eventArgs
     * @param EntityManager $em
     * @throws \Exception
     */
    protected function updateTree(array $entities, OnFlushEventArgs $eventArgs, EntityManager $em)
    {
        foreach ($entities as $entity) {

            $classMetadata = $em->getClassMetadata(get_class($entity));

            foreach ($this->strategies as $strategy => $traitName) {
                if ($this->isEntitySupported($classMetadata->reflClass, $traitName)) {

                    $this->getStrategy($strategy)->updateTree($eventArgs, $entity);
                }
            }
        }
    }

    /**
     * Add Metadata fields on entity based on the strategy used
     *
     * @param ClassMetadata $classMetadata
     * @param string $strategy
     * @throws MappingException
     */
    protected function mapFields(ClassMetadata $classMetadata, $strategy)
    {
        $this->getStrategy($strategy)->mapFields($classMetadata);
    }

    /**
     * Checks whether provided entity is supported.
     *
     * @param \ReflectionClass $reflClass
     *
     * @return bool
     */
    protected function isEntitySupported(\ReflectionClass $reflClass, $traitName)
    {
        $traitNames = [];
        $originalReflClass = $reflClass;

        while ($reflClass) {
            $traitNames = array_merge($traitNames, $reflClass->getTraitNames());
            $reflClass = $reflClass->getParentClass();
        }

        return in_array($traitName, $traitNames)
        &&
        (
            (!in_array($originalReflClass->name, $this->excludedEntities) && !$this->entityName)
            || $originalReflClass->name == $this->entityName
        );
    }

    /**
     * Get Entity Name
     *
     * @return string
     */
    public function getEntityName()
    {
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
     * Set the list of excluded entities. These entities have their own service.
     *
     * @param array $excludedEntities
     */
    public function setExcludedEntities($excludedEntities)
    {
        $this->excludedEntities = $excludedEntities;
    }

    /**
     * Add an excluded entity
     *
     * @param $namespace
     */
    public function addExcludedEntity($namespace)
    {
        if (!in_array($namespace, $this->excludedEntities)) {
            $this->excludedEntities[] = $namespace;
        }
    }

    /**
     * Get the list of excluded entities. These entities have their own service.
     *
     * @return array
     */
    public function getExcludedEntities()
    {
        return $this->excludedEntities;
    }

    /**
     * {@inheritDoc}
     */
    protected function getNamespace()
    {
        return __NAMESPACE__;
    }
}