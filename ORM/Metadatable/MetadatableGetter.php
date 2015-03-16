<?php

namespace Unifik\DoctrineBehaviorsBundle\ORM\Metadatable;

use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class MetadatableGetter
 *
 * Get Metadata field of a Metadatable Entity
 */
class MetadatableGetter
{
    /**
     * @var ObjectManager $om
     */
    protected $om;

    /**
     * Constructor
     *
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * Get Metadata
     *
     * @param $entity
     * @param $metaName
     *
     * @return null
     */
    public function getMetadata($entity, $metaName)
    {
        if ($entity && $this->isEntitySupported($entity)) {
            $method = 'getMeta' . ucfirst($metaName);
            if (is_callable([$entity, $method])) {
                return $entity->$method();
            }
        }

        return null;
    }

    /**
     * Checks whether provided entity is supported by the Metadatable Controller.
     *
     * @param mixed $entity
     *
     * @return bool
     */
    protected function isEntitySupported($entity)
    {
        try {
            $classMetadata = $this->om->getMetadataFactory()->getMetadataFor(get_class($entity));
        } catch (\Exception $e) {
            return false;
        }

        $reflClass = $classMetadata->reflClass;

        $traitNames = [];

        while ($reflClass) {
            $traitNames = array_merge($traitNames, $reflClass->getTraitNames());
            $reflClass = $reflClass->getParentClass();
        }

        $supported = in_array('Unifik\DoctrineBehaviorsBundle\Model\Metadatable\Metadatable', $traitNames);

        if (!$supported && is_callable([$entity, 'getTranslation'])) {
            return $this->isEntitySupported($entity->getTranslation());
        }

        return $supported;
    }
}