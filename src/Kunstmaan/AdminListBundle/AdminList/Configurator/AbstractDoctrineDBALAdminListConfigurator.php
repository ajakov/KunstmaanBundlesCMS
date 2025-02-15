<?php

namespace Kunstmaan\AdminListBundle\AdminList\Configurator;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Kunstmaan\AdminListBundle\AdminList\FilterType\DBAL\AbstractDBALFilterType;
use Pagerfanta\Doctrine\DBAL\QueryAdapter as DbalQueryAdapter;
use Pagerfanta\Pagerfanta;

/**
 * An abstract admin list configurator that can be used with dbal query builder
 */
abstract class AbstractDoctrineDBALAdminListConfigurator extends AbstractAdminListConfigurator
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var QueryBuilder
     */
    protected $queryBuilder;

    /**
     * @var Pagerfanta
     */
    private $pagerfanta;

    /**
     * @var string
     */
    private $countField = 'b.id';

    /**
     * @var bool
     */
    private $useDistinctCount = true;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Return the url to edit the given $item
     *
     * @param array $item
     *
     * @return array
     */
    public function getEditUrlFor($item)
    {
        $params = ['id' => $item['id']];
        $params = array_merge($params, $this->getExtraParameters());

        return [
            'path' => $this->getPathByConvention($this::SUFFIX_EDIT),
            'params' => $params,
        ];
    }

    /**
     * Get the delete url for the given $item
     *
     * @param array $item
     *
     * @return array
     */
    public function getDeleteUrlFor($item)
    {
        $params = ['id' => $item['id']];
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
            $adapter = new DbalQueryAdapter($this->getQueryBuilder(), function (QueryBuilder $queryBuilder): void {
                $distinctString = $this->getUseDistinctCount() ? 'DISTINCT ' : '';
                $queryBuilder->select('COUNT(' . $distinctString . $this->getCountField() . ') AS total_results')
                    ->resetQueryPart('orderBy')
                    ->setMaxResults(1);
            });

            $this->pagerfanta = new Pagerfanta($adapter);
            $this->pagerfanta->setMaxPerPage($this->getLimit());
            $this->pagerfanta->setCurrentPage($this->getPage());
        }

        return $this->pagerfanta;
    }

    /**
     * @param array<int|string, mixed> $params
     *
     * @return void
     */
    public function adaptQueryBuilder(QueryBuilder $queryBuilder, array $params = [])
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
     * @return array|mixed|\Traversable
     */
    public function getItems()
    {
        return $this->getPagerfanta()->getCurrentPageResults();
    }

    /**
     * Return an iterable statement or int for all items that matches the current filtering
     *
     * @return \Traversable|int
     */
    public function getIterator()
    {
        return $this->getQueryBuilder()->execute()->iterateAssociative();
    }

    /**
     * @return QueryBuilder
     */
    public function getQueryBuilder()
    {
        if (\is_null($this->queryBuilder)) {
            $this->queryBuilder = new QueryBuilder($this->connection);
            $this->adaptQueryBuilder($this->queryBuilder);

            // Apply filters
            $filters = $this->getFilterBuilder()->getCurrentFilters();
            foreach ($filters as $filter) {
                /* @var AbstractDBALFilterType $type */
                $type = $filter->getType();
                $type->setQueryBuilder($this->queryBuilder);
                $filter->apply();
            }

            // Apply sorting
            if (!empty($this->orderBy)) {
                $orderBy = $this->orderBy;
                $this->queryBuilder->orderBy($orderBy, $this->orderDirection == 'DESC' ? 'DESC' : 'ASC');
            }
        }

        return $this->queryBuilder;
    }

    /**
     * Set count field (must include table alias!)
     *
     * @param string $countField
     *
     * @return AbstractDoctrineDBALAdminListConfigurator
     */
    public function setCountField($countField)
    {
        $this->countField = $countField;

        return $this;
    }

    /**
     * Get current count field (including table alias)
     *
     * @return string
     */
    public function getCountField()
    {
        return $this->countField;
    }

    /**
     * When doing the count you can turn the distinct on or off.
     *
     * @param bool $value
     *
     * @return AbstractDoctrineDBALAdminListConfigurator
     */
    public function setUseDistinctCount($value)
    {
        $this->useDistinctCount = $value;

        return $this;
    }

    /**
     * Get current doDistinctCount
     *
     * @return bool
     */
    public function getUseDistinctCount()
    {
        return $this->useDistinctCount;
    }
}
