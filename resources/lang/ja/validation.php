<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'             => 'The :attribute must be accepted.',
    'active_url'           => 'The :attribute is not a valid URL.',
    'after'                => '「:attribute」 は :date より後を指定してください',
    'after_or_equal'       => '「:attribute」 は :date 以降を指定してください.',
    'alpha'                => 'The :attribute may only contain letters.',
    'alpha_dash'           => 'The :attribute may only contain letters, numbers, and dashes.',
    'alpha_num'            => 'The :attribute may only contain letters and numbers.',
    'array'                => 'The :attribute must be an array.',
    'before'               => 'The :attribute must be a date before :date.',
    'before_or_equal'      => 'The :attribute must be a date before or equal to :date.',
    'between'              => [
        'numeric' => 'The :attribute must be between :min and :max.',
        'file'    => 'The :attribute must be between :min and :max kilobytes.',
        'string'  => 'The :attribute must be between :min and :max characters.',
        'array'   => 'The :attribute must have between :min and :max items.',
    ],
    'boolean'              => '「:attribute」の値が正しくありません',
    'confirmed'            => '「:attribute」が一致していません',
    'date'                 => '「:attribute」 が日付として正しくありません',
    'date_format'          => '「:attribute」 は 『:format』の形式で入力してください',
    'different'            => 'The :attribute and :other must be different.',
    'digits'               => 'The :attribute must be :digits digits.',
    'digits_between'       => 'The :attribute must be between :min and :max digits.',
    'dimensions'           => 'The :attribute has invalid image dimensions.',
    'distinct'             => 'The :attribute field has a duplicate value.',
    'email'                => '「:attribute」メールアドレスとして正しくありません',
    'exists'               => '指定された「:attribute」が見つかりません',
    'file'                 => 'The :attribute must be a file.',
    'filled'               => 'The :attribute field must have a value.',
    'gt'                   => [
        'numeric' => 'The :attribute must be greater than :value.',
        'file'    => 'The :attribute must be greater than :value kilobytes.',
        'string'  => 'The :attribute must be greater than :value characters.',
        'array'   => 'The :attribute must have more than :value items.',
    ],
    'gte'                  => [
        'numeric' => 'The :attribute must be greater than or equal :value.',
        'file'    => 'The :attribute must be greater than or equal :value kilobytes.',
        'string'  => 'The :attribute must be greater than or equal :value characters.',
        'array'   => 'The :attribute must have :value items or more.',
    ],
    'image'                => 'The :attribute must be an image.',
    'in'                   => '選択された 「:attribute」 が正しくありません',
    'in_array'             => 'The :attribute field does not exist in :other.',
    'integer'              => 'The :attribute must be an integer.',
    'ip'                   => 'The :attribute must be a valid IP address.',
    'ipv4'                 => 'The :attribute must be a valid IPv4 address.',
    'ipv6'                 => 'The :attribute must be a valid IPv6 address.',
    'json'                 => 'The :attribute must be a valid JSON string.',
    'lt'                   => [
        'numeric' => 'The :attribute must be less than :value.',
        'file'    => 'The :attribute must be less than :value kilobytes.',
        'string'  => 'The :attribute must be less than :value characters.',
        'array'   => 'The :attribute must have less than :value items.',
    ],
    'lte'                  => [
        'numeric' => 'The :attribute must be less than or equal :value.',
        'file'    => 'The :attribute must be less than or equal :value kilobytes.',
        'string'  => 'The :attribute must be less than or equal :value characters.',
        'array'   => 'The :attribute must not have more than :value items.',
    ],
    'max'                  => [
        'numeric' => '「:attribute」は :max 以下で入力してください',
        'file'    => 'The :attribute may not be greater than :max kilobytes.',
        'string'  => '「:attribute」:max 文字以下で入力してください',
        'array'   => '「:attribute」は最大 :max つまでです',
        'store'   => '最大 :max つ以上は登録できません',
    ],
    'mimes'                => 'The :attribute must be a file of type: :values.',
    'mimetypes'            => 'The :attribute must be a file of type: :values.',
    'min'                  => [
        'numeric' => 'The :attribute must be at least :min.',
        'file'    => 'The :attribute must be at least :min kilobytes.',
        'string'  => '「:attribute」:min 文字以上で入力してください',
        'array'   => 'The :attribute must have at least :min items.',
    ],
    'not_in'               => '選択された 「:attribute」 が正しくありません',
    'not_regex'            => '「:attribute」 の形式が正しくありません',
    'numeric'              => '「:attribute」 は数値で入力してください',
    'present'              => 'The :attribute field must be present.',
    'regex'                => '「:attribute」 フォーマットが正しくありません.',
    'required'             => '「:attribute」は必須です',
    'required_if'          => 'The :attribute field is required when :other is :value.',
    'required_unless'      => 'The :attribute field is required unless :other is in :values.',
    'required_with'        => 'The :attribute field is required when :values is present.',
    'required_with_all'    => 'The :attribute field is required when :values is present.',
    'required_without'     => 'The :attribute field is required when :values is not present.',
    'required_without_all' => 'The :attribute field is required when none of :values are present.',
    'same'                 => 'The :attribute and :other must match.',
    'size'                 => [
        'numeric' => 'The :attribute must be :size.',
        'file'    => 'The :attribute must be :size kilobytes.',
        'string'  => 'The :attribute must be :size characters.',
        'array'   => 'The :attribute must contain :size items.',
    ],
    'string'               => '「:attribute」文字で入力してください',
    'timezone'             => 'The :attribute must be a valid zone.',
    'unique'               => '「:attribute」すでに登録されています',
    'uploaded'             => 'The :attribute failed to upload.',
    'url'                  => 'The :attribute format is invalid.',
    
    'ja_zen_hira'          => '「:attribute」全角ひらがなで入力してください',
    'expired'              => '「:attribute」有効期限が切れています\n有効期限は:limitです',
    
    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
        'email' => [
            'exists' => '指定された「メールアドレス」は承認されていません',
            'duplicate_account' => 'ログインIDとして使用しているアドレスは使用できません',
        ],
        'approval_token' => [
            'expired' => '「:attribute」の有効期限が切れています\n
                有効期限はお知らせから「:limit日」です\nお手数ですが、お問い合わせください。',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [
        'email' => 'メールアドレス',
        'password' => 'パスワード',
        'reminder' => 'ログイン継続',

        'device_name' => '端末名',
        'device_user_name' => '端末利用者名',
        'device_image' => '端末画像',
        'device_reset_word' => 'タイマーリセット名',
        'device_description' => '説明',
        'device_suspend_term' => '休止期間',
        'device_suspend_start_at' => '休止期間(開始日)',
        'device_suspend_end_at' => '休止期間(終了日)',
        'device_rule_id' => '通知ルール',
        'device_notification_targets' => '通知先',
        'device_last_reported_at' => '最終リセット日時',

        'rule_name' => 'ルール名',
        'rule_description' => '説明',
        'rule_time_limits' => 'タイマーリセット期限',
        'rule_notify_times' => '通知回数',
        'rule_message_id' => 'メッセージ',
        'rule_embedded_message' => '追加メッセージ',

        'contact_email' => '通知先メールアドレス',
        'contact_name' => '通知先名',
        'contact_description' => '説明',

        'profile_id' => 'ユーザーID',
        'profile_name' => '表示名',
    ],

];

