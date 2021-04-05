<?php

declare(strict_types=1);

namespace Codin\DBAL\Traits;

trait MemoryLimit
{
    protected int $memoryLimit = 0;

    /**
     * Get memory limit in bytes
     */
    protected function getMemoryLimit(): int
    {
        if ($this->memoryLimit) {
            return $this->memoryLimit;
        }
        $limit = \ini_get('memory_limit') === '-1' ? PHP_INT_MAX : \ini_get('memory_limit');
        return $this->memoryLimit = $this->toBytes((string) $limit);
    }

    /**
     * Convert binary size string (512M) into bytes
     */
    protected function toBytes(string $string): int
    {
        \sscanf($string, '%u%c', $number, $suffix);

        if (isset($suffix)) {
            $exp = \strpos(' KMGT', \strtoupper($suffix));
            if (false !== $exp) {
                $number = $number * \pow(1024, $exp);
            }
        }

        return $number;
    }

    /**
     * Get memory remaining
     */
    protected function getMemoryRemaining(int $buffer = 1048576): int
    {
        $limit = $this->getMemoryLimit();
        $current = \memory_get_usage();
        return $limit - ($current + $buffer);
    }
}
