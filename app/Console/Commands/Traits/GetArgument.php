<?php

namespace App\Console\Commands\Traits;

trait GetArgument
{
    /**
     * @param string $key
     * @param int $fallback
     * @return int
     */
    protected function getArgumentInt(string $key, int $fallback): int
    {
        return !is_array($this->argument($key)) && is_int($this->argument($key))
            ? (int) $this->argument($key)
            : $fallback;
    }
}
