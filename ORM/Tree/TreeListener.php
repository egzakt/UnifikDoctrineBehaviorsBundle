<?php

namespace Unifik\DoctrineBehaviorsBundle\ORM\Tree;

use Doctrine\ORM\Events;

/**
 * Class TreeListener
 *
 * Adds mapping to the tree entities.
 */
class TreeListener extends BaseTreeListener
{
    /**
     * Get Subscribed Events
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Events::loadClassMetadata,
            Events::onFlush
        ];
    }
}