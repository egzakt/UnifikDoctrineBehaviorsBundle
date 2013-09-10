<?php

namespace Egzakt\DoctrineBehaviorsBundle\Model\Translatable;

/**
 * Translation trait.
 *
 * This is a replacement for the default KnpLabs TranslationMethods trait to remove the getId method.
 */
trait TranslationMethods
{
    /**
     * Sets entity, that this translation should be mapped to.
     *
     * @param Translatable $translatable The translatable
     */
    public function setTranslatable($translatable)
    {
        $this->translatable = $translatable;
    }

    /**
     * Returns entity, that this translation is mapped to.
     *
     * @return Translatable
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
