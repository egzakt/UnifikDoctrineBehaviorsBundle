<?php

namespace Unifik\DoctrineBehaviorsBundle\Form;

use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityChoiceList;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Unifik\DoctrineBehaviorsBundle\Form\ChoiceList\TaggableEntityLoader;
use Unifik\DoctrineBehaviorsBundle\Form\DataTransformer\DenormalizedEntityTransformer;
use Unifik\DoctrineBehaviorsBundle\Form\DataTransformer\TaggableEntityTransformer;
use Unifik\DoctrineBehaviorsBundle\ORM\Taggable\TagManager;

/**
 * This form type should be used for Taggable entities
 */
class TaggableType extends AbstractType
{
    /**
     * @var TagManager
     */
    protected $tagManager;

    /**
     * @var DenormalizedEntityTransformer
     */
    protected $denormalizedEntityTransformer;

    /**
     * Constructor
     *
     * @param TagManager $tagManager
     */
    public function __construct(TagManager $tagManager)
    {
        $this->tagManager = $tagManager;
        $this->denormalizedEntityTransformer = new DenormalizedEntityTransformer($this->tagManager);
    }

    /**
     * Returns the name of the parent type.
     *
     * @return string|null The name of the parent type if any, null otherwise.
     */
    public function getParent()
    {
        return 'choice';
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'taggable';
    }

    /**
     * Add options required by this form type
     *
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $that = $this;

        $choiceList = function (Options $options) use ($that) {
            return $that->getChoiceList($options);
        };

        $resolver->setRequired(['resource_type', 'locale']);

        $resolver->setDefaults(array(
            'use_global_tags' => true,
            'multiple' => true,
            'expanded' => false,
            'choice_list' => $choiceList,
            'class' => 'Unifik\DoctrineBehaviorsBundle\Entity\Tag',
            'mapped' => true
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer($this->denormalizedEntityTransformer);

        $builder->addEventListener(FormEvents::POST_SET_DATA, function ($event) {
            $form = $event->getForm();
            $entity = $form->getParent()->getData();

            $this->denormalizedEntityTransformer->setEntity($entity);
        });
    }

    /**
     * Get the Taggable Entity Loader
     *
     * @param $options
     * @return TaggableEntityLoader
     */
    protected function getEntityLoader($options)
    {
        return new TaggableEntityLoader(
            $this->tagManager->getEm(),
            $options
        );
    }

    /**
     * Get the Choice List
     *
     * @param $options
     * @return EntityChoiceList
     */
    protected function getChoiceList($options)
    {
        $loader = $this->getEntityLoader($options);

        return new EntityChoiceList(
            $this->tagManager->getEm(),
            $options['class'],
            null,
            $loader
        );
    }
}
