<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Various Words Language Lines.
    | (For show in label etc)
    |--------------------------------------------------------------------------
    |
    */

    'email' => 'メールアドレス',
    'password' => 'パスワード',
    'reminder' => 'ログインしたままにする',
    'password_confirm' => 'パスワード(確認)',

    'notice_log' => '通知ログ',
    'notice_rule' => '通知ルール',
    'notice_address' => '通知先',
    'notice_times' => '通知回数',
    'send_date' => '送信日時',

    'today' => '本日',
    'tomorrow' => '明日',
    'yesterday' => '昨日',

    'verified' => '承認済み',
    'verify_expired' => '承認期限切れ',
    'verify_waiting' => '承認待ち',
    'verified_date' => '承認日',

    'from' => 'から',
    'to' => 'まで',

    'unit' => [
        'day' => '日',
        'times' => '回',
    ],

    'btn' => [
        'ok' => 'OK',
        'cancel' => 'キャンセル',
        'register' => '登録',
        'new' => '新規作成',
        'add' => '新規',
        'edit' => '編集',
        'update' => '更新',
        'delete' => '削除',

        'register_account' => 'アカウント登録',
        'resent_verify' => '再送信',
        'login' => 'ログイン',
        'logout' => 'ログアウト',
        'request_reset_password' => '再設定メールを送信',
        'reset_password' => 'OK',

        'reset_timer' => 'タイマーリセット',
        'send_verify_request' => '承認依頼メールを送信',
    ],

    'page_title' => [
        'welcome' => 'ようこそ',
        'login' => 'ログイン',
        'register_account' => 'アカウント登録',
        'verify_email' => 'メールアドレス確認',
        'reset_password' => 'パスワード再設定',
        'about' => 'サービス説明',
        'terms' => '利用規約',
        'error' => 'エラー',
    ],

    'section_title' => [
        'notice_log' => [
            'outline' => '通知ログ概要',
            'content' => '送信内容',
        ],
        'about' => [
            'outline' => '概要',
            'usage' => '使い方',
            'specs' => '仕様一覧',
        ],
        'terms' => [
            'summary' => '利用規約 (要約)',
            'details' => '利用規約 (詳細)',
        ],
    ],

    'menu' => [
        'home' => 'ホーム',
        'device' => '端末',
        'rule' => '通知ルール',
        'notice_address' => '通知先',
        'profile' => 'プロフィール',
        'logout' => 'ログアウト',
    ],

    'link' => [
        'ask_password_reset' => 'パスワードをお忘れの方はこちら',
        'about' => 'サービス説明',
        'terms' => '利用規約',
        'contact' => 'お問い合わせ',
        'reset_password' => 'パスワード変更',
        'delete_account' => 'アカウント削除',
    ],

    'placeholder' => [
        'email' => 'xxxx@ikitell.me',

        'device' => [
            'name' => '',
            'reset_word' => 'タイマーリセット',
            'notice_rule' => '通知ルールを選択',
            'notice_address' => '通知先を選択',
        ],

        'rule' => [
            'name' => '',
            'message_id' => 'メッセージテンプレートを選択',
        ],
    ],

    'default' => [
        'user' => [
            'name' => 'ユーザー',
        ],

        'device' => [
            'name' => '初期端末',
            'reset_word' => 'タイマーリセット',
            'user_name' => '端末ユーザー',
        ],

        'rule' => [
            'name' => '初期通知ルール',
        ],
    ],
];
