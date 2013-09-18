<?php

namespace Egzakt\DoctrineBehaviorsBundle\ORM\Uploadable;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * Uploadable listener.
 *
 * Subscribe Doctrine Events to the uploadable entites.
 */
class UploadableListener implements EventSubscriber
{
    /**
     * @var string
     */
    protected $uploadRootDir;

    /**
     * Constructor
     *
     * @param $uploadRootDir
     */
    public function __construct($uploadRootDir)
    {
        $this->uploadRootDir = $uploadRootDir;
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

        if ($this->isEntitySupported($classMetadata)) {

            // Upload on new and updated entities
            $classMetadata->addLifecycleCallback('upload', Events::postPersist);
            $classMetadata->addLifecycleCallback('upload', Events::postUpdate);

            // Remove the file on the server when deleting an entity
            $classMetadata->addLifecycleCallback('removeUploads', Events::postRemove);
        }
    }

    /**
     * Post Load
     *
     * Inject the Upload Root Dir on entities loaded from the Entity Manager
     *
     * @param LifecycleEventArgs $eventArgs
     */
    public function postLoad(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        $em = $eventArgs->getEntityManager();
        $classMetadata = $em->getClassMetadata(get_class($entity));

        if ($this->isEntitySupported($classMetadata)) {

            // Set the upload root dir
            $entity->setUploadRootDir($this->uploadRootDir);
        }
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
            Events::postLoad
        ];
    }

    /**
     * Checks whether provided entity is supported.
     *
     * @param ClassMetadata $classMetadata The metadata
     *
     * @return Boolean
     */
    private function isEntitySupported(ClassMetadata $classMetadata)
    {
        $traitNames = $classMetadata->reflClass->getTraitNames();

        return in_array('Egzakt\DoctrineBehaviorsBundle\Model\Uploadable\Uploadable', $traitNames);
    }
}
