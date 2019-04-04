{{--
    @see vendor/laravel/framework/src/Illuminate/Mail/resources/views/html/layout.blade.php
--}}
@component('mail::layout')
{{--Header (nested table)--}}
@slot('header')
    @component('mail::header', ['url' => config('app.url')]) {{ config('app.name') }} @endcomponent
@endslot

{{--BODY (nested table)--}}
@yield('body')
@include('emails.signature')
{{--//BODY--}}

{{--Footer (nested table)--}}
@slot('footer')
    @component('mail::footer')
        Copyright (c) {{ today()->year }} {{ config('app.name') }} All Rights Reserved.
    @endcomponent
@endslot
@endcomponent
