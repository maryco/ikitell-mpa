@extends('layouts.app')

@section('pageTitle', __('label.page_title.welcome'))

@section('content')
<div class="container">

    @include('layouts.menu')

    <!--contents-->
    <div class="layout-contents">

        <div class="layout-section-title">
            <h2 class="text-italic">{{ __('label.notice_log') }}</h2>
        </div>

        @if(count($logs) === 0)
        <div class="layout-panel panel-with-picture bg-grey-light">
            <div class="panel-body text-center">
                <p>{{ __('message.support.empty_list') }}</p>
                <a href="{{ url('/') }}" class="btn btn-theme-single-green">{{ __('label.menu.home') }}</a>
            </div>
        </div>
        @else
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

        {{ $logs->links() }}
        @endif

    </div><!--/.layout-contents-->

</div><!-- /.container -->
@endsection
