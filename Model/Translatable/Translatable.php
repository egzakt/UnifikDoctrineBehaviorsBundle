<?php

namespace Egzakt\DoctrineBehaviorsBundle\Model\Translatable;

/**
 * Translatable trait
 *
 * Should be in the entity that needs to be translated.
 */
trait Translatable
{
    use TranslatableProperties,
        TranslatableMethods
    ;
}
