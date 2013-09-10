<?php

namespace Egzakt\DoctrineBehaviorsBundle\ORM\Sluggable;

use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;

use Doctrine\Common\EventArgs;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;

/**
 * Sluggable listener.
 *
 * Adds mapping to sluggable entities and the slug field to the ClassMetadata
 */
class SluggableListener extends BaseSluggableListener implements EventSubscriber
{

    /**
     * Constructor
     *
     * @param ClassAnalyzer $classAnalyser
     */
    public function __construct(ClassAnalyzer $classAnalyser)
    {
        $this->classAnalyzer = $classAnalyser;
    }

    /**
     * Get Subscribed Events
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Events::loadClassMetadata,
            Events::prePersist,
            Events::preUpdate
        ];
    }

}
