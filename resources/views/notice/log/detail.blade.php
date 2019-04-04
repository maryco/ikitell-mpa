@extends('layouts.app')

@section('pageTitle', __('label.page_title.welcome'))

@section('content')
<div class="container">

    @include('layouts.menu')

    <!--contents-->
    <div class="layout-contents">

        <div class="layout-section-title">
            <h2 class="text-italic">{{ __('label.section_title.notice_log.outline') }}</h2>
        </div>

        <div class="layout-panel panel-with-picture bg-white">
            <div class="panel-body">
                <dl class="panel-data-list">
                    <div class="data-item-pair">
                        <dt>{{ __('validation.attributes.contact_name') }}</dt>
                        <dd>{{ $log->name }}</dd>
                    </div>
                    <div class="data-item-pair">
                        <dt>{{ __('validation.attributes.contact_email') }}</dt>
                        <dd>{{ $log->email }}</dd>
                    </div>
                    <div class="data-item-pair">
                        <dt>{{ __('label.send_date') }}</dt>
                        <dd>{{ $log->getDate('created_at') }}</dd>
                    </div>
                    <div class="data-item-pair">
                        <dt>{{ __('label.notice_times') }}</dt>
                        <dd>{{ $log->getNotifyCountString() }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="layout-section-title">
            <h2 class="text-italic">{{ __('label.section_title.notice_log.content') }}</h2>
        </div>

        <div class="layout-panel panel-with-picture">
            <div class="panel-body">
                <p class="text-attention">下記は文章の内容です。実際はプレビューで確認した形式で送信しています。</p>
                <textarea disabled style="width: 100%; height: 200px;">{{ $log->content }}</textarea>
            </div>
        </div>

    </div><!--/.layout-contents-->

</div><!-- /.container -->
@endsection
