<?php

namespace Unifik\DoctrineBehaviorsBundle\Model\Repository;

use Doctrine\ORM\QueryBuilder;
use Unifik\DoctrineBehaviorsBundle\Model\Tree\NodeInterface;

interface NodeRepositoryInterface
{
    /**
     * Add a set of criteria
     *
     * @param QueryBuilder $queryBuilder
     * @return QueryBuilder
     */
    public function getCriteria(QueryBuilder $queryBuilder);

    /**
     * Constructs a query builder to get all root nodes
     *
     * @param string $alias
     * @return QueryBuilder
     */
    public function getRootNodesQB($alias);

    /**
     * Returns all root nodes
     *
     * @api
     * @param string $alias
     * @return array
     */
    public function findRootNodes($alias);

    /**
     * @param $nodeId
     * @param $alias
     * @return QueryBuilder
     */
    public function getNodeByIdQB($nodeId, $alias);
    
    /**
     * @param string $nodeId
     * @return NodeInterface
     */
    public function findNodeById($nodeId);

    /**
     * @param $nodeId
     * @param int $depth optional
     * @param string $alias optional
     * @return QueryBuilder
     */
    public function getNodeChildrenQB($nodeId, $depth, $alias);
    
    /**
     * @param $nodeId
     * @param int $depth optional
     * @return array
     */
    public function findNodeChildren($nodeId, $depth);

    /**
     * Returns QueryBuilder for a tree starting from $path
     *
     * @param string|array|null $nodeIds
     * @param string $alias
     * @return QueryBuilder
     */
    public function getTreeFromQB($nodeIds = null, $alias = 't');

    /**
     * Returns a tree starting from $path
     *
     * @param string|array|null $nodeIds
     * @param string $alias
     * @return array
     */
    public function findTreeFrom($nodeIds = null, $alias = 't');

    /**
     * Builds a hierarchical tree from a flat collection of NodeInterface elements
     *
     * @param array $nodes
     * @return array
     */
    static function buildTree(array $nodes);

    /**
     * @param array $nodes
     * @return array
     */
    static function toFlatArray(array $nodes);
}