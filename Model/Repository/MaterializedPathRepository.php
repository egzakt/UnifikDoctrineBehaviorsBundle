<?php

namespace Unifik\DoctrineBehaviorsBundle\Model\Repository;

use Doctrine\ORM\QueryBuilder,
    Doctrine\ORM\EntityRepository;

use Unifik\DoctrineBehaviorsBundle\Model\Tree\NodeInterface,
    Unifik\DoctrineBehaviorsBundle\ORM\Tree\Strategy\MaterializedPath;

/**
 * Class MaterializedPathRepository
 * @package Unifik\DoctrineBehaviorsBundle\Model\Repository
 */
trait MaterializedPathRepository
{
    /**
     * Add a set of criteria
     *
     * @param QueryBuilder $queryBuilder
     * @return QueryBuilder
     */
    public function getCriteria(QueryBuilder $queryBuilder)
    {
        return $queryBuilder;
    }

    /**
     * Constructs a query builder to get all root nodes
     *
     * @param string $alias
     * @return QueryBuilder
     */
    public function getRootNodesQB($alias)
    {
        /** @var EntityRepository|MaterializedPathRepository $this */
        $queryBuilder = $this->getCriteria($this->createQueryBuilder($alias));
        $queryBuilder
            ->andWhere('LENGTH('.$alias.'.materializedPath) = :hash_length')
            ->setParameter('hash_length', MaterializedPath::HASH_LENGTH)
        ;

        return $queryBuilder;
    }

    /**
     * Returns all root nodes
     *
     * @api
     *
     * @param string $alias
     *
     * @return array
     */
    public function findRootNodes($alias = 't')
    {
        return self::toFlatArray(
            $this
                ->getRootNodesQB($alias)
                ->getQuery()
                ->getResult()
        );
    }

    /**
     * @param $nodeId
     * @param string $alias
     * @return QueryBuilder
     */
    public function getNodeByIdQB($nodeId, $alias = 'e')
    {
        /** @var EntityRepository|MaterializedPathRepository $this */
        $queryBuilder = $this->getCriteria($this->createQueryBuilder($alias));
        return $queryBuilder
            ->andWhere($alias.'.materializedPath = :nodeId')
            ->setParameter('nodeId', $nodeId)
            ->addOrderBy($alias.'.materializedPath', 'ASC')
            ->setMaxResults(1)
        ;
    }
    
    /**
     * @param string $nodeId
     * @return NodeInterface
     */
    public function findNodeById($nodeId)
    {
        return $this->getNodeByIdQB($nodeId)->getQuery()->getSingleResult();
    }

    /**
     * @param string $nodeId
     * @param int $depth
     * @param string $alias
     * @return QueryBuilder
     */
    public function getNodeChildrenQB($nodeId, $depth = null, $alias = 'e')
    {
        /** @var EntityRepository|MaterializedPathRepository $this */
        $queryBuilder = $this->getCriteria($this->createQueryBuilder($alias));
        $queryBuilder
            ->andWhere($alias.'.materializedPath LIKE :nodeId')
            ->andWhere('LENGTH('.$alias.'.materializedPath) > :nodeIdLength')
            ->setParameter('nodeId', $nodeId.'%')
        ;

        if (is_int($depth)) {
            $nodeIdLength = count($nodeId);
            $childNodeIdLength = $nodeIdLength + ($depth * MaterializedPath::HASH_LENGTH);
            $queryBuilder
                ->andWhere('LENGTH('.$alias.'.materializedPath) <= :childNodeIdLength')
                ->setParameter('childNodeIdLength', $childNodeIdLength)
                ->setParameter('nodeIdLength', $nodeIdLength)
            ;
        }

        return $queryBuilder;
    }
    
    /**
     * @param string $nodeId
     * @param int $depth optional
     * @return array
     */
    public function findNodeChildren($nodeId, $depth = null)
    {
        $results =$this->getNodeChildrenQB($nodeId, $depth)->getQuery()->getResult();

        if (is_int($depth) && $depth > 1) {
            return self::buildTree($results);
        }

        return $results;
    }

    /**
     * Returns QueryBuilder for a tree starting from MatPath nodes id
     *
     * @param string|array|null $nodeIds
     * @param string $alias
     * @return QueryBuilder
     */
    public function getTreeFromQB($nodeIds = null, $alias = 't')
    {
        /** @var EntityRepository|MaterializedPathRepository $this */
        $queryBuilder = $this->getCriteria($this->createQueryBuilder($alias));

        if ($nodeIds) {

            if (!is_array($nodeIds)) {
                $nodeIds = [$nodeIds];
            }

            $condition = '';
            $lastIndex = count($nodeIds) - 1;

            foreach ($nodeIds as $index => $nodeId) {
                $condition .= $alias.'.materializedPath LIKE :nodeId_'.$index
                    . (($index != $lastIndex) ? ' OR ' : '');
                $queryBuilder->setParameter('nodeId_'.$index, $nodeIds[$index].'%');
            }

            $queryBuilder->andWhere($condition);
        }

        return $queryBuilder;
    }

    /**
     * Returns a tree starting from MatPath nodes id
     *
     * @param string|array|null $nodeIds
     * @param string $alias
     * @return array
     */
    public function findTreeFrom($nodeIds = null, $alias = 't')
    {
        return $this->getTreeFromQB($nodeIds, $alias)->getQuery()->getResult();
    }

    /**
     * Builds a hierarchical tree from a flat collection of NodeInterface elements
     *
     * @param array $nodes
     * @return array
     */
    static function buildTree(array $nodes)
    {
        $nodes = self::toFlatArray($nodes);

        /** @var NodeInterface $node */
        foreach ($nodes as $node) {
            $parentNodeId = $node->getParentNodeId();
            if (isset($nodes[$parentNodeId])) {
                $nodes[$parentNodeId]->addChildNode($node);
            }
        }

        return $nodes;
    }

    /**
     * @param array $nodes
     * @return array
     */
    static function toFlatArray(array $nodes)
    {
        $flatNodes = [];

        /** @var NodeInterface $node */
        foreach ($nodes as $node) {
            $node->getChildNodes()->clear();
            $flatNodes[$node->getNodeId()] = $node;
        }

        unset($nodes);

        return $flatNodes;
    }

    /**
     * @param array $nodes
     * @return array
     */
    static function getNodeIds(array $nodes)
    {
        $nodeIds = [];
        foreach ($nodes as $node) {
            /** @var NodeInterface $node */
            $nodeIds[] = $node->getNodeId();
        }

        return $nodeIds;
    }
}