@extends('layouts.app')

@section('pageTitle', __('label.page_title.welcome'))

@section('content')
<div class="container">

    <!--contents-->
    <div class="layout-contents bg-grey-light">
        <div class="layout-welcome">
            <div class="concept">
                <svg role="img"><use xlink:href="#logo_with_line"></use></svg>
                <p>I'm still alive!</p>
            </div>
        </div>

        <div class="layout-panel panel-with-picture panel-flat" style="margin-top: 50px;">
            <div class="panel-body text-center">
                <div class="frame-box">
                    <p class="frame-box text-readable">{{ config('app.name') }}は「おひとりさま」の不測の事態を誰かにお知らせするための自己防衛サービスです。</p>
                    <ul class="layout-h-btn-box">
                        <li class="btn btn-theme-single-green"><a href="{{ url('/about#outline') }}">サービスについて</a></li>
                        <li class="btn btn-theme-single-green"><a href="{{ url('/about#usage') }}">使い方</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="layout-panel panel-with-picture panel-flat">
            <div class="panel-body">
                <ul class="layout-h-btn-box">
                    {{--TODO: fix activateButton() ajax issue (app.js)--}}
                    {{--<li class="btn btn-theme-main"><a href="#" @click.prevent="showModal('register')">{{ __('label.btn.register_account') }}</a></li>--}}
                    <li class="btn btn-theme-main"><a href="{{ route('register') }}">{{ __('label.btn.register_account') }}</a></li>
                @if(is_seems_ie())
                    {{--TODO: Fix bug IE not redirect after get ajax response.--}}
                    <li class="btn btn-theme-main-flip"><a href="{{ route('login') }}">{{ __('label.btn.login') }}</a></li>
                @else
                    <li class="btn btn-theme-main-flip"><a href="#" @click.prevent="showModal('login')">{{ __('label.btn.login') }}</a></li>
                @endif
                </ul>
            </div>
        </div>
    </div><!--./contents-->

</div><!-- /.container -->
@endsection

@section('add-on-content')
    @include('modals.login')
{{--    @include('modals.register')--}}
@endsection
