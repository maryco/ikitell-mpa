<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="seems-ie" content="{{ is_seems_ie() ? 1 : 0 }}">
    <meta content="@yield('pageTitle') | Ikitell-Me" name="title">
    <meta content="Ikitell.meは「おひとりさま」の不測の事態を誰かにお知らせするための自己防衛サービスです" name="description">
    <meta content="ひとり暮らし,孤独死" name="keywords">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="apple-touch-icon" type="image/png" href="{{ asset('apple-touch-icon-180x180.png') }}">
    <link rel="icon" type="image/vnd.microsoft.icon" href="{{ asset('favicon.ico') }}" >
    <link rel="icon" href="{{ asset('icon.svg') }}" type="image/svg+xml" sizes="any">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:400,400i,500,700">
    <link rel="stylesheet" href="https://fonts.googleapis.com/earlyaccess/notosansjp.css">
    <link rel="stylesheet" href="{{ mix('/css/styles.css') }}">

    <title>@yield('pageTitle') | Ikitell.me - I'm still alive!</title>
</head>

<body>

@include('svg.icons')
@include('svg.logo')

<div id="app">

@section('header')
<header class="{{ !auth()->check() ? 'header-theme-dark' : '' }}">
    @auth
    <div class="header-logo"><a href="{{ route('home') }}"><svg role="img"><use xlink:href="#logo"></use></svg></a></div>
    <button name="menu" type="button" class="btn-toggle-menu act-toggle-menu"
            id="btnSpToggleMenu"
            @click="toggleMenu()">Menu</button>
    @else
        @if(starts_with(\Route::currentRouteName(), 'lab.'))
            <button name="menu" type="button" class="btn-toggle-menu act-toggle-menu"
                id="btnSpToggleMenu"
                @click="toggleMenu()">DevMenu</button>
        @else
            <div class="navi">
                <ul class="">
                    <li class="navi-item"><a href="{{ route('register') }}">{{ __('label.btn.register_account') }}</a></li>
                @if(is_seems_ie())
                    {{--TODO: Fix bug IE not redirect after get ajax response.--}}
                    <li class="navi-item"><a href="{{ route('login') }}">{{ __('label.btn.login') }}</a></li>
                @else
                    <li class="navi-item"><a href="#" @click.prevent="showModal('login')">{{ __('label.btn.login') }}</a></li>
                @endif
                </ul>
            </div>
        @endif
    @endauth
</header>
@show

@if(isset($appInforms) && count($appInforms) > 0)
<app-inform-panel
    v-bind:messages="{{ json_encode($appInforms) }}">
</app-inform-panel>
@endif

<instant-message
    ref="appInstantMsg"
    v-bind:message="appInstMsg"
    v-bind:theme="appInstMsgTheme"
    v-bind:active-time="appInstMsgActiveTime">
</instant-message>

@yield('content')

@section('footer')
<footer>
    <a href="{{ url('/') }}"><svg role="img"><use xlink:href="#logo_with_line"></use><title>Logo</title></svg></a>
    <ul>
        <li><a href="{{ route('about') }}">{{ __('label.link.about') }}</a></li>
        <li><a href="{{ route('terms') }}">{{ __('label.link.terms') }}</a></li>
        <li><a href="{{ route('contact') }}">{{ __('label.link.contact') }}</a></li>
    </ul>
    <p>Copyright 2020 {{ \Illuminate\Support\Str::upper(config('app.name')) }} <span class="sp-break">All Rights Reserved</span></p>
</footer>
@show

@yield('add-on-content')

@include('modals.confirmation')
@include('modals.loading')

</div><!--//#app-->

<script src="https://www.promisejs.org/polyfills/promise-6.1.0.min.js"></script>
<script type="text/javascript" src="{{ mix('js/app.js') }}"></script>
</body>

</html>
