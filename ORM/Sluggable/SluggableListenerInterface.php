<?php

namespace Egzakt\DoctrineBehaviorsBundle\ORM\Sluggable;

/**
 * Class SluggableListenerInterface
 * @package Egzakt\DoctrineBehaviorsBundle\ORM\Sluggable
 */
interface SluggableListenerInterface {

    /**
     * Get Sluggable Fields
     *
     * Returns the list of sluggable fields
     *
     * @return array
     */
    public function getSluggableFields();

    /**
     * Get Slug Delemiter
     *
     * Returns the slug delemiter
     *
     * @return string
     */
    public function getSlugDelimiter();

}