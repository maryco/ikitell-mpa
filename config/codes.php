<?php

return [

    /*
    |--------------------------------------------------------------------------
    | The Subscription types.
    |--------------------------------------------------------------------------
    |
    | 'users.plan'
    |
    | 0: 'Basic' for the personal use.
    | 1: 'Business' for the business use.
    | 3: 'Limited' for the report only user. (Register by business user)
    */

    'subscription_types' => [
        'basic' => 0,
        'business' => 1,
        'limited' => 2,
    ],

    /*
    |--------------------------------------------------------------------------
    | The types of devices.
    |--------------------------------------------------------------------------
    |
    | 'devices.type'
    |
    | 1: PC
    | 2: Mobile(apple)
    | 3: Mobile(Android)
    | 4: Mobile(Other)
    | 5: Other
    | 0: Unknown
    |
    */

    'device_types' => [
        'pc' => 1,
        'mobile-apple' => 2,
        'mobile-android' => 3,
        'mobile-other' => 4,
        'other' => 5,
        'unknown' => 0,
    ],

    /*
    |--------------------------------------------------------------------------
    | The types of report.
    |--------------------------------------------------------------------------
    |
    | 'device_log.reporting_type'
    |
    | 1: Manual report by the user with a native app.
    | 2: Manual report by the user with a web app.
    | 3: Automatic report from the unknown device.
    | 4: System report for the resume from suspend.
    | 5: System report for other cases.
    | 0: Unknown(default).
    |
    */

    'report_types' => [
        'user_app' => 1,
        'user_web' => 2,
        'auto' => 3,
        'system_resume' => 4,
        'system' => 5,
        'unknown' => 0,
    ],
];
