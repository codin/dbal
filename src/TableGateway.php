<?php

declare(strict_types=1);

namespace Codin\DBAL;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

abstract class TableGateway implements Contracts\Gateway
{
    use Traits\Commands, Traits\MemoryLimit, Traits\Aggregation;

    /**
     * The database connection
     */
    protected Connection $conn;

    /**
     * The entity prototype to clone for each row
     */
    protected ?Contracts\Model $prototype = null;

    /**
     * The class name of the model to prototype
     */
    protected ?string $model = null;

    /**
     * The table name
     */
    protected string $table = '';

    /**
     * The primary key name
     */
    protected string $primary = '';

    public function __construct(Connection $conn, ?Contracts\Model $prototype = null)
    {
        $this->conn = $conn;
        $this->prototype = $prototype;

        if ('' === $this->table) {
            throw Exceptions\GatewayError::undefinedTableName($this);
        }

        if ('' === $this->primary) {
            throw Exceptions\GatewayError::undefinedPrimaryKey($this);
        }
    }

    /**
     * Create a new entity
     */
    protected function createModel(): Contracts\Model
    {
        if ($this->prototype instanceof Contracts\Model) {
            return clone $this->prototype;
        }

        // create prototype from model class name if one was set
        if (is_string($this->model) && class_exists($this->model)) {
            return new $this->model;
        }

        // create anon object class to create rows from
        return new class() implements Contracts\Model {
        };
    }

    /**
     * Get the table name
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Get name of primary key
     */
    public function getPrimary(): string
    {
        return $this->primary;
    }

    /**
     * Get database connection
     */
    public function getConnection(): Connection
    {
        return $this->conn;
    }

    /**
     * Get this table gateway query builder
     */
    public function getQueryBuilder(): QueryBuilder
    {
        return $this->getConnection()->createQueryBuilder()->select('*')->from($this->table);
    }
}
