<?php

return [

    /*
    |--------------------------------------------------------------------------
    | The alert message template language lines
    |--------------------------------------------------------------------------
    |
    | See also config/alert.php
    |
    */

    'template' => [
        '101' => [
            'name' => 'Notice message(01)',
            'subject' => '[IkitellMe] Please try contact to the no response user.',
        ],

        '102' => [
            'name' => 'Notice message(02)',
            'subject' => '[IkitellMe] Please try contact to the no response user.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | The mock variables language lines
    |--------------------------------------------------------------------------
    |
    | Using for email preview.
    |
    */

    'mock' => [
        'device_user_name' => '{Your device user name}',
        'device_name' => '{Your device name}',
        'device_reported_at' => '',
        'rule_time_limits' => '24',
        'rule_notify_times' => '1',
        'rule_embedded_message' => '{Rules additional message}',
        'to_name' => '{Target contacts name}',
        'to_email' => '',
    ],
];
