<?php

declare(strict_types=1);

namespace Codin\DBAL\Traits;

trait MemoryLimit
{
    /**
     * Get memory limit in bytes
     */
    protected function getMemoryLimit(): int
    {
        $limit = \ini_get('memory_limit');
        if ($limit === '-1' || empty($limit)) {
            return PHP_INT_MAX;
        }
        return $this->toBytes($limit);
    }

    /**
     * Convert binary size string (512M) into bytes
     */
    protected function toBytes(string $string): int
    {
        \sscanf($string, '%u%c', $number, $suffix);

        if (isset($suffix)) {
            $exp = \strpos(' KMG', \strtoupper($suffix));
            if (false !== $exp) {
                $number = $number * \pow(1024, $exp);
            }
        }

        return $number;
    }
}
