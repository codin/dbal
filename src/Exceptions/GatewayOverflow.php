<?php

declare(strict_types=1);

namespace Codin\DBAL\Exceptions;

use OverflowException;

class GatewayOverflow extends OverflowException
{
    public static function create(int $max): self
    {
        return new self(\sprintf('Allowed memory size of %s bytes has been exceeded', $max));
    }
}
