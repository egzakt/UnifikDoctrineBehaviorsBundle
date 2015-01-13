<?php

namespace Unifik\DoctrineBehaviorsBundle\ORM\Taggable;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;

/**
 * Taggable Doctrine2 Listener
 *
 * Listens to loadClassMetadata and adds n-n relation between a Taggable entity and the Tag entity.
 * This listener also manage adding/removing tags before flushing.
 */
class TaggableListener implements EventSubscriber
{
    /**
     * @var TagManager
     */
    protected $tagManager;

    /**
     * Constructor
     *
     * @param TagManager $tagManager
     */
    public function __construct(TagManager $tagManager)
    {
        $this->tagManager = $tagManager;
    }

    /**
     * Create a n-n relation between a Taggable entity and the Tag entity
     *
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $classMetadata = $eventArgs->getClassMetadata();

        if (null === $classMetadata->reflClass) {
            return;
        }

        // Add relation if this entity is supported
        if ($this->isEntitySupported($classMetadata->reflClass)) {
            $this->mapTag($classMetadata);
        }
    }

    /**
     * Map an entity with the Tag entity
     *
     * @param ClassMetadata $classMetadata
     */
    protected function mapTag(ClassMetadata $classMetadata)
    {
        if (!$classMetadata->hasAssociation('tags')) {

            $namingStrategy = $eventArgs
                ->getEntityManager()
                ->getConfiguration()
                ->getNamingStrategy();

            $metadata->mapManyToMany(array(
                'targetEntity'  => $metadata->getName(),
                'fieldName'     => 'tags',
                'cascade'       => array('persist'),
                'joinTable'     => array(
                    'name'        => strtolower($namingStrategy->classToTableName($metadata->getName())) . '_tags',
                    'joinColumns' => array(
                        array(
                            'name'                  => $namingStrategy->joinKeyColumnName($metadata->getName()),
                            'referencedColumnName'  => $namingStrategy->referenceColumnName(),
                            'onDelete'  => 'CASCADE',
                            'onUpdate'  => 'CASCADE',
                        ),
                    ),
                    'inverseJoinColumns'    => array(
                        array(
                            'name'                  => 'tag_id',
                            'referencedColumnName'  => $namingStrategy->referenceColumnName(),
                            'onDelete'  => 'CASCADE',
                            'onUpdate'  => 'CASCADE',
                        ),
                    )
                )
            ));
        }
    }

    /**
     * Checks whether provided entity is supported.
     *
     * @param \ReflectionClass $reflClass
     *
     * @return bool
     */
    private function isEntitySupported(\ReflectionClass $reflClass)
    {
        $traitNames = [];

        while ($reflClass) {
            $traitNames = array_merge($traitNames, $reflClass->getTraitNames());
            $reflClass = $reflClass->getParentClass();
        }

        return in_array('Unifik\DoctrineBehaviorsBundle\Model\Taggable\Taggable', $traitNames);
    }

    /**
     * Returns list of events, that this listener is listening to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Events::loadClassMetadata
        ];
    }
} 