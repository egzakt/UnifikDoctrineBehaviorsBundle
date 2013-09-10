<?php

/*
 * This file is part of the KnpDoctrineBehaviors package.
 *
 * (c) KnpLabs <http://knplabs.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Egzakt\DoctrineBehaviorsBundle\ORM\Translatable;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;

use Knp\DoctrineBehaviors\ORM\Translatable\TranslatableListener as BaseTranslatableListener;

/**
 * Translatable Doctrine2 listener.
 *
 * Provides mapping for translatable entities and their translations.
 *
 * This Listener override the KnpLabs TranslatableListener to add the automatic registering of the
 * locale field when using YML.
 */
class TranslatableListener extends BaseTranslatableListener
{

    /**
     * Adds mapping to the translatable and translations.
     *
     * @param LoadClassMetadataEventArgs $eventArgs The event arguments
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        parent::loadClassMetadata($eventArgs);

        if ($this->isTranslation($classMetadata)) {
            $this->mapLocale($classMetadata);
        }
    }

    /**
     * Map Locale
     *
     * Add a "locale" field to a Translation entity
     *
     * @param ClassMetadata $classMetadata
     */
    protected function mapLocale(ClassMetadata $classMetadata)
    {
        if (!$classMetadata->hasField('locale')) {
            $classMetadata->mapField([
                'fieldName' => 'locale',
                'type' => 'string',
                'length' => 5
            ]);
        }
    }

    /**
     * Is Translation
     *
     * Return true if it's a Translation entity
     *
     * @param ClassMetadata $classMetadata
     *
     * @return bool
     */
    protected function isTranslation(ClassMetadata $classMetadata)
    {
        return $this->getClassAnalyzer()->hasProperty($classMetadata->reflClass, 'translatable');
    }

}
