<?php

namespace Unifik\DoctrineBehaviorsBundle\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

use Unifik\DoctrineBehaviorsBundle\Model\Taggable\Taggable;
use Unifik\DoctrineBehaviorsBundle\ORM\Taggable\TagManager;

class DenormalizedEntityTransformer implements DataTransformerInterface
{
    /**
     * @var TagManager
     */
    protected $tagManager;

    /**
     * @var Taggable
     */
    protected $entity;

    /**
     * Constructor
     *
     * @param TagManager $tagManager
     */
    public function __construct(TagManager $tagManager, $entity = null)
    {
        $this->tagManager = $tagManager;
        $this->entity = $entity;
    }

    /**
     * Get Entity
     *
     * @return Taggable
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Set Entity
     *
     * @param Taggable $entity
     * @return DenormalizedEntityTransformer
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }

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
     * @return array
     */
    public function reverseTransform($array)
    {
        $tags = new ArrayCollection($array);

        $this->entity->setTags($tags);
        $this->tagManager->saveTagging($this->entity);

        return $array;
    }
}