<?php

namespace Kunstmaan\AdminListBundle\AdminList\Configurator;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Kunstmaan\AdminBundle\Helper\Security\Acl\AclHelper;
use Kunstmaan\AdminBundle\Helper\Security\Acl\Permission\PermissionDefinition;
use Kunstmaan\AdminListBundle\AdminList\Filter;
use Kunstmaan\AdminListBundle\AdminList\FilterType\ORM\AbstractORMFilterType;
use Kunstmaan\AdminListBundle\AdminList\SortableInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter as OrmQueryAdapter;
use Pagerfanta\Pagerfanta;
use Traversable;

/**
 * An abstract admin list configurator that can be used with the orm query builder
 */
abstract class AbstractDoctrineORMAdminListConfigurator extends AbstractAdminListConfigurator
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var Query
     */
    private $query;

    /**
     * @var Pagerfanta
     */
    private $pagerfanta;

    /**
     * @var PermissionDefinition
     */
    private $permissionDef;

    /**
     * @var AclHelper
     */
    protected $aclHelper;

    public function __construct(EntityManagerInterface $em, AclHelper $aclHelper = null)
    {
        $this->em = $em;
        $this->aclHelper = $aclHelper;
    }

    /**
     * Return the url to edit the given $item
     *
     * @param object $item
     *
     * @return array
     */
    public function getEditUrlFor($item)
    {
        $params = ['id' => $item->getId()];
        $params = array_merge($params, $this->getExtraParameters());

        return [
            'path' => $this->getPathByConvention($this::SUFFIX_EDIT),
            'params' => $params,
        ];
    }

    /**
     * Get the delete url for the given $item
     *
     * @param object $item
     *
     * @return array
     */
    public function getDeleteUrlFor($item)
    {
        $params = ['id' => $item->getId()];
        $params = array_merge($params, $this->getExtraParameters());

        return [
            'path' => $this->getPathByConvention($this::SUFFIX_DELETE),
            'params' => $params,
        ];
    }

    /**
     * @return Pagerfanta
     */
    public function getPagerfanta()
    {
        if (\is_null($this->pagerfanta)) {
            $adapter = new OrmQueryAdapter($this->getQuery());
            $this->pagerfanta = new Pagerfanta($adapter);
            $this->pagerfanta->setNormalizeOutOfRangePages(true);
            $this->pagerfanta->setMaxPerPage($this->getLimit());
            $this->pagerfanta->setCurrentPage($this->getPage());
        }

        return $this->pagerfanta;
    }

    public function adaptQueryBuilder(QueryBuilder $queryBuilder)
    {
        $queryBuilder->where('1=1');
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->getPagerfanta()->getNbResults();
    }

    /**
     * @return array|Traversable
     */
    public function getItems()
    {
        return $this->getPagerfanta()->getCurrentPageResults();
    }

    /**
     * Return an iterator for all items that matches the current filtering
     *
     * @return \Iterator
     */
    public function getIterator()
    {
        return $this->getQuery()->iterate();
    }

    /**
     * @return Query|null
     */
    public function getQuery()
    {
        if (\is_null($this->query)) {
            $queryBuilder = $this->getQueryBuilder();
            $this->adaptQueryBuilder($queryBuilder);

            // Apply filters
            $filters = $this->getFilterBuilder()->getCurrentFilters();
            /* @var Filter $filter */
            foreach ($filters as $filter) {
                /* @var AbstractORMFilterType $type */
                $type = $filter->getType();
                $type->setQueryBuilder($queryBuilder);
                $filter->apply();
            }

            // Apply sorting
            if (!empty($this->orderBy)) {
                $orderBy = $this->orderBy;
                if ($this->getEntityManager()->getClassMetadata($this->getRepositoryName())->hasAssociation($this->orderBy)) {
                    $queryBuilder->leftJoin('b.' . $orderBy, 'A' . $orderBy);
                    $orderBy = 'A' . $orderBy . '.id';
                } elseif (!strpos($orderBy, '.')) {
                    $orderBy = 'b.' . $orderBy;
                }
                $queryBuilder->orderBy($orderBy, $this->orderDirection == 'DESC' ? 'DESC' : 'ASC');
            }

            // Apply other changes
            $this->finishQueryBuilder($queryBuilder);

            // Apply ACL restrictions (if applicable)
            if (!\is_null($this->permissionDef) && !\is_null($this->aclHelper)) {
                $this->query = $this->aclHelper->apply($queryBuilder, $this->permissionDef);
            } else {
                $this->query = $queryBuilder->getQuery();
            }
        }

        return $this->query;
    }

    protected function finishQueryBuilder(QueryBuilder $queryBuilder)
    {
        if ($this instanceof SortableInterface) {
            $queryBuilder->addOrderBy('b.' . $this->getSortableField());
        }
    }

    /**
     * @return QueryBuilder
     */
    protected function getQueryBuilder()
    {
        $queryBuilder = $this->em
            ->getRepository($this->getRepositoryName())
            ->createQueryBuilder('b');

        return $queryBuilder;
    }

    /**
     * Get current permission definition.
     *
     * @return PermissionDefinition|null
     */
    public function getPermissionDefinition()
    {
        return $this->permissionDef;
    }

    /**
     * Set permission definition.
     *
     * @return AbstractDoctrineORMAdminListConfigurator
     */
    public function setPermissionDefinition(PermissionDefinition $permissionDef)
    {
        $this->permissionDef = $permissionDef;

        return $this;
    }

    /**
     * @return AbstractDoctrineORMAdminListConfigurator
     */
    public function setEntityManager(EntityManagerInterface $em)
    {
        $this->em = $em;

        return $this;
    }

    /**
     * @return EntityManagerInterface
     */
    public function getEntityManager()
    {
        return $this->em;
    }
}
