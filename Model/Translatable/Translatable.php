<?php

namespace Egzakt\DoctrineBehaviorsBundle\Model\Translatable;

use Knp\DoctrineBehaviors\Model\Translatable\TranslatableProperties;

/**
 * Translatable trait.
 *
 * This is a replacement for the default KnpLabs Translatable to use a different TranslatableMethods Trait.
 */
trait Translatable
{
    use TranslatableProperties,
        TranslatableMethods
    ;
}
