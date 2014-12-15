<?php

namespace Unifik\DoctrineBehaviorsBundle\Model\Metadatable;

/**
 * Metadatable trait.
 *
 * Should be used inside an entity where you want to definde specific meta data.
 */
trait Metadatable
{
    /**
     * @var string $metaTitle
     */
    protected $metaTitle;

    /**
     * @var string $metaDescription
     */
    protected $metaDescription;

    /**
     * @var string $metaKeywords
     */
    protected $metaKeywords;

    /**
     * Get Meta Title
     *
     * @return string
     */
    public function getMetaTitle()
    {
        return $this->metaTitle;
    }

    /**
     * Set Meta Title
     *
     * @param string $metaTitle
     * @return Metadatable
     */
    public function setMetaTitle($metaTitle)
    {
        $this->metaTitle = $metaTitle;

        return $this;
    }

    /**
     * Get Meta Description
     *
     * @return string
     */
    public function getMetaDescription()
    {
        return $this->metaDescription;
    }

    /**
     * Set Meta Description
     *
     * @param string $metaDescription
     *
     * @return Metadatable
     */
    public function setMetaDescription($metaDescription)
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }

    /**
     * Get Meta Keywords
     *
     * @return string
     */
    public function getMetaKeywords()
    {
        return $this->metaKeywords;
    }

    /**
     * Set Meta Keywords
     *
     * @param string $metaKeywords
     * @return Metadatable
     */
    public function setMetaKeywords($metaKeywords)
    {
        $this->metaKeywords = $metaKeywords;

        return $this;
    }
}