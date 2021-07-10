<?php

declare(strict_types=1);

namespace Codin\DBAL;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

class TableGateway implements Contracts\Gateway
{
    use Traits\Models, Traits\Commands, Traits\MemoryLimit, Traits\Aggregation;

    /**
     * The database connection
     */
    protected Connection $conn;

    /**
     * The entity prototype to clone for each row
     */
    protected ?object $prototype = null;

    /**
     * The class name of the model to prototype
     */
    protected ?string $model = null;

    /**
     * The table name
     */
    protected string $table;

    /**
     * The primary key name
     */
    protected string $primary;

    public function __construct(Connection $conn, ?object $prototype = null)
    {
        $this->conn = $conn;
        $this->prototype = $prototype;
    }

    /**
     * Get the table name
     */
    public function getTable(): string
    {
        if (!isset($this->table) || '' === $this->table) {
            throw Exceptions\GatewayError::undefinedTableName($this);
        }
        return $this->table;
    }

    /**
     * Get name of primary key
     */
    public function getPrimary(): string
    {
        if (!isset($this->primary) || '' === $this->primary) {
            throw Exceptions\GatewayError::undefinedPrimaryKey($this);
        }
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
        return $this->getConnection()->createQueryBuilder()->select('*')->from($this->getTable());
    }
}
