<?php

declare(strict_types=1);

namespace Codin\DBAL\Traits;

use Codin\DBAL\Contracts;
use Codin\DBAL\Exceptions;
use Doctrine\DBAL\Exception\DriverException;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Result;
use Generator;
use ReflectionClass;
use ReflectionProperty;

trait Commands
{
    /**
     * Get public properties on a model that can be set. Returns key-value array.
     */
    protected function getModelProps(Contracts\Model $model): array
    {
        $definitions = [];
        $ref = new ReflectionClass($model);
        $props = $ref->getProperties(ReflectionProperty::IS_PUBLIC);
        foreach ($props as $prop) {
            $definitions[$prop->getName()] = $model->{$prop->getName()};
        }
        return $definitions;
    }

    /**
     * Create a model entity from database row
     */
    protected function model(array $attributes): Contracts\Model
    {
        $model = $this->createModel();

        foreach ($this->getModelProps($model) as $prop => $default) {
            $model->{$prop} = array_key_exists($prop, $attributes) ? $attributes[$prop] : $default;
        }

        return $model;
    }

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
    public function fetch(?QueryBuilder $query = null): ?Contracts\Model
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
        $buffer = [];

        // when buffering results into array
        // check memory usage to prevent fatal error
        $limit = $this->getMemoryLimit();
        $current = \memory_get_usage();
        $max = $limit - $current - (1024 * 1024);

        foreach ($result->iterateAssociative() as $row) {
            $buffer[] = $this->model($row);
            if (\memory_get_usage() > $max) {
                throw Exceptions\GatewayOverflow::create($max);
            }
        }

        return $buffer;
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
        if ($this->getConnection()->insert($this->table, $params)) {
            $platform = $this->getConnection()->getDatabasePlatform();
            $sequenceName = $platform->supportsSequences() ?
                $platform->getIdentitySequenceName($this->table, $this->primary) :
                null;
            return $this->getConnection()->lastInsertId($sequenceName);
        }

        return '0';
    }

    /**
     * Update from query returning the number of row affected
     */
    public function update(QueryBuilder $query): string
    {
        $query->update($this->table);

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
        $query->delete($this->table);

        $result = $this->execute($query);

        return (string) $result->rowCount();
    }

    /**
     * Insert/Update a model to a gateway table
     */
    public function persist(Contracts\Model $model): string
    {
        $data = [];

        foreach ($this->getModelProps($model) as $prop => $default) {
            if ($prop !== $this->primary) {
                $data[$prop] = $model->{$prop};
            }
        }

        if ($model->{$this->primary} === null) {
            return $this->insert($data);
        }

        $criteria = [$this->primary => $model->{$this->primary}];

        return (string) $this->getConnection()
            ->update($this->table, $data, $criteria);
    }
}
