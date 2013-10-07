<?php

namespace Flexy\DoctrineBehaviorsBundle\Model\Repository;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\ORMException;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Flexy\SystemBundle\Lib\Core;

/**
 * TranslatableEntityRepository Trait.
 *
 * Use this trait in repositories of translatable entities.
 */
trait TranslatableEntityRepository
{
    /**
     * @var string
     */
    protected $currentAppName;

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var boolean $returnQueryBuilder
     */
    protected $returnQueryBuilder;

    /**
     * Get Container
     *
     * @return ContainerInterface
     *
     * @throws \UnexpectedValueException
     */
    public function getContainer()
    {
        if (null === $this->container) {
            throw new \UnexpectedValueException(sprintf('Trying to access $container in «%s» but it\'s null. ' .
            'Did you forget to implement the ContainerAwareInterface?', __CLASS__));
        }

        return $this->container;
    }

    /**
     * Gets the System Core
     *
     * @return Core
     */
    protected function getSystemCore()
    {
        return $this->getContainer()->get('flexy_system.core');
    }

    /**
     * Set Current App Name
     *
     * @param string $currentAppName The App Name
     */
    public function setCurrentAppName($currentAppName)
    {
        $this->currentAppName = $currentAppName;
    }

    /**
     * Get Current App Name
     *
     * @return string
     */
    protected function getCurrentAppName()
    {
        if ($this->currentAppName) {
            return $this->currentAppName;
        }

        return $this->getSystemCore()->getCurrentAppName();
    }

    /**
     * Set locale
     *
     * @param string $locale
     *
     * @return void
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * Get Locale
     *
     * @return string
     */
    protected function getLocale()
    {
        if ($this->locale) {
            return $this->locale;
        }

        if ($locale = $this->getContainer()->get('request')->getLocale()) {
            $this->locale = $locale;

            return $this->locale;
        }

        return $this->getContainer()->getParameter('locale');
    }

    /**
     * Set ReturnQueryBuilder
     *
     * @param bool $returnQueryBuilder
     */
    public function setReturnQueryBuilder($returnQueryBuilder)
    {
        $this->returnQueryBuilder = $returnQueryBuilder;
    }

    /**
     * Get ReturnQueryBuilder
     *
     * @return bool
     */
    public function getReturnQueryBuilder()
    {
        return $this->returnQueryBuilder;
    }

    /**
     * Returns the Query Builder or the results depending on the repository parameters
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param bool                       $singleResult
     *
     * @return mixed
     */
    protected function processQuery($queryBuilder, $singleResult = false)
    {
        if ($this->returnQueryBuilder) {
            return $queryBuilder;
        }

        if ($singleResult) {
            return $queryBuilder->getQuery()->getSingleResult();
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Get Class Namespace For Dql
     *
     * Exemple: Converts Flexy\Backend\SectionBundle\Section to FlexyBackendSectionBundle:Section
     *
     * @param \Doctrine\ORM\Mapping\ClassMetadata $class
     *
     * @return string
     */
    private function getClassNamespaceForDql($class)
    {
        $namespace = $class->getName();
        $namespace = preg_replace('/(.*)\\\Entity\\\(.*)/', '$1:$2', $namespace);
        $namespace = preg_replace('/\\\/', '', $namespace);

        return $namespace;
    }

    /**
     * Generate Translation Dql
     *
     * @param array      $criteria The array of criterias
     * @param array|null $orderBy  Query ordering
     * @param int|null   $limit    Query limit
     * @param int|null   $offset   Query offset
     *
     * @return \Doctrine\ORM\Query
     */
    private function generateTranslationDql(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        if ($this->getCurrentAppName() == 'backend') {
            $join = 'LEFT JOIN o.translations ot';
        } else {
            $join = 'INNER JOIN o.translations ot';
            $criteria['locale'] = $this->getLocale();
            if ($this->_em->getClassMetadata($this->_entityName . 'Translation')->hasField('active') && !in_array('active', array_keys($criteria))) {
                $criteria['active'] = true;
            }
        }

        $dql = 'SELECT o, ot
                FROM ' . $this->getClassNamespaceForDql($this->_class) . ' o ' . $join . ' ';

        if ($criteria) {

            $dql .= 'WHERE ';

            foreach (array_keys($criteria) as $column) {
                if (!$this->_class->hasField($column) && $this->_em->getClassMetadata($this->_entityName . 'Translation')->hasField($column)) {
                    // Null values must be handled differently
                    if (is_null($criteria[$column])) {
                        $dql .= 'ot.' . $column . ' IS NULL AND ';
                        unset($criteria[$column]);
                    } else {
                        $dql .= 'ot.' . $column . ' = :' . $column . ' AND ';
                    }
                } else {
                    // Null values must be handled differently
                    if (is_null($criteria[$column])) {
                        $dql .= 'o.' . $column . ' IS NULL AND ';
                        unset($criteria[$column]);
                } else {
                        $dql .= 'o.' . $column . ' = :' . $column . ' AND ';
                    }
                }
            }

            $dql = substr($dql, 0, -4);
        }

        if ($orderBy) {

            $dql .= 'ORDER BY ';

            foreach ($orderBy as $key => $order) {
                if (!$this->_class->hasField(key($orderBy)) && $this->_em->getClassMetadata($this->_entityName . 'Translation')->hasField(key($orderBy))) {
                    $dql .= 'ot.' . key($orderBy) . ' ' . $order . ', ';
                } else {
                    $dql .= 'o.' . key($orderBy) . ' ' . $order . ', ';
                }
            }

            $dql = substr($dql, 0, -2) . ' ';
        }

        $query = $this->getEntityManager()->createQuery($dql);

        if ($limit) {
            $query->setMaxResults($limit);
        }

        if ($offset) {
            $query->setFirstResult($offset);
        }

        if ($criteria) {
            $query->setParameters($criteria);
        }

        return $query;
    }

    /**
     * Finds an entity by its primary key / identifier.
     *
     * Overriden to join the translations, if it exists
     *
     * @param int $id
     * @param int $lockMode
     * @param int $lockVersion
     *
     * @return object The entity.
     */
    public function find($id, $lockMode = LockMode::NONE, $lockVersion = null)
    {
        try {
            if ($this->_class->hasAssociation('translations') && $id) {
                $criteria = array('id' => $id);
                $query = $this->generateTranslationDql($criteria);

                return $query->getSingleResult();
            } else {
                return parent::find($id, $lockMode, $lockVersion);
            }
        } catch (NoResultException $e) {
            return null;
        }
    }

    /**
     * Finds all entities in the repository.
     *
     * Overrided to join the translations, if it exists, and add the where clauses
     * on the translation table if applicable
     *
     * @return array The entities.
     */
    public function findAll()
    {
        if ($this->_class->hasAssociation('translations')) {
            // Join on Translation
            $query = $this->generateTranslationDql(array());

            return $query->getResult();
        } else {
            // Default
            return $this->findBy(array());
        }
    }

    /**
     * Finds entities by a set of criteria.
     *
     * Overrided to join the translations, if it exists, and add the where clauses
     * on the translation table if applicable
     *
     * @param array      $criteria
     * @param array|null $orderBy
     * @param int|null   $limit
     * @param int|null   $offset
     *
     * @return array The objects.
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        if ($this->_class->hasAssociation('translations')) {
            // Join on Translation
            $query = $this->generateTranslationDql($criteria, $orderBy, $limit, $offset);

            return $query->getResult();
        } else {
            // Default
            return parent::findBy($criteria, $orderBy, $limit, $offset);
        }
    }

    /**
     * Finds a single entity by a set of criteria.
     *
     * Overrided to join the translations, if it exists, and add the where clauses
     * on the translation table if applicable
     *
     * @param array $criteria
     *
     * @return object
     */
    public function findOneBy(array $criteria)
    {
        try {
            if ($this->_class->hasAssociation('translations')) {
                // Join on Translation
                $query = $this->generateTranslationDql($criteria);

                return $query->getSingleResult();
            } else {
                // Default
                return parent::findOneBy($criteria);
            }
        } catch (NoResultException $e) {
            return null;
        }
    }

    /**
     * Adds support for magic finders
     *
     * @param $method
     * @param $arguments
     *
     * @return mixed
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\ORMException
     * @throws \BadMethodCallException
     */
    public function __call($method, $arguments)
    {
        if (substr($method, 0, 6) == 'findBy') {
            $by = substr($method, 6, strlen($method));
            $method = 'findBy';
    } elseif (substr($method, 0, 9) == 'findOneBy') {
            $by = substr($method, 9, strlen($method));
            $method = 'findOneBy';
        } else {
            throw new \BadMethodCallException(
                "Undefined method '$method'. The method name must start with ".
                    "either findBy or findOneBy!"
            );
        }

        if ( !isset($arguments[0])) {
            // we dont even want to allow null at this point, because we cannot (yet) transform it into IS NULL.
            throw ORMException::findByRequiresParameter($method.$by);
        }

        $fieldName = lcfirst(\Doctrine\Common\Util\Inflector::classify($by));

        $hasTranslation = false;
        if ($this->_class->hasAssociation('translations')) {
            $hasTranslation = true;
        }

        if ($this->_class->hasField($fieldName) || $this->_class->hasAssociation($fieldName) || $hasTranslation) {
            return $this->$method(array($fieldName => $arguments[0]));
        } else {
            throw ORMException::invalidFindByCall($this->_entityName, $fieldName, $method.$by);
        }
    }
}