@extends('emails.layout')

@section('body')
{{--You are receiving this email because we received a password reset request for your account.--}}
パスワードリセットのご依頼を受け付けました。<br>
以下にアクセスして新しいパスワードを設定してください。

@component('mail::button', ['url' => $actionUrl])
{{ $actionText }}
@endcomponent

@endsection
