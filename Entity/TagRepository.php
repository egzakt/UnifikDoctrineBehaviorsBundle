<?php

namespace Unifik\DoctrineBehaviorsBundle\Entity;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;

/**
 * TagRepository
 */
class TagRepository extends EntityRepository
{
    /**
     * The field that's considered the "lookup" for tags
     *
     * @var string
     */
    protected $tagQueryField = 'name';

    /**
     * @var string
     */
    protected $locale;

    /**
     * Get Locale
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set Locale
     *
     * @param string $locale
     * @return TagRepository
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * For a specific taggable type, this returns an array where they key
     * is the tag and the value is the number of times that tag is used
     *
     * @param string $taggableType The taggable type / resource type
     * @param null|integer $limit The max results to return
     * @return array
     */
    public function getTagsWithCountArray($taggableType, $limit = null)
    {
        $qb = $this->getTagsWithCountArrayQueryBuilder($taggableType);

        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }

        $tags = $qb->getQuery()
            ->getResult(AbstractQuery::HYDRATE_SCALAR)
        ;

        $arr = array();
        foreach ($tags as $tag) {
            $count = $tag['tag_count'];

            // don't include orphaned tags
            if ($count > 0) {
                $tagName = $tag[$this->tagQueryField];
                $arr[$tagName] = $count;
            }
        }

        return $arr;
    }

    /**
     * Returns an array of ids (e.g. Post ids) for a given taggable
     * type that have the given tag
     *
     * @param string $taggableType The type of object we're looking for
     * @param string $tag The actual tag we're looking for
     * @return array
     */
    public function getResourceIdsForTag($taggableType, $tag)
    {
        $queryBuilder = $this->getTagsQueryBuilder($taggableType)
            ->andWhere('tag.'.$this->tagQueryField.' = :tag')
            ->setParameter('tag', $tag)
            ->select('taggings.resourceId')
        ;

        if ($this->getLocale()) {
            $queryBuilder->andWhere('tag.locale = :locale')
                ->setParameter('locale', $this->getLocale());
        }

        $results = $queryBuilder->getQuery()->execute(array(), AbstractQuery::HYDRATE_SCALAR);

        $ids = array();
        foreach ($results as $result) {
            $ids[] = $result['resourceId'];
        }

        return $ids;
    }

    /**
     * Returns a query builder built to return tag counts for a given type
     *
     * @see getTagsWithCountArray
     * @param $taggableType
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getTagsWithCountArrayQueryBuilder($taggableType)
    {
        $qb = $this->getTagsQueryBuilder($taggableType)
            ->innerjoin('tag.taggings', 'taggings')
            ->groupBy('taggings.tag')
            ->select('tag.'.$this->tagQueryField.', COUNT(taggings.tag) as tag_count')
            ->orderBy('tag_count', 'DESC')
        ;

        return $qb;
    }

    /**
     * Returns a query builder returning tags for a given type
     *
     * @param string $taggableType
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getTagsQueryBuilder($taggableType = null)
    {
        $queryBuilder = $this->createQueryBuilder('tag')
            ->orderBy('tag.' . $this->tagQueryField);

        if ($this->getLocale()) {
            $queryBuilder
                ->where('tag.locale = :locale')
                ->setParameter('locale', $this->getLocale());
        }

        if ($taggableType) {
            $queryBuilder
                ->andWhere('tag.resourceType = :resourceType')
                ->setParameter('resourceType', $taggableType);
        } else {
            $queryBuilder
                ->andWhere('tag.resourceType IS NULL');
        }

        return $queryBuilder;
    }
}
