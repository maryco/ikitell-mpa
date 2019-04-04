@extends('layouts.app')

@section('pageTitle', __('label.page_title.verify_email'))

@section('content')

@include('svg.logo_variety')

<div class="container">

    <div class="layout-contents">

        <div class="layout-panel panel-with-picture {{ session('resent') ? 'bg-pale-green' : 'bg-grey-light' }}">
            <div class="panel-body text-center">
                @if (session('resent'))
                <svg role="img" class="visual-emotion"><use xlink:href="#logo_normal"></use></svg>
                <p>{{ __('message.app.resent') }}</p>
                <ul class="layout-h-btn-box">
                    <li class="">
                        <a href="{{ route('home') }}" class="btn btn-theme-main-flip">{{ __('label.menu.home') }}</a>
                    </li>
                </ul>
                @else
                <svg role="img" class="visual-emotion"><use xlink:href="#logo_unhappy"></use></svg>
                <p class="frame-box">{{ __('message.support.resent_verify.1') }}</p>
                <div class="frame-box">
                    <p>{{ __('message.support.resent_verify.2') }}</p>
                    <a href="{{ route('verification.resend') }}" class="btn btn-theme-single-main">{{ __('label.btn.resent_verify') }}</a>
                </div>
                <ul class="layout-h-btn-box">
                    <li class="">
                        <a href="{{ route('home') }}" class="btn btn-theme-main-flip">{{ __('label.menu.home') }}</a>
                    </li>
                </ul>
                @endif
            </div>
        </div>

    </div><!--/.layout-contents -->

</div><!--/.container -->
@endsection
