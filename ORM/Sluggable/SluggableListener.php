<?php

namespace Egzakt\DoctrineBehaviorsBundle\ORM\Sluggable;

use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;

use Doctrine\Common\EventArgs;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Sluggable listener.
 *
 * All sluggable listeners must extend this base Class
 */
class SluggableListener extends BaseSluggableListener implements EventSubscriber
{

    /**
     * Constructor
     *
     * @param ClassAnalyzer $classAnalyser
     *
     * @throws Exception
     */
    public function __construct(ClassAnalyzer $classAnalyser)
    {
        // The custom SluggableListener service that extends this class must implement
        // the SluggableListenerInterface
        if (!$this instanceof SluggableListenerInterface) {
            throw new Exception('This service class ' . get_class($this) . ' must implement the SluggableListenerInterface');
        }

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
            Events::onFlush
        ];
    }

}
