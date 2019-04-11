@extends('layouts.app')

@section('content')
<div class="container">

    @include('layouts.menu')

    <!--contents-->
    <div class="layout-contents">

        <div class="layout-section-title mt-section">
            <svg role="image" class="icon-prefix icon-m" aria-hidden="true"><use xlink:href="#mobile"></use></svg>
            <h2 class="title-with-icon-m"><span>たんまつ設定</span></h2>
        </div>

        <form method="post" action="#">
            <div class="layout-form-group-h-column">
                <device-image-picker
                    v-bind:initial-picked="1"
                    v-bind:preset-images="{{ json_encode($deviceForm->getPresetImages()) }}"></device-image-picker>
                {{--<div class="form-set-image-picker">--}}
                    {{--<div class="image-preview"><img src="" title="" /></div>--}}
                    {{--<button type="button" class="btn btn-inline-icon-only btn-theme-single-main"><svg role="image" class="icon-center"><use xlink:href="#edit"></use><title>edit profile image</title>></svg></button>--}}
                {{--</div>--}}
                <div class="form-items-s">
                    <div class="form-item-group">
                        <label for="" class="mark-required">端末名</label>
                        <input type="text" value="" placeholder="" />
                        <span class="text-form-notice form-notice-multi">１エラーメッセージなど</span>
                        <span class="text-form-notice form-notice-multi">２エラーメッセージなど</span>
                    </div>
                    <div class="form-item-group">
                        <label for="" class="mark-required">利用者名</label>
                        <input type="text" value="" placeholder="" />
                        <span class="text-form-notice">エラーメッセージなど</span>
                    </div>
                    <div class="form-item-group">
                        <label for="" class="mark-required">時限リセットボタン名</label>
                        <input type="text" value="" placeholder="リセット" />
                        <span class="text-form-notice">エラーメッセージなど</span>
                    </div>
                    <div class="form-item-group">
                        <label for="">説明</label><textarea name=""></textarea>
                    </div>
                    <div class="form-item-group state-disp-only">
                        <label for="">最終リセット日時</label>
                        <input type="text" value="" placeholder="" disabled />
                    </div>
                </div>
            </div>

            <div class="layout-form-group-h-column">
                <div class="form-items-l">
                    @component('components.help')
                        設定した期間中はタイマーのリセット期限が過ぎても通知は行われません。
                    @endcomponent
                    <div class="form-item-group">
                        <label for="">休止期間
                        </label>
                        <div class="form-items-align-h">
                            <flat-pickr
                                name="suspend_start_at"
                                :config="{ dateFormat: 'Y-m-d', defaultDate: '', minDate: '', locale: 'ja', disableMobile: true }"
                                value="" placeholder="">
                            </flat-pickr>
                            <span>から</span>
                            <flat-pickr name="suspend_end_at"
                                        :config="{ dateFormat: 'Y-m-d', defaultDate: '', locale: 'ja', disableMobile: true }"
                                        value="" placeholder="">
                            </flat-pickr>
                            <span>まで</span>
                        </div>
                        <instant-message
                            ref="infoMsgSuspend"
                            v-bind:theme="1">
                            設定した期間中はタイマーのリセット期限が過ぎても通知は行われません。
                        </instant-message>
                    </div>
                </div>
            </div>

            <div class="layout-form-group-h-column">
                <div class="form-items-l">
                    <div class="form-item-group">
                        <label for="ruleSelect">警報設定(COMPO)</label>
                        <custom-select name="rule"
                                       ref="ruleSelect"
                                       id="ruleSelect"
                                       v-bind:place-holder="'警報設定を選択'"
                                       v-bind:initial-selected="'2'"
                                       v-bind:item-structure="{{ json_encode(['text' => '', 'value' => '', 'reset_limit' => 0, 'max_notify_count' => 0]) }}"
                                       v-bind:initial-items="{{ json_encode([
                                    ['text' => '設定その１', 'value' => '1', 'reset_limit' => 24, 'max_notify_count' => 3],
                                    ['text' => '設定その２', 'value' => '2', 'reset_limit' => 12, 'max_notify_count' => 5],
                                    ['text' => '設定その３', 'value' => '3', 'reset_limit' => 48, 'max_notify_count' => 1],
                                 ]) }}">
                        </custom-select>
                    </div>

                    <select-sync-content
                        v-bind:root-class="'form-item-group state-disp-only'"
                        v-bind:ref-select="'ruleSelect'"
                        v-bind:initial-item="{{ json_encode(['text' => '', 'value' => '', 'reset_limit' => 0, 'max_notify_count' => 0]) }}">
                            <label slot="label" for="">リセット期限</label>
                            <input slot="content" slot-scope="item" type="text" :value="(item.item.value) ? item.item.reset_limit + '時間' : ''" placeholder="" disabled />
                    </select-sync-content>

                    <select-sync-content
                        v-bind:root-class="'form-item-group state-disp-only'"
                        v-bind:ref-select="'ruleSelect'"
                        v-bind:initial-item="{{ json_encode(['text' => '', 'value' => '', 'reset_limit' => 0, 'max_notify_count' => 0]) }}">
                        <label slot="label" for="">最大通知回数</label>
                        <input slot="content" slot-scope="item" type="text" :value="(item.item.value) ? item.item.max_notify_count + '回' : ''" placeholder="" disabled />
                    </select-sync-content>

                    {{--
                    <div class="form-item-group"><label for="">警報設定</label><select name=""><option value=""></option><option value="">設定その１</option><option value="">設定その２</option></select></div>

                    <div class="form-item-group state-disp-only"><label for="">リセット期限</label><input type="text" value="24時間" placeholder="" disabled /></div>
                    <div class="form-item-group state-disp-only"><label for="">最大通知回数</label><input type="text" value="3回" placeholder="" disabled /></div>
                    --}}

                    <pop-out-select v-bind:selector-id="'mailAddrSel'"
                                    v-bind:place-holder="'通知先アドレスを選択'"
                                    v-bind:max-out="1"
                                    ref="addressList"
                                     v-bind:initial-items="{{ json_encode([
                                        ['text' => 'xxx@example.com', 'value' => 'xxx', 'isPop' => true],
                                        ['text' => 'yyy@example.com', 'value' => 'yyy', 'isPop' => true],
                                        ['text' => 'zzz@example.com', 'value' => 'zzz', 'isPop' => false],
                                     ]) }}">
                        <label slot="before" for="mailAddrSel">警報メール通知先(COMPO)</label>
                        <span slot="after" class="text-form-notice text-attention">エラーメッセージなどエラーメッセージなどエラーメッセージなどエラーメッセージなどエラーメッセージなどエラーメッセージなどエラーメッセージなどエラーメッセージなどエラーメッセージなどエラーメッセージなどエラーメッセージなどエラーメッセージなど</span>
                    </pop-out-select>

                    <div class="form-item-group">
                        <label for=""></label>
                        <pop-in-list v-bind:ref-shared-list-compo="'addressList'"
                                     v-bind:hidden-slot-name="'notification-targets[]'"
                                     v-bind:initial-items="{{ json_encode([
                                        ['text' => 'xxx@example.com', 'value' => 'xxx', 'isPop' => true],
                                        ['text' => 'yyy@example.com', 'value' => 'yyy', 'isPop' => true],
                                        ['text' => 'zzz@example.com', 'value' => 'zzz', 'isPop' => false],
                                     ]) }}">
                            <div slot="list-content" class="text-with-icon" slot-scope="item">
                                <svg role="image" class="icon-prefix icon-l icon-circle-l" aria-hidden="true"><use xlink:href="#email_tilted"></use></svg>
                                <span>@{{ item.item.text }}</span>
                            </div>
                        </pop-in-list>
                    </div>

                    <div class="form-item-group">
                        <label for="">警報メール通知先</label>
                        <select name=""><option value=""></option><option value="">xxx@example.com</option><option value="">yyy@example.com</option></select>
                        <div class="form-item-right"><button type="button" class="btn btn-inline-icon-only btn-theme-single-green"><svg role="image" class="icon-center"><use xlink:href="#plus"></use><title>edit profile image</title>></svg></button></div>
                        <span class="text-form-notice text-attention">エラーメッセージなどエラーメッセージなどエラーメッセージなどエラーメッセージなどエラーメッセージなどエラーメッセージなどエラーメッセージなどエラーメッセージなどエラーメッセージなどエラーメッセージなどエラーメッセージなどエラーメッセージなど</span>
                    </div>

                    <div class="form-item-group">
                        <label for=""></label>
                        <ul>
                            <li class="list-item-with-btn">
                                <div class="text-with-icon"><svg role="image" class="icon-prefix icon-l icon-circle-l" aria-hidden="true"><use xlink:href="#email_tilted"></svg><span>xxx@example.com</span></div>
                                <button type="button" class="btn btn-inline-icon-only btn-theme-single-tint-orange-flip"><svg role="image" class="icon-center"><use xlink:href="#minus"></use><title>remove</title>></svg></button>
                            </li>
                            <li class="list-item-with-btn">
                                <div class="text-with-icon"><svg role="image" class="icon-prefix icon-l icon-circle-l" aria-hidden="true"><use xlink:href="#email_tilted"></svg><span>xxxxxxxxxxxxxxxxxxxxxxxxxxxxxx@example.com</span></div>
                                <button type="button" class="btn btn-inline-icon-only btn-theme-single-tint-orange-flip"><svg role="image" class="icon-center"><use xlink:href="#minus"></use><title>remove</title>></svg></button>
                            </li>
                        </ul>
                    </div>

                    <div class="form-item-group">
                        <ul class="layout-h-btn-box">
                            <li class="btn btn-theme-single-main"><a href="#">OK</a></li>
                            <li class="btn btn-theme-single-tint-orange-flip"><a href="#">キャンセル</a></li>
                        </ul>
                    </div>

                    <div class="form-item-group">
                        <ul class="layout-h-btn-box">
                            <li class="btn btn-theme-single-tint-orange-flip"><a href="#">削除</a></li>
                        </ul>
                    </div>

                </div><!--.form-items-l-->
            </div><!--//layout-form-group-h-column-->
        </form>

    </div><!--/.layout-contents-->

</div><!-- /.container -->
@endsection
