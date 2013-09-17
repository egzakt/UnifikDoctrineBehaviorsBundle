DoctrineBehaviorsBundle
=======================

This bundle is highly inspired from [KnpLabs/DoctrineBehaviors](https://github.com/KnpLabs/DoctrineBehaviors) and has been adapted to the Egzakt Standard Distribution.

The original behaviors have been wrapped in a bundle.

For now, only two behaviors have been modified :

- [Translatable](#translatable)
- [Sluggable](#sluggable)

## How to use

### Translatable ###

#### The entities ####

You have to generate both Translatable and Translation entities. For example, Text and TextTranslation :

```yml
# Text.orm.yml
Egzakt\SystemBundle\Entity\Text:
  type: entity
  fields:
    id:
      type: integer
      id: true
      generator:
        strategy: AUTO
    createdAt:
      type: datetime
      gedmo:
        timestampable:
          on: create
    updatedAt:
      type: datetime
      gedmo:
        timestampable:
          on: update
```

```yml
# TextTranslation.orm.yml
Egzakt\SystemBundle\Entity\TextTranslation:
  type: entity
  fields:
    id:
      type: integer
      id: true
      generator:
        strategy: AUTO
    text:
      type: text
    name:
      type: string
      length: 255
      nullable: true
    active:
      type: boolean
      nullable: true
```

In the Translatable entity, add a `use` statement to include the `Translatable` trait

```php
<?php

namespace Egzakt\SystemBundle\Entity;

use Egzakt\DoctrineBehaviorsBundle\Model as EgzaktORMBehaviors;

/**
 * Text
 */
class Text
{
    use EgzaktORMBehaviors\Translatable\Translatable;

    /**
     * @var integer $id
     */
    protected $id;
    
    [...]
}
```

In the Translation entity, add a `use` statement to include the `Translation` trait

```php
<?php

namespace Egzakt\SystemBundle\Entity;

use Symfony\Component\Validator\ExecutionContextInterface;

use Egzakt\DoctrineBehaviorsBundle\Model as EgzaktORMBehaviors;

/**
 * TextTranslation
 */
class TextTranslation
{
    use EgzaktORMBehaviors\Translatable\Translation;

    /**
     * @var integer $id
     */
    protected $id;
    
    [...]
}
```

You're done! The `locale` field and the bidirectional relation are automatically registered to the entity's classMetadata with Doctrine Event Listeners with the following names :

```php
// $translations property
$textTranslations = $text->getTranslations();

// $translatable property
$text = $textTranslation->getTranslatable();

// $locale property
$locale = $text->getLocale()
```

Translated entities are loaded with the current locale on a postLoad Doctrine Event. If you want to load an entity in a specific locale, you can use the "setCurrentLocale" method :

```php
$text->setCurrentLocale('fr');
$name = $text->getName(); // Will return the 'fr' version of the $name property
```

#### The forms ####

You need to build a form for both the Translatable and the Translation entities, and embed a TranslationForm on the `translation` property :

```php
/**
 * Text Type
 */
class TextType extends AbstractType
{
    /**
     * Build Form
     *
     * @param FormBuilderInterface $builder The builder
     * @param array                $options Form options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('translation', new TextTranslationType())
        ;
    }
    
    [...]
}
```

```php
/**
 * Text Translation Type
 */
class TextTranslationType extends AbstractType
{
    /**
     * Build Form
     *
     * @param FormBuilderInterface $builder The builder
     * @param array                $options Form options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('active', 'checkbox')
            ->add('name')
            ->add('text', 'textarea', array('label' => 'Text', 'attr' => array('class' => 'ckeditor')))
        ;
    }
    
    [...]
}
```

In twig, you can simply render the form with `form_rest` or field by field :

```html
<table class="fields" cellspacing="0">

    {{ form_row(form.translation.active) }}

    {{ form_row(form.translation.name) }}

    {{ form_row(form.translation.text) }}

    {{ form_rest(form) }}

</table>
```

### Sluggable ###

This behavior is pretty simple to implement with only two steps :

- [Trait](#trait)
- [Service](#service)

#### Trait ####

The trait will be used to add a `slug` field to the entity's metadataClass and to configure the slug field.

You need to add a `use` statement to include the `sluggable` trait and override the `getSluggableFields` method to configure the fields (1 or more) to slug :

```php
<?php

namespace Egzakt\SystemBundle\Entity;

use Egzakt\DoctrineBehaviorsBundle\Model as EgzaktORMBehaviors;

/**
 * SectionTranslation
 */
class SectionTranslation
{
    use EgzaktORMBehaviors\Sluggable\Sluggable;

    /**
     * @var integer $id
     */
    protected $id;
    
    [...]
    
    /**
     * Get Sluggable Fields
     *
     * @return array
     */
    public function getSluggableFields()
    {
        return array('name');
    }
    
}
```

Other methods can be overrided to configure the behavior :

- `getIsSlugUnique` : Determines whether the slug is unique or not. Default is `true` (It supports the translatable behavior by looking for a similar slug in the current locale only).
- `getSlugDelimiter` : The slug delemiter. Default is `-`.
- `getRegenerateOnUpdate` : Determines if the slug should be regenerated when a sluggable field has been modified. Default is `true`. If set to `false`, the slug will be regenerated only if the slug field is set to `NULL` or an empty string.

#### Service ####

A service (listener) is necessary to activate the sluggable behavior because we need to perform some actions on Doctrine Events.
There's already a default service class that you can use that contains all the code to generate the slug according to the configuration you've made in your sluggable entity using the trait.

Simply define a new service using the default service class `%egzakt_doctrine_behaviors.sluggable.listener.class%` and setting the `type` to sluggable and the `entity` name, as follow :

```yml
# services.yml
services:
    egzakt_system.section_translation.sluggable.listener:
        class: %egzakt_doctrine_behaviors.sluggable.listener.class%
        tags:
            - { name: doctrine.event_subscriber, type: sluggable, entity: Egzakt\SystemBundle\Entity\SectionTranslation }
```

There is no need to create a new class for this service, simply give a unique name to this service (`egzakt_system.section_translation.sluggable_listener` in this case) and you're done.

When the slug is configured to be unique (via the `getIsSlugUnique` method in the entity/trait), a QueryBuilder is used to make a query on the entity's table to find a slug similar to the one generated by this behavior. While a slug is found, the slug will be appended by "-1", "-2", and so on.
Optionally, you can override the default `getSelectQueryBuilder` method to specify a different QueryBuilder. The QueryBuilder must find a slug similar to the entity's one, on other entities.

Simply use your own class for the service as follow :

```yml
services:
    egzakt_system.section_translation.sluggable.listener:
        class: %egzakt_system.section_translation.sluggable.listener.class%
        tags:
            - { name: doctrine.event_subscriber, type: sluggable, entity: Egzakt\SystemBundle\Entity\SectionTranslation }
```

The class needs to extend the `SluggableListener` abstract class.

Here is an example of a custom service. We try to find a similar slug only on entities having the same parent than the sluggable's one :

```php
// SectionTranslationSluggableListener.php

<?php

namespace Egzakt\SystemBundle\Lib;

use Egzakt\DoctrineBehaviorsBundle\ORM\Sluggable\SluggableListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

/**
 * Class SectionTranslationSluggableListener
 */
class SectionTranslationSluggableListener extends SluggableListener
{

    /**
     * Get Select Query Builder
     *
     * Returns the Select QueryBuilder that will check for a similar slug in the table
     * The slug will be valid when the Query returns 0 rows.
     *
     * @param string $slug
     * @param mixed $entity
     * @param EntityManager $em
     *
     * @return QueryBuilder
     */
    public function getSelectQueryBuilder($slug, $entity, EntityManager $em)
    {
        $translatable = $entity->getTranslatable();

        $queryBuilder = $em->createQueryBuilder()
                ->select('DISTINCT(s.slug)')
                ->from('Egzakt\SystemBundle\Entity\SectionTranslation', 's')
                ->innerJoin('s.translatable', 't')
                ->where('s.slug = :slug')
                ->andWhere('s.locale = :locale')
                ->setParameters([
                        'slug' => $slug,
                        'locale' => $entity->getLocale()
                ]);

        // On update, look for other slug, not the current entity slug
        if ($em->getUnitOfWork()->isScheduledForUpdate($entity)) {
            $queryBuilder->andWhere('t.id <> :id')
                ->setParameter('id', $translatable->getId());
        }

        // Only look for slug on the same level
        if ($translatable->getParent()) {
            $queryBuilder->andWhere('t.parent = :parent')
                ->setParameter('parent', $translatable->getParent());
        }

        return $queryBuilder;
    }

}
```
