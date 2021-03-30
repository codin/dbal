<?php

declare(strict_types=1);

namespace Codin\DBAL\Contracts;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

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
}
