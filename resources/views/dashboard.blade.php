@extends('layouts.app')

@section('pageTitle', __('label.menu.home'))

@section('content')
<div class="container">

    @include('layouts.menu')

    <!--contents-->
    <div class="layout-contents">

        @foreach($devices as $device)
            <dashboard-panel :is-first="{{ ($loop->first) ? 'true' : 'false' }}"
                             :initial-device-info="{{ json_encode($device->toArray()) }}">
            </dashboard-panel>
        @endforeach

        <div class="layout-section-title">
            <h2 class="text-italic">{{ __('label.notice_log') }}</h2>
        </div>

        <ul class="list-simple">
        @foreach($logs as $log)
            <li class="list-item-link">
                <a href="{{ route('notice.history.alert.detail', ['id' => $log->id]) }}">
                    <svg role="img" class="icon-prefix icon-m"><use xlink:href="#email"></use></svg>
                    {{ $log->getDate('created_at') }}
                    <span class="text-matter">{{ $log->name }}さんに通知メールが送信されました。</span>
                </a>
            </li>
        @endforeach
        </ul>

        @if($logs && count($logs) > 0)
        <div class="w-grid m-h-center text-right">
            <a class="btn btn-theme-main-flip" href="{{ route('notice.history.alert.search') }}">一覧へ</a>
        </div>
        @else
        <div class="layout-panel panel-with-picture bg-grey-light">
            <div class="panel-body text-center">
                <p>{{ __('message.support.empty') }}</p>
            </div>
        </div>
        @endif

    </div><!--/.layout-contents-->

</div><!-- /.container -->
@endsection
