<?php

namespace Egzakt\DoctrineBehaviorsBundle\Model\Translatable;

/**
 * Translation trait.
 *
 * This is a replacement for the default KnpLabs TranslationProperties trait to remove the ID property.
 */
trait TranslationProperties
{
    /**
     * @ORM\Column(type="string")
     */
    protected $locale;

    /**
     * Will be mapped to translatable entity
     * by TranslatableListener
     */
    protected $translatable;
}
