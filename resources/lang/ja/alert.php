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
            'name' => '通知メールテンプレート(01)',
            'subject' => '[IkitellMe] 安否確認のお願い',
        ],

        '102' => [
            'name' => '通知メールテンプレート(02)',
            'subject' => '[IkitellMe] 安否確認のお願い',
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
        'device_user_name' => '「(端末の利用者名)」',
        'device_name' => '「(端末の名称)」',
        'device_reported_at' => '',
        'rule_time_limits' => '24',
        'rule_notify_times' => '1',
        'rule_embedded_message' => '追加メッセージ',
        'to_name' => '「(通知先の名前)」',
        'to_email' => '',
    ],
];
