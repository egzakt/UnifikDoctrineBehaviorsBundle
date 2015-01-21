<?php

namespace Unifik\DoctrineBehaviorsBundle\ORM\Taggable;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr;
use Symfony\Bridge\Doctrine\RegistryInterface;

use Unifik\DoctrineBehaviorsBundle\Entity\Tag;
use Unifik\DoctrineBehaviorsBundle\Model\Taggable\Taggable;
use Unifik\DoctrineBehaviorsBundle\Entity\Tagging;

/**
 * This service is used to manage the tags
 */
class TagManager
{
    /**
     * @var RegistryInterface
     */
    protected $registry;

    /**
     * @var string
     */
    protected $locale;

    /**
     * Constructor
     *
     * @param RegistryInterface $registry
     * @param string $locale
     */
    public function __construct(RegistryInterface $registry, $locale = null)
    {
        $this->registry = $registry;
        $this->locale = $locale;
    }

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
     * @return TagManager
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Get Em
     *
     * @return EntityManager
     */
    public function getEm()
    {
        return $this->registry->getEntityManager();
    }

    /**
     * Adds a tag on the given taggable resource
     *
     * @param Tag       $tag        Tag object
     * @param Taggable  $resource   Taggable resource
     */
    public function addTag(Tag $tag, $resource)
    {
        $resource->getTags()->add($tag);
    }
    
    /**
     * Adds multiple tags on the given taggable resource
     *
     * @param Tag[]     $tags       Array of Tag objects
     * @param Taggable  $resource   Taggable resource
     */
    public function addTags(array $tags, $resource)
    {
        foreach ($tags as $tag) {
            if ($tag instanceof Tag) {
                if (!$resource->getTags()->contains($tag)) {
                    $this->addTag($tag, $resource);
                }
            }
        }
    }
    
    /**
     * Removes an existant tag on the given taggable resource
     *
     * @param Tag       $tag        Tag object
     * @param Taggable  $resource   Taggable resource
     * @return Boolean
     */
    public function removeTag(Tag $tag, $resource)
    {
        return $resource->getTags()->removeElement($tag);
    }
    
    /**
     * Replaces all current tags on the given taggable resource
     *
     * @param Tag[]     $tags       Array of Tag objects
     * @param Taggable  $resource   Taggable resource
     */
    public function replaceTags(array $tags, $resource)
    {
        $resource->getTags()->clear();
        $this->addTags($tags, $resource);
    }

    /**
     * Load a Tag by Id
     *
     * @param $id
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function loadTagById($id)
    {
        return $this->getEm()->createQueryBuilder()
            ->select('t')
            ->from('UnifikDoctrineBehaviorsBundle:Tag', 't')
            ->where('t.id = :id')
            ->setParameter('id', $id)
            ->getQuery()->getSingleResult();
    }
    
    /**
     * Loads or creates a tag from tag name
     *
     * @param array       $name          Tag name
     * @param string|null $resourceType The Resource Type
     * @return Tag
     */
    public function loadOrCreateTag($name, $resourceType = null)
    {
        $tags = $this->loadOrCreateTags(array($name), $resourceType);
        return $tags[0];
    }
    
    /**
     * Loads or creates multiples tags from a list of tag names
     *
     * @param array       $names        Array of tag names
     * @param string|null $resourceType The Resource Type
     * @return Tag[]
     */
    public function loadOrCreateTags(array $names, $resourceType = null)
    {
        if (empty($names)) {
            return [];
        }

        $names = array_unique($names);

        $builder = $this->getEm()->createQueryBuilder();
        $builder->select('t')
                ->from('UnifikDoctrineBehaviorsBundle:Tag', 't')
                ->where($builder->expr()->in('t.name', $names))
        ;

        if ($this->getLocale()) {
            $builder
                ->andWhere('t.locale = :locale')
                ->setParameter('locale', $this->getLocale());
        }

        $tags = $builder
            ->getQuery()
            ->getResult()
        ;

        $loadedNames = array();
        foreach ($tags as $tag) {
            $loadedNames[] = $tag->getName();
        }

        $missingNames = array_udiff($names, $loadedNames, 'strcasecmp');
        if (sizeof($missingNames)) {
            foreach ($missingNames as $name) {
                $tag = $this->createTag($name, $resourceType);
                $this->getEm()->persist($tag);
                $tags[] = $tag;
            }
            $this->getEm()->flush();
        }

        return $tags;
    }
    
    /**
     * Saves tags for the given taggable resource
     *
     * @param Taggable  $resource   Taggable resource
     */
    public function saveTagging($resource)
    {
        $oldTags = $this->getTagging($resource);
        $newTags = $resource->getTags();
        $tagsToAdd = $newTags;

        if ($oldTags !== null and is_array($oldTags) and !empty($oldTags)) {
            $tagsToRemove = array();
            foreach ($oldTags as $oldTag) {
                if ($newTags->exists(function ($index, $newTag) use ($oldTag) {
                    return $newTag->getName() == $oldTag->getName();
                })) {
                    $tagsToAdd->removeElement($oldTag);
                } else {
                    $tagsToRemove[] = $oldTag->getId();
                }
            }

            if (sizeof($tagsToRemove)) {
                $builder = $this->getEm()->createQueryBuilder();
                $builder
                    ->delete('UnifikDoctrineBehaviorsBundle:Tagging', 't')
                    ->where('t.tag_id')
                    ->where($builder->expr()->in('t.tag', $tagsToRemove))
                    ->andWhere('t.resourceType = :resourceType')
                    ->setParameter('resourceType', $resource->getResourceType())
                    ->andWhere('t.resourceId = :resourceId')
                    ->setParameter('resourceId', $resource->getId())
                    ->getQuery()->getResult();
                ;
            }
        }

        foreach ($tagsToAdd as $tag) {
            $this->getEm()->persist($tag);
            $this->getEm()->persist($this->createTagging($tag, $resource));
        }

        if (count($tagsToAdd)) {
            $this->getEm()->flush();
        }
    }
    
    /**
     * Loads all tags for the given taggable resource
     *
     * @param Taggable  $resource   Taggable resource
     */
    public function loadTagging($resource)
    {
        $tags = $this->getTagging($resource);
        $this->replaceTags($tags, $resource);
    }

    /**
     * Gets all tags for the given taggable resource
     *
     * @param Taggable  $resource   Taggable resource
     * @return array
     */
    protected function getTagging($resource)
    {
        return $this->getEm()
            ->createQueryBuilder()
            ->select('t')
            ->from('UnifikDoctrineBehaviorsBundle:Tag', 't')
            ->innerJoin('t.taggings', 't2', Expr\Join::WITH, 't2.resourceId = :id AND t2.resourceType = :type')
            ->setParameter('id', $resource->getId())
            ->setParameter('type', $resource->getResourceType())
            // ->orderBy('t.name', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
    
    /**
     * Deletes all tagging records for the given taggable resource
     *
     * @param Taggable  $resource   Taggable resource
     */
    public function deleteTagging($resource)
    {
        $taggingList = $this->getEm()->createQueryBuilder()
            ->select('t')
            ->from('UnifikDoctrineBehaviorsBundle:Tagging', 't')
            ->where('t.resourceType = :type')
            ->setParameter('type', $resource->getResourceType())
            ->andWhere('t.resourceId = :id')
            ->setParameter('id', $resource->getId())
            ->getQuery()
            ->getResult();

        foreach ($taggingList as $tagging) {
            $this->getEm()->remove($tagging);
        }
    }

    /**
     * Splits an string into an array of valid tag names
     *
     * @param string    $names      String of tag names
     * @param string    $separator  Tag name separator
     * @return array
     */
    public function splitTagNames($names, $separator=',')
    {
        $tags = explode($separator, $names);
        $tags = array_map('trim', $tags);
        $tags = array_filter($tags, function ($value) { return !empty($value); });

        return array_values($tags);
    }
    
    /**
     * Returns an array of tag names for the given Taggable resource.
     *
     * @param Taggable  $resource   Taggable resource
     * @return array
     */
    public function getTagNames($resource)
    {
        $names = array();
        if (sizeof($resource->getTags()) > 0) {
            foreach ($resource->getTags() as $tag) {
                $names[] = $tag->getName();
            }
        }

        return $names;
    }
    
    /**
     * Creates a new Tag object
     *
     * @param string      $name         Tag name
     * @param string|null $resourceType The Resource Type, null to use the Global Tags
     * @return Tag
     */
    protected function createTag($name, $resourceType = null)
    {
        $tag = new Tag();
        $tag->setName($name);
        $tag->setLocale($this->getLocale());
        $tag->setResourceType($resourceType);

        return $tag;
    }
    
    /**
     * Creates a new Tagging object
     *
     * @param Tag       $tag        Tag object
     * @param Taggable  $resource   Taggable resource object
     * @return Tagging
     */
    protected function createTagging(Tag $tag, $resource)
    {
        $tagging = new Tagging();
        $tagging->setTag($tag);
        $tagging->setResourceId($resource->getId());
        $tagging->setResourceType($resource->getResourceType());

        return $tagging;
    }
} 