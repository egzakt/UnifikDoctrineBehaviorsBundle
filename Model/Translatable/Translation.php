<?php

namespace Flexy\DoctrineBehaviorsBundle\Model\Translatable;

/**
 * Translation trait
 *
 * Should be in the entity that translate the translatable entity.
 */
trait Translation
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

    /**
     * Sets entity, that this translation should be mapped to.
     *
     * @param mixed $translatable The translatable
     */
    public function setTranslatable($translatable)
    {
        $this->translatable = $translatable;
    }

    /**
     * Returns entity, that this translation is mapped to.
     *
     * @return mixed
     */
    public function getTranslatable()
    {
        return $this->translatable;
    }

    /**
     * Sets locale name for this translation.
     *
     * @param string $locale The locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * Returns this translation locale.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Tells if translation is empty
     *
     * @return bool true if translation is not filled
     */
    public function isEmpty()
    {
        return false;
    }
}
