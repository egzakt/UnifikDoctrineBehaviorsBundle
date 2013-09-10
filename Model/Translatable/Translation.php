<?php

namespace Egzakt\DoctrineBehaviorsBundle\Model\Translatable;

/**
 * Translation trait.
 *
 * This is a replacement for the default KnpLabs Translation to use a different TranslationProperties
 * and TranslationMethods traits.
 */
trait Translation
{
    use TranslationProperties,
        TranslationMethods
    ;
}
