<?php

return [

    /*
    |--------------------------------------------------------------------------
    | For Various Message Language Lines
    |--------------------------------------------------------------------------
    |
    */

    'app' => [
        'registered' => 'Account registered!\n Will send mail for the verify your email address. Please complete verify at the mail.',
        'verified' => 'Complete verify email address!\nAnd has reset timer of devices, for activate.',
        'resent' => 'A fresh verification link has been sent to your email address.',
        'saved' => 'Saved.',
        'edited' => 'Edited.',
        'deleted' => 'Deleted.',
        'verify_requested' => 'Sent verify request email.',

        'notice' => [
            'has_error' => 'Some error has your input, please check the below.',
        ],
    ],

    'support' => [
        'resent_verify' => [
            '1' => 'Before proceeding, please check your email for a verification link.',
            '2' => 'If you did not receive the email, click below link to request another.',
        ],

        'thanks_verified' => 'Thank you for a verified.',
        'empty_list' => 'The list is nothing.',
        'empty' => 'Nothing.',
    ],

    'confirm' => [
        'delete' => 'Are you really sure to delete?',
        'send_verify_request' => 'Do send verify request mail?\n(NOTE: If send, need to wait several minutes to next send.)',
        'password_request' => 'Do send reset your password mail?\n(NOTE: Logout after sent.)',
    ],

    // TODO:
    'error' => [
        'whoops' => 'Whoops! something is wrong.',
        'notfound' => 'The :attribute is not found',
        'invalid_user' => 'Invalid user',
        'report_interval' => 'The resettable interval is :minutes minutes.',
        'send_verify_request' => '通知先が「承認済み」,または最後の承認依頼メールの送信から「:minutes分」経過していない場合は送信できません',

        '400' => 'Bad Request',
        '401' => 'Unauthorized',
        '403' => 'Forbidden',
        '404' => 'Not Found',
        '405' => 'Invalid access', // 'Method Not Allowed'
        '422' => 'Unprocessable Entity',
        '429' => 'Too many requests',
        '500' => 'Internal Server Error',
        '503' => 'Service Unavailable',
    ],
];
