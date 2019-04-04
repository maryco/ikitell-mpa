@extends('layouts.app')

@section('pageTitle', __('label.page_title.welcome'))

@section('content')
    <div class="container container-lab">

        @include('layouts.menu')

        <div class="layout-contents" style="padding-top: 0;">

            <ul class="list-simple">
                <li class="list-item-link"><a href="{{ route('lab.device') }}">端末一覧 (要ログイン) </a></li>
            </ul>

            <div class="layout-section-title mt-section">
                <svg role="img" class="icon-prefix icon-m" aria-hidden="true"><use xlink:href="#alert"></use></svg>
                <h2 class="title-with-icon-m"><span>確認モーダルテスト</span></h2>
            </div>

            <div class="layout-form-group-h-column">
                {{--FIXME: 中身が小さいと'w-100'を使用しないと「width = 100%」にならない--}}
                <div class="form-items-s w-100">
                    <div class="form-item-group">
                        <ul class="layout-h-btn-box">
                            <li><button type="button" class="btn btn-theme-single-green" @click.prevent="showConfirmationModel('issueAlertForm', ['何かします。','よろしいですか？'])">確認モーダル１</button></li>
                            <li><button type="button" class="btn btn-theme-single-green" @click.prevent="showConfirmationModel('', ['何もしません。','よろしいですか？'], true)">確認モーダル２</button></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="layout-section-title mt-section">
                <svg role="img" class="icon-prefix icon-m" aria-hidden="true"><use xlink:href="#alert"></use></svg>
                <h2 class="title-with-icon-m"><span>SelectSyncCompo</span></h2>
            </div>

            <div class="layout-form-group-h-column">
                <div class="form-items-s w-100">
                    <div class="form-item-group">
                        <label for="syncSelect" class="mark-required">あ行</label>
                        <custom-select name="a"
                                       ref="syncSelect"
                                       id="syncSelect"
                                       v-bind:place-holder="'選択してください'"
                                       v-bind:initial-selected="'13'"
                                       v-bind:item-structure="{{ json_encode(['value' => '', 'text' => '']) }}"
                                       v-bind:initial-items="{{ json_encode([
                                        ['value' => '11', 'text' => 'あめんぼ'],
                                        ['value' => '12', 'text' => 'あかいな'],
                                        ['value' => '13', 'text' => 'あいうえお'],
                                       ]) }}">
                        </custom-select>
                    </div>

                    <select-sync-content
                        v-bind:root-class="'form-item-group state-disp-only'"
                        v-bind:ref-select="'syncSelect'"
                        v-bind:initial-item="{{ json_encode(['value' => '', 'text' => '']) }}">
                        <label slot="label" for="">あ行</label>
                        <input slot="content" slot-scope="item" type="text" :value="(item.item.value) ? item.item.text : ''" placeholder="" disabled />
                    </select-sync-content>
                </div>
            </div>

            <div class="layout-section-title mt-section">
                <svg role="img" class="icon-prefix icon-m" aria-hidden="true"><use xlink:href="#alert"></use></svg>
                <h2 class="title-with-icon-m"><span>メールプレビューテスト</span></h2>
            </div>

            <div class="layout-form-group-h-column">
                <div class="form-items-s">

                    <div class="form-item-group">
                        <label for="">タイマーリセット期限：</label>
                        <select name="rule_time_limits" id="selectTimeLimits">
                            <option value=""></option>
                            <option value="100">100</option>
                        </select>
                    </div>

                    <div class="form-item-group">
                        <label for="">埋め込みメッセージ：</label>
                        <textarea name="emb_message" id="txtEmbMessage"></textarea>
                    </div>

                    <div class="form-item-group">
                        <label for="">１行テキスト：</label>
                        <input type="text" name="dummy_text" id="txtDummy" />
                    </div>

                    <div class="form-item-group">
                        <label for="">メールプレビューテスト：</label>
                        <custom-select name="rule_message_id"
                                       ref="msgSelect"
                                       id="msgSelect"
                                       v-bind:place-holder="'メッセージテンプレートを選択'"
                                       v-bind:initial-selected="'101'"
                                       v-bind:item-structure="{{ json_encode(['text' => '', 'value' => '', 'subject' => '']) }}"
                                       v-bind:initial-items="{{ json_encode([
                                       ['text' => 'メッセージ００１', 'value' => '101', 'subject' => '[Subject]メッセージ001'],
                                       ['text' => 'メッセージ００２', 'value' => '102', 'subject' => '[Subject]メッセージ002'],
                                       ['text' => 'メッセージ００３', 'value' => '103', 'subject' => '']
                                   ]) }}">
                        </custom-select>
                        <button type="button" class="btn btn-theme-single-green w-100"
                                @click.prevent="showMailPreviewModal(['selectTimeLimits', 'txtEmbMessage', 'txtDummy'])">メールプレビュー</button>
                    </div>

                </div>
            </div>

            {{--<div style="padding: 20px;">--}}
                {{--<form id="issueAlertForm" method="post" action="{{ route('lab.alert', ['id' => 2 ]) }}">--}}
                    {{--{{ csrf_field() }}--}}
                    {{--<select name="device">--}}
                        {{--<option value="">DeviceName</option>--}}
                    {{--</select>--}}
                    {{--<ul>--}}
                        {{--<li>--}}
                            {{--<button>アラート生成</button>--}}
                        {{--</li>--}}
                    {{--</ul>--}}
                {{--</form>--}}
            {{--</div>--}}

        </div><!-- /.layout-contents -->

    </div><!-- /.container -->
@endsection

@section('add-on-content')
    @include('modals.mail_preview', ['previewUrl' => route('lab.mail.preview')]);
@endsection
