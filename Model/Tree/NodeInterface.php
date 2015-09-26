<?php

namespace Unifik\DoctrineBehaviorsBundle\Model\Tree;

use Doctrine\Common\Collections\ArrayCollection;

interface NodeInterface
{
    /**
     * Set children
     *
     * @param array $children
     * @return $this
     */
    public function setChildren(array $children);

    /**
     * Add children
     *
     * @param $children
     * @return $this
     */
    public function addChildren(NodeInterface $children);

    /**
     * Has children
     *
     * @return Boolean
     */
    public function hasChildren();

    /**
     * Get children
     *
     * @return ArrayCollection
     */
    public function getChildren();

    /**
     * @param NodeInterface $node
     * @return $this
     */
    public function removeChildren(NodeInterface $node);

    /**
     * Set parent
     *
     * @param $parent
     */
    public function setParent($parent);

    /**
     * Get parent
     *
     * @return mixed
     */
    public function getParent();

    /**
     * Get parents
     *
     * @return array
     */
    public function getParents();

    /**
     * Return the node Id
     *
     * @return string the field that will represent the node in the path
     **/
    public function getNodeId();

    /**
     * Reset the node Id. Usually used to force update
     *
     * @return $this
     */
    public function resetNodeId();

    /**
     * @return NodeInterface the parent node
     **/
    public function getParentNode();

    /**
     * @return string
     */
    public function getParentNodeId();

    /**
     * @param NodeInterface $node
     */
    public function setParentNode(NodeInterface $node);

    /**
     * @return ArrayCollection the children collection
     **/
    public function getChildNodes();


    /**
     * @param array $nodes
     * @return $this
     */
    public function setChildNodes(array $nodes);

    /**
     * @param NodeInterface $node
     * @return $this
     */
    public function addChildNode(NodeInterface $node);

    /**
     * @param $node
     * @return $this
     */
    public function removeChildNode($node);

    /**
     * @return NodeInterface
     **/
    public function getRootNodeId();

    /**
     * @return bool
     */
    public function isRootNode();

    /**
     * Tells if this node is a child of another node
     * @param NodeInterface $node the node to compare with
     *
     * @return boolean true if this node is a direct child of $node
     **/
    public function isChildNodeOf(NodeInterface $node);

    /**
     * @return integer the level of this node, eg: the depth compared to root node
     **/
    public function getNodeLevel();
}