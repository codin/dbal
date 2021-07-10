<?php

declare(strict_types=1);

namespace Codin\DBAL\Traits;

use Codin\DBAL\Exceptions;
use Doctrine\DBAL\Exception\DriverException;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Result;
use Generator;

trait Commands
{
    /**
     * Execute a query against this table gateway
     */
    protected function execute(?QueryBuilder $query = null): Result
    {
        if (null === $query) {
            $query = $this->getQueryBuilder();
        }

        try {
            return $this->getConnection()->executeQuery($query->getSQL(), $query->getParameters());
        } catch (DriverException $e) {
            throw Exceptions\GatewayError::driverException($e);
        }
    }

    /**
     * Fetch the first row from a query
     */
    public function fetch(?QueryBuilder $query = null): ?object
    {
        $statement = $this->execute($query);

        if ($row = $statement->fetchAssociative()) {
            return $this->model($row);
        }

        return null;
    }

    /**
     * Get array of entities from the query
     */
    public function get(?QueryBuilder $query = null): array
    {
        $result = $this->execute($query);
        $buffered = [];

        foreach ($result->iterateAssociative() as $row) {
            $buffered[] = $this->model($row);
            if ($this->getMemoryRemaining() <= 0) {
                throw Exceptions\GatewayOverflow::create($this->getMemoryLimit());
            }
        }

        return $buffered;
    }

    /**
     * Get unbuffered array of entities from the query
     */
    public function getUnbuffered(?QueryBuilder $query = null): Generator
    {
        $result = $this->execute($query);

        foreach ($result->iterateAssociative() as $row) {
            yield $this->model($row);
        }
    }

    /**
     * Fetch the first column from the first row of a query
     */
    public function column(?QueryBuilder $query = null): ?string
    {
        $result = $this->execute($query);

        $column = $result->fetchOne();

        if (is_bool($column) && false === $column) {
            return null;
        }

        return (string) $column;
    }

    /**
     * Insert array of data returning the insert id
     */
    public function insert(array $params): string
    {
        if (\count($params) === 0) {
            throw Exceptions\GatewayError::invalidInsert();
        }

        try {
            $result = $this->getConnection()->insert($this->getTable(), $params);
        } catch (DriverException $e) {
            throw Exceptions\GatewayError::driverException($e);
        }

        $platform = $this->getConnection()->getDatabasePlatform();
        $sequenceName = $platform->supportsSequences() ? $platform->getIdentitySequenceName($this->getTable(), $this->getPrimary()) : null;

        return $result ? $this->getConnection()->lastInsertId($sequenceName) : '0';
    }

    /**
     * Update from query returning the number of row affected
     */
    public function update(QueryBuilder $query): string
    {
        $query->update($this->getTable());

        try {
            return (string) $this->getConnection()->executeUpdate($query->getSQL(), $query->getParameters());
        } catch (DriverException $e) {
            throw Exceptions\GatewayError::updateException($e);
        }
    }

    /**
     * Delete rows from table gateway using query
     * returning the number of row affected
     */
    public function delete(QueryBuilder $query): string
    {
        $query->delete($this->getTable());

        $result = $this->execute($query);

        return (string) $result->rowCount();
    }
}
