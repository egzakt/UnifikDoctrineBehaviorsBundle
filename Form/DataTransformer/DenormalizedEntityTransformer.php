<?php

namespace Unifik\DoctrineBehaviorsBundle\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

class DenormalizedEntityTransformer implements DataTransformerInterface
{
    /**
     * {@inheritDoc}
     *
     * @param ArrayCollection $array array collection of model objects
     *
     * @return array Array of model objects
     *
     * @throws UnexpectedTypeException if the given value is not an array
     */
    public function transform($value)
    {
        $value = $value instanceof \Traversable ? iterator_to_array($value) : $value;

        return $value;
    }

    /**
     * Do nothing reverse transform
     *
     * @param array $array
     *
     * @return ArrayCollection
     */
    public function reverseTransform($array)
    {
        return new ArrayCollection($array);
    }
}