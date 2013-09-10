<?php
/**
 * @author Lusitanian
 * Freely released with no restrictions, re-license however you'd like!
 */

namespace Egzakt\DoctrineBehaviorsBundle\Model\Sluggable;

use Knp\DoctrineBehaviors\Model\Sluggable\Sluggable as BaseSluggable;

/**
 * Sluggable trait.
 *
 * Should be used inside entities for which slugs should automatically be generated on creation for SEO/permalinks.
 */
trait Sluggable
{
    use BaseSluggable;

    /**
     * Returns whether or not the slug gets regenerated on update.
     *
     * @return bool
     */
    private function getRegenerateSlugOnUpdate()
    {
        return false;
    }

    /**
     * @param string $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

}
