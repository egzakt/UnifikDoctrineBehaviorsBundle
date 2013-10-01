<?php

namespace Egzakt\DoctrineBehaviorsBundle\Model\Translatable;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Translatable trait
 *
 * Should be in the entity that needs to be translated.
 */
trait Translatable
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

    /**
     * Returns translation for specific locale (creates new one if doesn't exists).
     * If requested translation doesn't exist, it will first try to fallback default locale
     * If any translation doesn't exist, it will be added to newTranslations collection.
     * In order to persist new translations, call mergeNewTranslations method, before flush
     *
     * @param string $locale The locale (en, ru, fr) | null If null, will try with current locale
     *
     * @return mixed
     */
    public function translate($locale = null)
    {
        return $this->doTranslate($locale);
    }

    /**
     * This method override the default one that returns the translation in the default locale
     * which is not the behavior we want. If no translation exists in the desired locale,
     * we return a new Translation entity in this locale.
     *
     * We also do not use the $newTranslations property. The translations are directly added to the translatable entity.
     *
     * @param null $locale
     *
     * @return mixed
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

    /**
     * Magic __call function
     *
     * This function will try to call a non-existing method on the translatable entity on the translation entity.
     *
     * @param $method
     * @param $arguments
     *
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return $this->proxyCurrentLocaleTranslation($method, $arguments);
    }

    /**
     * Magic __get function
     *
     * This methods allows to get translatable fields from parent entity
     *
     * @param $property
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function __get($property)
    {
        if (!property_exists($this, $property)) {
            if (method_exists($this->translate(), $getter = 'get'.ucfirst($property))) {
                return $this->translate()->$getter();
            }
        }

        throw new \Exception('Call to undefined property : ' . $property);
    }

    /**
     * Magic __set function
     *
     * This methods allows to set translatable fields from parent entity
     *
     * @param $property
     * @param $value
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function __set($property, $value)
    {
        if (!property_exists($this, $property)) {
            if (method_exists($this->translate(), $setter = 'set'.ucfirst($property))) {
                return $this->translate()->$setter($value);
            }
        }

        throw new \Exception('Trying to set an undefined property : ' . $property);
    }

    /**
    * Magic __isset function
    *
    * This method is being called to check if a property exists, in a translation entity
    *
    * @param string $name
    *
    * @return bool
    */
    public function __isset($name)
    {
        // If the property doesn't exist in this class...
        if (!property_exists($this, $name)) {

            // We take a look at the translation class
            if (property_exists($this->translate(), $name)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns collection of translations.
     *
     * @return ArrayCollection
     */
    public function getTranslations()
    {
        return $this->translations = $this->translations ?: new ArrayCollection();
    }

    /**
     * Get Translation
     *
     * @param string $locale The locale in which we want to get the translation entity
     *
     * @return mixed
     */
    public function getTranslation($locale = null)
    {
        if (!$locale) {
            $locale = $this->getCurrentLocale();
        }

        return $this->translate($locale);
    }

    /**
     * Adds new translation.
     *
     * @param Translation $translation The translation
     */
    public function addTranslation($translation)
    {
        $this->getTranslations()->set($translation->getLocale(), $translation);
        $translation->setTranslatable($this);
    }

    /**
     * Removes specific translation.
     *
     * @param Translation $translation The translation
     */
    public function removeTranslation($translation)
    {
        $this->getTranslations()->removeElement($translation);
    }

    /**
     * Set Current Locale
     *
     * @param mixed $locale the current locale
     */
    public function setCurrentLocale($locale)
    {
        $this->currentLocale = $locale;
    }

    /**
     * Get Current Locale
     */
    public function getCurrentLocale()
    {
        return $this->currentLocale ?: $this->getDefaultLocale();
    }

    /**
     * Get Default Locale
     *
     * Throw an exception when there's no locale defined as a locale should always be manually set on new entities
     * to work properly with the translatable behavior.
     *
     * @throws \Exception
     */
    public function getDefaultLocale()
    {
        throw new \Exception('The locale has not been set on this entity (new ' . get_class($this) . '())');
    }

    /**
     * Returns the list of available locales
     *
     * @return array
     */
    public function getLocales()
    {
        $locales = array();

        foreach($this->getTranslations() as $translation) {
            $locales[] = $translation->getLocale();
        }

        return $locales;
    }

    /**
     * Proxy Current Locale Translation
     *
     * @param $method
     * @param array $arguments
     *
     * @return mixed
     *
     * @throws \BadMethodCallException
     */
    protected function proxyCurrentLocaleTranslation($method, array $arguments = [])
    {
        if (!is_callable([$this->getTranslationEntityClass(), $method])) {
            throw new \BadMethodCallException(sprintf('The method "%s" doesn\'t exists in "%s" class nor in "%s" class',
                $method,
                __CLASS__,
                $this->getTranslationEntityClass()
            ));
        }

        return call_user_func_array(
            [$this->translate($this->getCurrentLocale()), $method],
            $arguments
        );
    }

    /**
     * Returns translation entity class name.
     *
     * @return string
     */
    public static function getTranslationEntityClass()
    {
        return __CLASS__.'Translation';
    }

    /**
     * Finds specific translation in collection by its locale.
     *
     * @param string $locale The locale (en, ru, fr)
     *
     * @return Translation|null
     */
    protected function findTranslationByLocale($locale)
    {
        $translation = $this->getTranslations()->get($locale);

        if ($translation) {
            return $translation;
        }
    }
}
