@extends('emails.layout')

@section('body')
@if($isCopy)
----------------------<br>
以下の内容で通知先に承認依頼メールを送信しました。<br>
(注：承認用URLはダミーです)<br>
----------------------<br>
@endif

{{ $contact->name }}様<br>
<br>
{{ $user->name ?: __('label.default.user.name') }}様が「安否確認依頼メール」の送信先として、このメールアドレスの利用許可を求めています。<br>
承諾される場合は、お手数ですが下記のURLにアクセスしてください。<br>

@component('mail::button', ['url' => $actionUrl])
{{ $actionText }}
@endcomponent

@component('mail::button', ['url' => route('about')])
本サービスについて
@endcomponent
<br>
<ul>
<li>URLの有効期限は{{ config('specs.verify_limit.contacts', 1) / 60 }}時間です。</li>
</ul>
@endsection
