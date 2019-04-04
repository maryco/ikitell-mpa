@extends('layouts.app')

@section('pageTitle', __('label.page_title.welcome'))

{{-- NOTE: Activate this comment out section if hide header. --}}
{{--@section('header')--}}
{{--@endsection--}}

@section('content')
    @include('svg.logo_variety')

<div class="container">

    @include('layouts.menu')

    <!--contents-->
    <div class="layout-contents">

        <div class="layout-panel panel-with-picture bg-pale-green">
            <div class="panel-body text-center">
                <svg role="img" class="visual-emotion"><use xlink:href="#logo_unhappy"></use><title>logo</title></svg>
                <p class="frame-box">お問い合わせは未対応です</p>
            </div>
        </div>
    </div><!--/.layout-contents-->

</div><!-- /.container -->
@endsection

{{-- NOTE: Activate this comment out section if hide footer. --}}
{{--@section('footer')--}}
{{--@endsection--}}

@section('add-on-content')
    @guest @include('modals.login') @endguest
@endsection
