<?php

namespace App\Enums\Device;


enum DeviceImageType: int
{
    case PRESET = 1;
    case CUSTOM = 2;

    /**
     * @return array<string, int>
     */
    public static function toArray(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [strtolower($case->name) => $case->value])
            ->all();
    }

    /**
     * @param string $name
     * @return DeviceImageType
     */
    public static function fromName(string $name): DeviceImageType
    {
        return match (strtoupper($name)) {
            'PRESET' => self::PRESET,
            'CUSTOM' => self::CUSTOM,
        };
    }
}
