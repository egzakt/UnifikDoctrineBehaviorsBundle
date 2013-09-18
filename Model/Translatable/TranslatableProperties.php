<?php

namespace Egzakt\DoctrineBehaviorsBundle\Model\Translatable;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Translatable Properties
 */
trait TranslatableProperties
{
    /**
     * @var ArrayCollection $translations
     *
     * Will be mapped to translatable entity
     * by TranslatableListener
     */
    protected $translations;

    /**
     * @var string $currentLocale
     *
     * currentLocale is a non persisted field configured during postLoad event
     */
    protected $currentLocale;
}
