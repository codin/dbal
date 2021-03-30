<?php

declare(strict_types=1);

namespace Codin\DBAL\Exceptions;

use Codin\DBAL\Contracts;
use Doctrine\DBAL\Exception\DriverException;
use Exception;

class GatewayError extends Exception
{
    public static function undefinedTableName(Contracts\Gateway $gateway): self
    {
        $className = \get_class($gateway);
        return new self(\sprintf('The property "table" must be set on class %s', $className));
    }

    public static function undefinedPrimaryKey(Contracts\Gateway $gateway): self
    {
        $className = \get_class($gateway);
        return new self(\sprintf('The property "primary" must be set as the primary key on class %s', $className));
    }

    public static function driverException(DriverException $e): self
    {
        return new self('There was error executing query', $e->getCode(), $e);
    }

    public static function updateException(DriverException $e): self
    {
        return new self('There was error executing update', $e->getCode(), $e);
    }
}
