<?php

namespace App\Enums\DeviceLog;

enum ReportingType: int
{
    /*
    |--------------------------------------------------------------------------
    | The types of report.
    |--------------------------------------------------------------------------
    |
    | 'device_log.reporting_type'
    |
    | 1: Manual report by the user with native app.
    | 2: Manual report by the user with web app.
    | 3: Automatic report from the unknown device.
    | 4: System report for the resume from suspend.
    | 5: System report for other cases.
    | 0: Unknown (default).
    |
    */

    case USER_APP = 1;
    case USER_WEB = 2;
    case AUTO = 3;
    case SYSTEM_RESUME = 4;
    case SYSTEM = 5;
    case UNKNOWN = 0;
}
