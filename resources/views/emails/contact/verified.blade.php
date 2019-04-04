@extends('emails.layout')

@section('body')
承認待ちの通知先メールアドレスが承認されました。<br>
<br>
@component('mail::button', ['url' => $actionUrl])
{{ $actionText }}
@endcomponent
<br>
@endsection
