<?php

namespace Egzakt\DoctrineBehaviorsBundle\Model\Translatable;

/**
 * Translation Properties
 */
trait TranslationProperties
{
    /**
     * @var string
     */
    protected $locale;

    /**
     * @var mixed
     *
     * Will be mapped to translatable entity
     * by TranslatableListener
     */
    protected $translatable;
}
