<?php

declare(strict_types=1);

namespace Codin\DBAL\Traits;

use Doctrine\DBAL\Query\QueryBuilder;

trait Aggregation
{
    /**
     * Run a aggregate function against a column
     */
    private function aggregate(string $method, QueryBuilder $query = null, string $column = null): string
    {
        $newQuery = null === $query ? $this->getQueryBuilder() : clone $query;

        if (null === $column) {
            $column = \sprintf(
                '%s.%s',
                $this->getConnection()->quoteIdentifier($this->getTable()),
                $this->getConnection()->quoteIdentifier($this->getPrimary())
            );
        }

        $newQuery->select(\sprintf('%s(%s)', $method, $column));

        $value = $this->column($newQuery);

        // null if there are no more rows
        if (null === $value || '' === $value) {
            return '0';
        }

        return $value;
    }

    /**
     * Run count aggregate
     */
    public function count(QueryBuilder $query = null, string $column = null): string
    {
        return $this->aggregate('count', $query, $column);
    }

    /**
     * Run sum aggregate
     */
    public function sum(QueryBuilder $query = null, string $column = null): string
    {
        return $this->aggregate('sum', $query, $column);
    }

    /**
     * Run min aggregate
     */
    public function min(QueryBuilder $query = null, string $column = null): string
    {
        return $this->aggregate('min', $query, $column);
    }

    /**
     * Run max aggregate
     */
    public function max(QueryBuilder $query = null, string $column = null): string
    {
        return $this->aggregate('max', $query, $column);
    }
}
