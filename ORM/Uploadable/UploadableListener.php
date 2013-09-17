<?php

/*
 * This file is part of the YtkoDoctrineBehaviors package.
 *
 * (c) Ytko <http://ytko.ru/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Egzakt\DoctrineBehaviorsBundle\ORM\Uploadable;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;

use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;

/**
 * Uploadable listener.
 *
 * Adds mapping to the uploadable entites.
 */
class UploadableListener implements EventSubscriber
{
    /**
     * @var ClassAnalyzer
     */
    protected $classAnalyzer;

    /**
     * @var string
     */
    protected $uploadRootDir;

    /**
     * Constructor
     *
     * @param ClassAnalyzer $classAnalyzer
     * @param $uploadRootDir
     */
    public function __construct(ClassAnalyzer $classAnalyzer, $uploadRootDir)
    {
        $this->classAnalyzer = $classAnalyzer;
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

            // Add the uploadPath field if necessary
            $this->mapUploadPath($classMetadata);

            // Upload on new and updated entities
            $classMetadata->addLifecycleCallback('upload', Events::postPersist);
            $classMetadata->addLifecycleCallback('upload', Events::postUpdate);

            // Remove the file on the server when deleting an entity
            $classMetadata->addLifecycleCallback('removeUpload', Events::postRemove);
        }
    }

    /**
     * Map Upload Path
     *
     * Add the uploadPath field if necessary
     *
     * @param ClassMetadata $classMetadata
     */
    protected function mapUploadPath(ClassMetadata $classMetadata)
    {
        if (!$classMetadata->hasField('uploadPath')) {
            $classMetadata->mapField([
                'fieldName' => 'uploadPath',
                'type' => 'string',
                'length' => 255,
                'nullable' => true
            ]);
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
        return $this->getClassAnalyzer()->hasTrait($classMetadata->reflClass, 'Egzakt\DoctrineBehaviorsBundle\Model\Uploadable\Uploadable');
    }

    /**
     * Get Class Analyzer
     *
     * @return ClassAnalyzer
     */
    protected function getClassAnalyzer()
    {
        return $this->classAnalyzer;
    }

}
