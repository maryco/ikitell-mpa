@extends('emails.alert.layout')

@section('body')
{{ $to['name'] }}様<br>
<br>
<span style="font-weight:bold;">{{ $content['device_user_name'] }}</span>さんから<span style="font-weight:bold;color:red">{{ $content['rule_time_limits'] / 24 }}日以上、音信がありません。</span><br>
お手数ですが、{{ $content['device_user_name'] }}さんに<strong>できるだけ早めに</strong>連絡を試みてください。<br>
@if ($content['rule_embedded_message'])
<br>
以下は{{ $content['device_user_name'] }}さんからのメッセージです。<br>
--------------------<br>
{{ $content['rule_embedded_message'] }}<br>
--------------------<br>
@endif
<br>
よろしくお願いいたします。<br>
<br>
<ul>
<li>{{ $content['device_user_name'] }}さんからの音信がない場合、この通知メールは最大で<em>{{ $content['rule_notify_times'] }}回</em>送信されます</li>
</ul>
<br>
@endsection

{{--@component('mail::button', ['url' => $actionUrl])--}}
{{--{{ $actionText }}--}}
{{--@endcomponent--}}
