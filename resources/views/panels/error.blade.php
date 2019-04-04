@extends('layouts.app')
{{--
    NOTE:
    - The default appearance no header, no menu, show footer.
    - The Footer show/hide is switch by the route name.
    @see \App\Exceptions\Handler::hideFooter
--}}

@section('pageTitle', __('label.page_title.error'))

@section('header')
@endsection

@section('content')
    @include('svg.logo_variety')

    <div class="container bg-orange">

    <!--contents-->
    <div class="layout-contents">

        <div class="layout-panel panel-with-picture">
            <span class="panel-badge">{{ $exception->getStatusCode() }}</span>
            <div class="panel-body text-center">
                <svg role="img" class="visual-emotion"><use xlink:href="#logo_panic"></use></svg>
                <p>{{ $exception->getMessage() ?: $defaultMessage }}</p>
                @if(!$hideFooter ?? true)
                <ul class="layout-h-btn-box">
                    <li class="btn btn-theme-single-tint-orange"><a href="{{ url('/') }}">{{ __('label.menu.home') }}</a></li>
                </ul>
                @endif
            </div>
        </div>

        @if(App::environment('local'))
        <div class="layout-panel panel-with-picture">
            <div class="panel-body">
                <p>STATUS:{{ $exception->getStatusCode() }}</p>
                <p>MESSAGE:{{ $exception->getMessage() }}</p>
                <p>EXCEPTION:{{ $exception->getTraceAsString() }}</p>
            </div>
        </div>
        @endif

    </div><!--/.layout-contents-->

    </div><!-- /.container -->
@endsection

@if($hideFooter ?? false)
    @section('footer')
    @endsection
@endif
