<?php

namespace Unifik\DoctrineBehaviorsBundle\Model\Tree;

use Doctrine\Common\Collections\ArrayCollection;

use Unifik\DoctrineBehaviorsBundle\ORM\Tree\Strategy\MaterializedPath as Strategy;

trait MaterializedPath
{
    /**
     * @var ArrayCollection
     */
    protected $children;

    /**
     * @var NodeInterface
     */
    protected $parent;

    /**
     * @var string
     */
    private $materializedPath;

    /**
     * Set children
     *
     * @param array $children
     * @return $this
     */
    public function setChildren(array $children = null)
    {
        if (null === $this->children) {
            $this->children = new ArrayCollection();
        }

        foreach ($children as $child) {
            $this->children->add($child);
        }

        return $this;
    }

    /**
     * Add children
     *
     * @param $children
     * @return $this
     */
    public function addChildren(NodeInterface $children)
    {
        if (null === $this->children) {
            $this->children = new ArrayCollection();
        }

        $this->children->add($children);

        return $this;
    }

    /**
     * Has children
     *
     * @return Boolean
     */
    public function hasChildren()
    {
        return (false == $this->children->isEmpty());
    }

    /**
     * Get children
     *
     * @return ArrayCollection
     */
    public function getChildren()
    {
        if (null === $this->children) {
            $this->children = new ArrayCollection();
        }

        return $this->children;
    }

    /**
     * @param NodeInterface $node
     * @return $this
     */
    public function removeChildren(NodeInterface $node)
    {
        $this->children->removeElement($node);
        return $this;
    }

    /**
     * Set parent
     *
     * @param $parent
     */
    public function setParent($parent = null)
    {
        $this->parent = $parent;
    }

    /**
     * Get parent
     *
     * @return mixed
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Get parents
     *
     * @return array
     */
    public function getParents()
    {
        $parents = [];
        $tempParents = [];
        $parent = $this->getParent();
        $level = 1;

        while ($parent && $parent->getId()) {
            $tempParents[] = $parent;
            $parent = $parent->getParent();
        }

        $tempParents = array_reverse($tempParents);
        foreach ($tempParents as $parent) {
            $parents[$level] = $parent;
            $level++;
        }

        return $parents;
    }

    /**
     * @return string
     */
    public function getNodeId()
    {
        return $this->materializedPath;
    }

    /**
     * @return $this
     */
    public function resetNodeId()
    {
        $this->materializedPath = '__empty__';

        foreach ($this->getChildren() as $children) {
            /** @var MaterializedPath $children */
            $children->resetNodeId();
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getRootNodeId()
    {
        return substr($this->getNodeId(), 0, Strategy::HASH_LENGTH);
    }

    /**
     * @return string
     */
    public function getParentNodeId()
    {
        return substr($this->getNodeId(), 0, -(Strategy::HASH_LENGTH));
    }

    /**
     * @return NodeInterface the parent node
     **/
    public function getParentNode()
    {
        return $this->parent;
    }

    /**
     * @param NodeInterface $node
     */
    public function setParentNode(NodeInterface $node = null)
    {
        $this->parent = $node;
    }

    /**
     * @return ArrayCollection
     */
    public function getChildNodes()
    {
        if (!($this->children instanceof ArrayCollection)) {
            $this->children = new ArrayCollection();
        }

        return $this->children;
    }

    /**
     * @param array $nodes
     * @return $this
     */
    public function setChildNodes(array $nodes = null)
    {
        if (!($this->children instanceof ArrayCollection)) {
            $this->children = new ArrayCollection();
        }

        foreach ($nodes as $node) {
            if ($node instanceof NodeInterface) {
                $this->children->set($node->getNodeId(), $node);
            }
        }

        return $this;
    }

    /**
     * @param NodeInterface $node
     * @return $this
     */
    public function addChildNode(NodeInterface $node)
    {
        if (!($this->children instanceof ArrayCollection)) {
            $this->children = new ArrayCollection();
        }

        $this->children->set($node->getNodeId(), $node);

        return $this;
    }

    /**
     * @param $node
     * @return $this
     */
    public function removeChildNode($node)
    {
        if ($node instanceof NodeInterface && $this->children->contains($node)) {
            $this->children->removeElement($node);
        } elseif (is_string($node) && $this->children->containsKey($node)) {
            $this->children->remove($node);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isRootNode()
    {
        return (strlen($this->getNodeId()) == Strategy::HASH_LENGTH);
    }

    /**
     * @param NodeInterface $node
     * @return bool
     */
    public function isChildNodeOf(NodeInterface $node)
    {
        return (0 === strpos($node->getNodeId(), $this->getNodeId())
            && $node->getNodeId() != $this->getNodeId());
    }

    /**
     * @return int
     */
    public function getNodeLevel()
    {
        return (int)(strlen($this->getNodeId()) / Strategy::HASH_LENGTH);
    }

    /**
     * initNode for insert/update
     */
    public function initNode()
    {
        if (empty($this->materializedPath)) {
            $this->resetNodeId();
        }
    }

    /**
     * @return string
     */
    public function getReadablePath()
    {
        $nodes = str_split($this->materializedPath, Strategy::HASH_LENGTH);

        foreach ($nodes as $key => $node) {
            $nodes[$key] = gmp_strval(gmp_init($node, 62), 10);
        }

        return '/'.implode('/', $nodes);
    }
}