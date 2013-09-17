<?php

namespace Egzakt\DoctrineBehaviorsBundle\Model\Translatable;

/**
 * Translation Methods
 */
trait TranslationMethods
{
    /**
     * Set Translatable
     *
     * Sets entity, that this translation should be mapped to.
     *
     * @param Translatable $translatable The translatable
     */
    public function setTranslatable($translatable)
    {
        $this->translatable = $translatable;
    }

    /**
     * Get Translatable
     *
     * Returns entity, that this translation is mapped to.
     *
     * @return Translatable
     */
    public function getTranslatable()
    {
        return $this->translatable;
    }

    /**
     * Set Locale
     *
     * Sets locale name for this translation.
     *
     * @param string $locale The locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * Get Locale
     *
     * Returns this translation locale.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Is Empty
     *
     * Tells if translation is empty
     *
     * @return bool true if translation is not filled
     */
    public function isEmpty()
    {
        return false;
    }
}
