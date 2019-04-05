@extends('emails.layout')

@section('body')
{{--Please click the button below to verify your email address.--}}
アカウントの登録を受け付けました。<br>
下記のURLにアクセスして登録を完了してください。

@component('mail::button', ['url' => $actionUrl])
{{ $actionText }}
@endcomponent

<ul>
<li>承認が完了するまで以下の機能制限があります</li>
<li><ul style="list-style: none;">
    <li>1.「通知先メールアドレス」の新規登録</li>
    <li>2.タイマーリセットの期限が過ぎてもお知らせメールが送信されません。</li>
</ul></li>
<li>URLの有効期限は{{ config('specs.verify_limit.account') }}分です。</li>
</ul>

{{--If you did not create an account, no further action is required.--}}

@endsection
