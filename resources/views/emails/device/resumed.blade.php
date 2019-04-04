@extends('emails.layout')

@section('body')
休止期間が終了したため、「{{ $device->name }}」の休止状態を解除したことをお知らせします。<br>
<br>
解除日時：{{ $device->reported_at ?? $device->getReportedDateTime(false)->format('Y-m-d H:i') }}<br>
<br>
@component('mail::button', ['url' => $actionUrl])
{{ $actionText }}
@endcomponent

@endsection
