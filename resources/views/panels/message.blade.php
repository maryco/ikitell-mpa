@extends('layouts.app')
{{--
    NOTE:
    - The default appearance no header, no menu, show footer.
--}}

@section('pageTitle', $pageTitle ?? 'Ikitell')

@if($hideHeader ?? true) @section('header') @endsection @endif

@section('content')
    @include('svg.logo_variety')

    <div class="container {{ $containerClass ?? 'bg-main-theme' }}">

    <!--contents-->
    <div class="layout-contents">

        <div class="layout-panel panel-with-picture">
            <div class="panel-body text-center">
                <svg role="img" class="visual-emotion"><use xlink:href="#{{ $logoRef ?? 'logo_normal' }}"></use></svg>

                @foreach($messages ?? [] as $message)
                <p class="text-center">{{ $message }}</p>
                @endforeach

                @if($linkItems ?? false)
                <ul class="layout-h-btn-box">
                @foreach($linkItems ?? [] as $linkItem)
                    <li class="btn {{ $linkItem['btnThemeClass'] ?? 'btn-theme-single-tint-orange' }}">
                        <a href="{{ $linkItem['href'] }}">{{ $linkItem['text'] }}</a></li>
                @endforeach
                </ul>
                @endif
            </div>
        </div><!--/.layout-panel -->

    </div><!--/.layout-contents-->

    </div><!--/.container -->
@endsection

@if($hideFooter ?? false) @section('footer') @endsection @endif
