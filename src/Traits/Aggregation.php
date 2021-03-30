<?php

declare(strict_types=1);

namespace Codin\DBAL\Traits;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

trait Aggregation
{
    /**
     * Get active database connection
     */
    abstract public function getConnection(): Connection;

    /**
     * Get this table gateway query builder
     */
    abstract public function getQueryBuilder(): QueryBuilder;

    /**
     * Fetch the first column from the first row of a query
     */
    abstract public function column(QueryBuilder $query = null);

    /**
     * Run a aggregate function against a column
     */
    private function aggregate(string $method, string $column = null, QueryBuilder $query = null): string
    {
        $newQuery = null === $query ? $this->getQueryBuilder() : clone $query;

        if (null === $column) {
            $column = \sprintf(
                '%s.%s',
                $this->getConnection()->quoteIdentifier($this->table),
                $this->getConnection()->quoteIdentifier($this->primary)
            );
        }

        $newQuery->select(\sprintf('%s(%s)', $method, $column));

        $value = $this->column($newQuery);

        // null if there are no more rows
        if (null === $value) {
            return '0';
        }

        return $value;
    }

    /**
     * Run count aggregate
     */
    public function count(QueryBuilder $query = null, string $column = null): string
    {
        return $this->aggregate('count', $column, $query);
    }

    /**
     * Run sum aggregate
     */
    public function sum(QueryBuilder $query = null, string $column = null): string
    {
        return $this->aggregate('sum', $column, $query);
    }

    /**
     * Run min aggregate
     */
    public function min(QueryBuilder $query = null, string $column = null): string
    {
        return $this->aggregate('min', $column, $query);
    }

    /**
     * Run max aggregate
     */
    public function max(QueryBuilder $query = null, string $column = null): string
    {
        return $this->aggregate('max', $column, $query);
    }
}
