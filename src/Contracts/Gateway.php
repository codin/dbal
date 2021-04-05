<?php

declare(strict_types=1);

namespace Codin\DBAL\Contracts;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Generator;

interface Gateway
{
    /**
     * Get the table name
     */
    public function getTable(): string;

    /**
     * Get name of primary key
     */
    public function getPrimary(): string;

    /**
     * Get active database connection
     */
    public function getConnection(): Connection;

    /**
     * Get this table gateway query builder
     */
    public function getQueryBuilder(): QueryBuilder;

    /**
     * Fetch the first row from a query
     */
    public function fetch(?QueryBuilder $query = null): ?Model;

    /**
     * Get array of entities from the query
     */
    public function get(?QueryBuilder $query = null): array;

    /**
     * Get unbuffered array of entities from the query
     */
    public function getUnbuffered(?QueryBuilder $query = null): Generator;

    /**
     * Fetch the first column from the first row of a query
     */
    public function column(?QueryBuilder $query = null): ?string;

    /**
     * Insert array of data returning the insert id
     */
    public function insert(array $params): string;

    /**
     * Update from query returning the number of row affected
     */
    public function update(QueryBuilder $query): string;

    /**
     * Delete rows from table gateway using query
     * returning the number of row affected
     */
    public function delete(QueryBuilder $query): string;

    /**
     * Run count aggregate
     */
    public function count(QueryBuilder $query = null, string $column = null): string;

    /**
     * Run sum aggregate
     */
    public function sum(QueryBuilder $query = null, string $column = null): string;

    /**
     * Run min aggregate
     */
    public function min(QueryBuilder $query = null, string $column = null): string;

    /**
     * Run max aggregate
     */
    public function max(QueryBuilder $query = null, string $column = null): string;
}
