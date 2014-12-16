<?php

namespace Unifik\DoctrineBehaviorsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * This form should be extended to add the Metadata fields
 */
abstract class MetadatableType extends AbstractType
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
            ->add('metaTitle', 'text', ['required' => false, 'label' => 'Meta Title'])
            ->add('metaDescription', 'text', ['required' => false, 'label' => 'Meta Description'])
            ->add('metaKeywords', 'text', ['required' => false, 'label' => 'Meta Keywords']);
    }
}
