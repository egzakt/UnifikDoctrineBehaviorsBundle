DoctrineBehaviorsBundle
=======================

This bundle is a bridge between KnpLabs/DoctrineBehaviors and the Egzakt Standard Distribution.

For now, only two behaviors have been overrided by this bundle :

- [Translatable](#translatable)
- [Sluggable](#sluggable)

## How to use

### Translatable ###

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

use Doctrine\ORM\Mapping as ORM;
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

You're done! The bidirectional relation is automatically registered with Doctrine Event Listeners with the following names :

```php
// $translations property
$textTranslations = $text->getTranslations();

// $translatable property
$text = $textTranslation->getTranslatable();
```
$translatable->translations and $translation->translatable.

Translated entities are loaded with the current locale on a postLoad Doctrine Event. If you want to load an entity in a specific locale, you can use the "setCurrentLocale" method :

```php
$text->setCurrentLocale('fr');
$name = $text->getName();
```

### Sluggable ###

Description here.
