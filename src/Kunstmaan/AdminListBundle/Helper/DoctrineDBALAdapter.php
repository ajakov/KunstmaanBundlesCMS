<?php

namespace Kunstmaan\AdminListBundle\Helper;

use Doctrine\DBAL\Query\QueryBuilder;
use Pagerfanta\Adapter\AdapterInterface;
use Pagerfanta\Exception\LogicException;

/**
 * DoctrineDBALAdapter.
 *
 * @author Michael Williams <michael@whizdevelopment.com>
 *
 * @api
 *
 * @deprecated since KunstmaanAdminListBundle 6.2 and will be removed in KunstmaanAdminListBundle 7.0. Use the dbal query adapter of "pagerfanta/doctrine-dbal-adapter" instead.
 */
class DoctrineDBALAdapter implements AdapterInterface
{
    private $queryBuilder;

    private $countField;

    private $useDistinct;

    /**
     * @param QueryBuilder $queryBuilder a DBAL query builder
     * @param string       $countField   Primary key for the table in query. Used in count expression. Must include table alias
     * @param bool         $useDistinct  when set to true it'll count the countfield with a distinct in front of it
     *
     * @api
     */
    public function __construct(QueryBuilder $queryBuilder, $countField, $useDistinct = true)
    {
        trigger_deprecation('kunstmaan/adminlist-bundle', '6.2', 'Class "%s" is deprecated, Use the dbal query adapter of "pagerfanta/doctrine-dbal-adapter" instead.', __CLASS__);

        if (strpos($countField, '.') === false) {
            throw new LogicException('The $countField must contain a table alias in the string.');
        }

        if (QueryBuilder::SELECT !== $queryBuilder->getType()) {
            throw new LogicException('Only SELECT queries can be paginated.');
        }

        $this->queryBuilder = $queryBuilder;
        $this->countField = $countField;
        $this->useDistinct = $useDistinct;
    }

    /**
     * Returns the query builder.
     *
     * @return QueryBuilder the query builder
     *
     * @api
     */
    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }

    /**
     * {@inheritdoc}
     *
     * @return int
     */
    public function getNbResults()
    {
        $query = clone $this->queryBuilder;
        $distinctString = '';
        if ($this->useDistinct) {
            $distinctString = 'DISTINCT ';
        }
        $query->resetQueryPart('orderBy');
        $statement = $query->select('COUNT(' . $distinctString . $this->countField . ') AS total_results')
            ->execute();

        return ($results = $statement->fetchOne()) ? (int) $results : 0;
    }

    /**
     * {@inheritdoc}
     *
     * @return iterable
     */
    public function getSlice($offset, $length)
    {
        $query = clone $this->queryBuilder;

        $result = $query->setMaxResults($length)
            ->setFirstResult($offset)
            ->execute();

        return $result->fetchAllAssociative();
    }
}
