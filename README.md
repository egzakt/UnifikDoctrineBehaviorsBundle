DoctrineBehaviorsBundle
=======================

This bundle is highly inspired from [KnpLabs/DoctrineBehaviors](https://github.com/KnpLabs/DoctrineBehaviors).
Some behaviors have been modified because they didn't accomplish exactly what we wanted.

A new Uploadable behavior has been added.

The original behaviors have been wrapped in a Symfony2 bundle.

*PHP 5.4 is required because we use traits*.

For now, these behaviors are available :

- [Translatable](#translatable)
- [TranslatableEntityRepository](#translatableentityrepository)
- [Sluggable](#sluggable)
- [Uploadable](#uploadable)
- [Timestampable](#timestampable)
- [Blameable](#blameable)
- [SoftDeletable](#softdeletable)

## How to use


### Translatable ###

#### The entities ####

You have to generate both Translatable and Translation entities. For example, Text and TextTranslation :

```yaml
# Text.orm.yml
Flexy\SystemBundle\Entity\Text:
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

```yaml
# TextTranslation.orm.yml
Flexy\SystemBundle\Entity\TextTranslation:
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

namespace Flexy\SystemBundle\Entity;

use Flexy\DoctrineBehaviorsBundle\Model as FlexyORMBehaviors;

/**
 * Text
 */
class Text
{
    use FlexyORMBehaviors\Translatable\Translatable;

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

namespace Flexy\SystemBundle\Entity;

use Symfony\Component\Validator\ExecutionContextInterface;

use Flexy\DoctrineBehaviorsBundle\Model as FlexyORMBehaviors;

/**
 * TextTranslation
 */
class TextTranslation
{
    use FlexyORMBehaviors\Translatable\Translation;

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


### TranslatableEntityRepository ###

This trait is related to the Translatable behavior.
It handles automatic LEFT/INNER JOIN on Translation tables to avoid additional queries to fetch the translation rows, when using the `find`, `findBy`, `findOneBy` and `findAll` methods.

To use this trait, you need to extend the `Doctrine\ORM\EntityRepository` and implement the `Symfony\Component\DependencyInjection\ContainerAwareInterface`, as follow :

```php
<?php

namespace Flexy\SystemBundle\Entity;

use Flexy\DoctrineBehaviorsBundle\Model as FlexyORMBehaviors;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

/**
 * SectionRepository
 */
class SectionRepository extends EntityRepository implements ContainerAwareInterface
{
    use FlexyORMBehaviors\Repository\TranslatableEntityRepository;
}
```


### Sluggable ###

This behavior is pretty simple to implement with only two steps :

- [Trait](#trait)
- [Service](#service)

#### Trait ####

The trait will be used to add a `slug` field to the entity's metadataClass and to configure the slug field.

You need to add a `use` statement to include the `sluggable` trait and define the `getSluggableFields` method (declared as abstract in the trait) to configure the fields (1 or more) to slug :

```php
<?php

namespace Flexy\SystemBundle\Entity;

use Flexy\DoctrineBehaviorsBundle\Model as FlexyORMBehaviors;

/**
 * SectionTranslation
 */
class SectionTranslation
{
    use FlexyORMBehaviors\Sluggable\Sluggable;

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

Other methods can be overloaded to configure the behavior :

- `getIsSlugUnique` : Determines whether the slug is unique or not. Default is `true` (It supports the translatable behavior by looking for a similar slug in the current locale only).
- `getSlugDelimiter` : The slug delemiter. Default is `-`.
- `getRegenerateOnUpdate` : Determines if the slug should be regenerated when a sluggable field has been modified. Default is `true`. If set to `false`, the slug will be regenerated only if the slug field is set to `NULL` or an empty string.

#### Service ####

There is no need to create a service is you wish to use the default behavior.

When the slug is configured to be unique (via the `getIsSlugUnique` method in the entity/trait), a QueryBuilder is used to make a query on the entity's table to find a slug similar (in the same locale if it's a Translation entity) to the one generated by this behavior. While a slug is found, the slug will be appended by "-1", "-2", and so on.
Optionally, you can create a new service and override the default `getSelectQueryBuilder` method to specify a different QueryBuilder. The QueryBuilder must find a slug similar to the entity's one, on other entities.

Simply use your own class for the service as follow :

```yml
services:
    flexy_system.section_translation.sluggable.listener:
        class: %flexy_system.section_translation.sluggable.listener.class%
        tags:
            - { name: doctrine.event_subscriber, type: sluggable, entity: Flexy\SystemBundle\Entity\SectionTranslation }
```

The class needs to extend the `SluggableListener` abstract class.

Here is an example of a custom service. We try to find a similar slug only on entities having the same parent than the sluggable's one :

```php
// SectionTranslationSluggableListener.php

<?php

namespace Flexy\SystemBundle\Lib;

use Flexy\DoctrineBehaviorsBundle\ORM\Sluggable\SluggableListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

/**
 * Class SectionTranslationSluggableListener
 */
class SectionTranslationSluggableListener extends SluggableListener
{

    /**
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
                ->from('Flexy\SystemBundle\Entity\SectionTranslation', 's')
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


### Uploadable ###

The uploadable behavior simplifies the way you handle file upload in Symfony2.
A trait is used to configure the uploadable fields and you only have to add 2 properties :

- A property that will contain the filename and will be persisted
- A non-persisted property that will contain the submitted file as an `\Symfony\Component\HttpFoundation\File\UploadedFile` entity

#### The config ####

You can optionally define what is your upload root folder by adding these lines to the `config.yml` file :

```yaml
flexy_doctrine_behaviors:
    uploadable:
        upload_root_dir: ../web/uploads
```

The `../web/uploads` path is the default value. If you wish to use the default path, you don't have to add anything to `config.yml`.

You will be able to specify a different subfolder of `upload_root_dir` for each uploadable field in your entity, we'll see how later on.

#### The entities ####

First, you will have to add a non-persisted property and add a `use` statement to include the Uploadable trait.

This trait contains the `getUploadableFields` abstract method that you will need to define in your entity.
This method returns a `key => value` array of the list of uploadable fields (key) with their respective upload directory (value).

You can add as many uploadable fields as you wish. In this example, we'll add two uploadable fields, as follow :

```php
<?php

namespace Flexy\SystemBundle\Entity;

use Symfony\Component\HttpFoundation\File\UploadedFile;

use Flexy\DoctrineBehaviorsBundle\Model as FlexyORMBehaviors;

/**
 * Section
 */
class Section
{
    use FlexyORMBehaviors\Uploadable\Uploadable;
    
    /**
     * @var integer
     */
    private $id;
    
    /**
     * @var UploadedFile
     */
    private $image;

    /**
     * @var UploadedFile
     */
    private $otherImage;

    /**
     * Get the list of uploabable fields and their respective upload directory in a key => value array format.
     *
     * @return array
     */
    public function getUploadableFields()
    {
        return [
            'image' => 'images',
            'otherImage' => 'autres_images'
        ];
    }
    
    /**
     * @param UploadedFile $image
     */
    public function setImage($image)
    {
        $this->setUploadedFile($image, 'image');
    }

    /**
     * @return UploadedFile
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param UploadedFile $otherImage
     */
    public function setOtherImage($otherImage)
    {
        $this->setUploadedFile($otherImage, 'otherImage');
    }
    
    /**
     * @return UploadedFile
     */
    public function getOtherImage()
    {
        return $this->otherImage;
    }

    [...]
}
```

**Note**: The `UploadedFile` properties setters will have to call the trait method `setUploadedFile` with 2 arguments, the `UploadedFile` instance and the name of the field.
This method will handle the file naming and the file deleting in case of a file replacement.

Next, you'll have to add a persisted field for each uploadable field to your entity's schema.
The name of each persisted property will be the name of the non-persisted field suffixed by "Path".
In this example, for `$image` we'll have `$imagePath` and for `$otherImage`, we'll have `$otherImagePath` :

```yaml
# Section.orm.yml

Flexy\SystemBundle\Entity\Section:
  type: entity
  fields:
    id:
      type: integer
      id: true
      generator:
        strategy: AUTO
    imagePath:
      type: string
      length: 255
      nullable: true
    otherImagePath:
      type: string
      length: 255
      nullable: true
```

Generate the getters and the setters :

```php
// Section.php
  
    [...]

    /**
     * @param string $imagePath
     */
    public function setImagePath($imagePath)
    {
        $this->imagePath = $imagePath;
    }

    /**
     * @return string
     */
    public function getImagePath()
    {
        return $this->imagePath;
    }

    /**
     * @param string $otherImagePath
     */
    public function setOtherImagePath($otherImagePath)
    {
        $this->otherImagePath = $otherImagePath;
    }

    /**
     * @return string
     */
    public function getOtherImagePath()
    {
        return $this->otherImagePath;
    }
    
    [...]

```

Other methods can be overloaded to configure the behavior :

- `getNamingStrategy` : Determines the naming strategy to use when renaming files with the alphanumeric naming strategy. Available choices are `alphanumeric`, `random` and `none`. See the phpdoc for a detailed description. Default is `alphanumeric`.
- `getAlphanumericDelimiter` : The delemiter when using the alphanumeric naming strategy. Default is `-`.
- `getIsUnique` : Determines whether the filename should be unique or not. Default is `true`. If `true`, the trait will generate a unique filename by appending "-1", "-2" and so on to the filename. If set to `false` and the uploaded file name already exists on the disk, it will be overwrited.

#### The form ####

Simply add a new `file` field to your form type and you're done :

```php
<?php

namespace Flexy\SystemBundle\Form\Backend;

/**
 * Section Type
 */
class SectionType extends AbstractType
{
    /**
     * Build Form
     *
     * @param FormBuilderInterface $builder The Builder
     * @param array                $options Array of options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('image', 'file')
            ->add('otherImage', 'file')
        ;
    }

    [...]
}
```

#### Controller ####

The upload process is handled by this listener.

When an entity is deleted or when a file is replaced, the files get automatically deleted from the server.


### Timestampable ###

The timestampable behavior is the easiest one to use, it simply requires to include a Trait in your entity and you're done.
The updatedAt and createdAt properties will automatically be added to your entity.

Only add the Timestampable trait to your entity :

```php
<?php

namespace Flexy\SystemBundle\Entity;

use Flexy\DoctrineBehaviorsBundle\Model as FlexyORMBehaviors;

/**
 * Section
 */
class Section
{
    use FlexyORMBehaviors\Timestampable\Timestampable;

    /**
     * @var integer
     */
    private $id;
    
    [...]
}
```


### Blameable ###

The blameable behavior lets you track which User created, updated or deleted an entity.
You can configure a User entity to link with the blameable entities, which means that these entities will have a many-to-one relation with the User entity.
If you don't specify a User entity, the name of the current logged User will be used instead and will be saved as string.

To activate the blameable behavior, simply use the Trait in the entity you want to behave as blameable :

```php
<?php

namespace Flexy\SystemBundle\Entity;

use Flexy\DoctrineBehaviorsBundle\Model as FlexyORMBehaviors;

/**
 * Section
 */
class Section extends BaseEntity
{
    use FlexyORMBehaviors\Blameable\Blameable;

    /**
     * @var integer
     */
    private $id;
    
    [...]
}
```

If you want to create a Many-to-One relation between your User entity and your blameable entities, you can configure the listener to manage automatically the association by setting the `user_entity` parameter to a fully qualified namespace :

```yaml
# config.yml

flexy_doctrine_behaviors:
    blameable:
        user_entity: Flexy\SystemBundle\Entity\User
```


### SoftDeletable ###

SoftDeletable let's you soft-delete an entity, which means that the entity won't be deleted but a deletedAt property will be set with the current timestamp when the entity gets deleted.

To make an entity behave as soft-deletable, simply use the SoftDeletable trait as follow :

```php
<?php

namespace Flexy\SystemBundle\Entity;

use Flexy\DoctrineBehaviorsBundle\Model as FlexyORMBehaviors;

/**
 * Section
 */
class Section extends BaseEntity
{
    use FlexyORMBehaviors\SoftDeletable\SoftDeletable;

    /**
     * @var integer
     */
    private $id;
    
    [...]
}
```

Here are some examples of use in a controller :

``` php
<?php

    $section = new Section();
    $em->persist($section);
    $em->flush();

    // Get id
    $id = $em->getId();

    // Now remove it
    $em->remove($section);

    // Hey, i'm still here:
    $section = $em->getRepository('FlexySystemBundle:Section')->findOneById($id);

    // But i'm "deleted"
    $section->isDeleted(); // === true
```

``` php
<?php

    $section = new Section();
    $em->persist($section);
    $em->flush();
    
    // I'll delete you tomorow
    $section->setDeletedAt((new \DateTime())->modify('+1 day'));

    // Ok, I'm here
    $section->isDeleted(); // === false
    
    /*
     *  24 hours later...
     */
     
    // Ok I'm deleted
    $section->isDeleted(); // === true
```
