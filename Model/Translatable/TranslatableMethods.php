<?php

namespace Egzakt\DoctrineBehaviorsBundle\Model\Translatable;

use Knp\DoctrineBehaviors\Model\Translatable\TranslatableMethods as BaseTranslatableMethods;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Translatable trait.
 *
 * Should be used inside entity, that needs to be translated.
 */
trait TranslatableMethods
{
    use BaseTranslatableMethods;

    /**
     * Do Translate
     *
     * This method override the default one that returns the translation in the default locale
     * which is not the behavior we want. If no translation exists in the desired locale,
     * we return a new Translation entity in this locale.
     *
     * We also do not use the $newTranslations property. The translations are directly added to the translatable entity.
     *
     * @param null $locale
     *
     * @return \Knp\DoctrineBehaviors\Model\Translatable\Translation|null
     */
    protected function doTranslate($locale = null)
    {
        if (null === $locale) {
            $locale = $this->getCurrentLocale();
        }

        $translation = $this->findTranslationByLocale($locale);
        if ($translation and !$translation->isEmpty()) {
            return $translation;
        }

        $class       = self::getTranslationEntityClass();
        $translation = new $class();
        $translation->setLocale($locale);

        $this->addTranslation($translation);
        $translation->setTranslatable($this);

        return $translation;
    }

}
