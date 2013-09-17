<?php

namespace Egzakt\DoctrineBehaviorsBundle\Model\Translatable;

/**
 * Translation trait
 *
 * Should be in the entity that translate the translatable entity.
 */
trait Translation
{
    use TranslationProperties,
        TranslationMethods
    ;
}
