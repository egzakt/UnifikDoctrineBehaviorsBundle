<?php

namespace Egzakt\DoctrineBehaviorsBundle\Model\Translatable;

use Knp\DoctrineBehaviors\Model\Translatable\TranslatableMethods as BaseTranslatableMethods;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Translatable trait.
 *
 * This is a replacement for the default KnpLabs Translatable trait to add magic functions
 * and modify the doTranslate method logic.
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

    /**
     * __call magic method
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
     * Get Translation
     *
     * @param string $locale The locale in which we want to get the translation entity
     *
     * @return \Knp\DoctrineBehaviors\Model\Translatable\Translation
     */
    public function getTranslation($locale = null)
    {
        if (!$locale) {
            $locale = $this->getCurrentLocale();
        }

        return $this->translate($locale);
    }

}
