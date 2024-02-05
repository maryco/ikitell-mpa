<?php

namespace App\Enums\NotificationLog;

enum JobStatus: int
{
    case RESERVED = 1;
    case EXECUTED = 2;
    case FAILED = 3;
    case UNKNOWN = 9;

    /**
     * @return array<int, int>
     */
    public static function values(): array
    {
        return array_map(static fn($case) => $case->value, self::cases());
    }
}
