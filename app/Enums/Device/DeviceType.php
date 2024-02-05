<?php

namespace App\Enums\Device;

enum DeviceType: int
{
    /*
    |--------------------------------------------------------------------------
    | The types of devices.
    |--------------------------------------------------------------------------
    |
    | 'devices.type'
    |
    | 1: General (pc or mobile)
    | 0: Unknown
    |
    */

    case GENERAL = 1;
    case UNKNOWN = 0;
}
