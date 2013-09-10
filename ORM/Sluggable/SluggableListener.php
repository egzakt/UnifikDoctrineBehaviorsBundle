<?php
/**
 * @author Lusitanian
 * Freely released with no restrictions, re-license however you'd like!
 */

namespace Egzakt\DoctrineBehaviorsBundle\ORM\Sluggable;

use Knp\DoctrineBehaviors\ORM\Sluggable\SluggableListener as BaseSluggableListener;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs,
    Doctrine\Common\EventSubscriber,
    Doctrine\ORM\Events,
    Doctrine\ORM\Mapping\ClassMetadata;

/**
 * Sluggable listener.
 *
 * Adds mapping to sluggable entities.
 */
class SluggableListener extends BaseSluggableListener
{
    /**
     * Checks whether provided entity is supported.
     *
     * This method replaces the original KnpLabs SluggableListener to support EgzaktDoctrineBehaviors Sluggable Traits
     *
     * @param ClassMetadata $classMetadata The metadata
     *
     * @return Boolean
     */
    protected function isEntitySupported(ClassMetadata $classMetadata)
    {
        return $this->getClassAnalyzer()->hasTrait($classMetadata->reflClass, 'Egzakt\DoctrineBehaviorsBundle\Model\Sluggable\Sluggable', $this->isRecursive);
    }
}
