@extends('layouts.app')

@section('pageTitle', __('label.menu.device'))

@section('content')
<div class="container">

    @include('layouts.menu')

    <!--contents-->
    <div class="layout-contents">

        <div class="layout-section-title mt-section">
            <svg role="img" class="icon-prefix icon-m" aria-hidden="true"><use xlink:href="#mobile"></use></svg>
            <h2 class="title-with-icon-m"><span>{{ __('label.menu.device') }}</span></h2>
        </div>

        <form method="post"
              action="{{ (!$device->id) ? route('device.create') : route('device.edit', ['id' => $device->id]) }}">
            @csrf
            <input type="hidden" name="device_total" value="0">
            <div class="layout-form-group-h-column">
                <div class="form-set-image-picker">
                    <device-image-picker
                        v-bind:initial-picked="{{ $errors->any() ? old('device_image_preset') : $device->getImage()['value'] ?? 1 }}"
                        v-bind:preset-images="{{ json_encode($deviceForm->getPresetImages()) }}">
                    </device-image-picker>
                    @if ($errors->has('device_image_preset'))
                        <span class="text-form-notice text-attention">{{ $errors->first('device_image_preset') }}</span>
                    @endif
                </div>
                <div class="form-items-s">
                    <div class="form-item-group">
                        <label for="inputName" class="mark-required">{{ __('validation.attributes.device_name') }}</label>
                        <input type="text" name="device_name" id="inputName" value="{{ $errors->any() ? old('device_name') : $device->name }}" placeholder="" />
                        @if ($errors->has('device_name'))
                        <span class="text-form-notice text-attention">{{ $errors->first('device_name') }}</span>
                        @endif
                    </div>
                    @component('components.help')
                        通知メール内で使用する名前です。未設定の場合はプロフィールの名前が使用されます
                    @endcomponent
                    <div class="form-item-group">
                        <label for="inputUserName" class="">
                            {{ __('validation.attributes.device_user_name') }}
                        </label>
                        <input type="text" name="device_user_name" id="inputUserName" value="{{ $errors->any() ? old('device_user_name') : $device->user_name }}" placeholder="" />
                        @if ($errors->has('device_user_name'))
                            <span class="text-form-notice text-attention">{{ $errors->first('device_user_name') }}</span>
                        @endif
                    </div>
                    @component('components.help')
                        タイマーをリセットするボタンに表示されます(例：「元気です」)
                    @endcomponent
                    <div class="form-item-group">
                        <label for="inputResetWord">
                            {{ __('validation.attributes.device_reset_word') }}
                        </label>
                        <input type="text" name="device_reset_word" id="inputResetWord"
                               value="{{ $errors->any() ? old('device_reset_word') : $device->reset_word }}"
                               placeholder="{{ __('label.placeholder.device.reset_word') }}"/>
                        @if ($errors->has('device_reset_word'))
                            <span class="text-form-notice text-attention">{{ $errors->first('device_reset_word') }}</span>
                        @endif
                    </div>
                    <div class="form-item-group">
                        <label for="inputDescription">{{ __('validation.attributes.device_description') }}</label>
                        <textarea name="device_description" id="inputDescription">{{ $errors->any() ? old('device_description') : $device->description }}</textarea>
                        @if ($errors->has('device_description'))
                            <span class="text-form-notice text-attention">{{ $errors->first('device_description') }}</span>
                        @endif
                    </div>
                    <div class="form-item-group state-disp-only">
                        <label for="">{{ __('validation.attributes.device_last_reported_at') }}</label>
                        <input type="text" value="{{ $device->reported_at ? $device->getReportedDateTime(false)->format('Y-m-d H:i') : ''}}" placeholder="" disabled />
                    </div>

                    @component('components.ie_flex_stretch') @endcomponent
                </div>
            </div>

            <div class="layout-form-group-h-column">
                <div class="form-items-l">
                    @component('components.help')
                        設定した期間中はタイマーのリセット期限が過ぎても通知は行われません。
                    @endcomponent
                    <div class="form-item-group">
                        <label for="inputSuspendStartAt">{{ __('validation.attributes.device_suspend_term') }}
                        </label>
                        <div class="form-items-align-h">
                            <flat-pickr
                                name="device_suspend_start_at"
                                :config="{ dateFormat: 'Y-m-d', defaultDate: '', minDate: '', locale: 'ja', disableMobile: true }"
                                value="{{ $errors->any() ? old('device_suspend_start_at') : $device->suspend_start_at }}" placeholder="">
                            </flat-pickr>
                            <span>{{ __('label.from') }}</span>
                            <flat-pickr name="device_suspend_end_at"
                                        :config="{ dateFormat: 'Y-m-d', defaultDate: '', locale: 'ja', disableMobile: true }"
                                        value="{{ $errors->any() ? old('device_suspend_end_at') : $device->suspend_end_at }}" placeholder="">
                            </flat-pickr>
                            <span>{{ __('label.to') }}</span>
                        </div>
                        @if ($errors->has('device_suspend_start_at'))
                            <span class="text-form-notice text-attention">{{ $errors->first('device_suspend_start_at') }}</span>
                        @endif
                        @if ($errors->has('device_suspend_end_at'))
                            <span class="text-form-notice text-attention">{{ $errors->first('device_suspend_end_at') }}</span>
                        @endif
                    </div>

                    @component('components.ie_flex_stretch') @endcomponent
                </div>
            </div>

            <div class="layout-form-group-h-column">
                <div class="form-items-l">
                    <div class="form-item-group">
                        <label for="ruleSelect" class="mark-required">{{ __('label.notice_rule') }}</label>
                        <custom-select name="device_rule_id"
                                       ref="ruleSelect"
                                       id="ruleSelect"
                                       v-bind:place-holder="'{{ __('label.placeholder.device.notice_rule') }}'"
                                       v-bind:initial-selected="'{{ $errors->any() ? old('device_rule_id', '') : $device->rule_id ?? '' }}'"
                                       v-bind:item-structure="{{ json_encode(\Illuminate\Support\Arr::get($deviceForm::FRONT_MODELS, 'rule', [])) }}"
                                       v-bind:initial-items="{{ json_encode($rules) }}">
                        </custom-select>
                        @if ($errors->has('device_rule_id'))
                            <span class="text-form-notice text-attention">{{ $errors->first('device_rule_id') }}</span>
                        @endif
                    </div>

                    <select-sync-content
                        v-bind:root-class="'form-item-group state-disp-only'"
                        v-bind:ref-select="'ruleSelect'"
                        v-bind:initial-item="{{ json_encode(\Illuminate\Support\Arr::get($deviceForm::FRONT_MODELS, 'rule', [])) }}">
                            <label slot="label" for="">{{ __('validation.attributes.rule_time_limits') }}</label>
                            <input slot="content" slot-scope="item" type="text" :value="(item.item.value) ? item.item.time_limits + '{{ __('label.unit.day') }}' : ''" placeholder="" disabled />
                    </select-sync-content>

                    <select-sync-content
                        v-bind:root-class="'form-item-group state-disp-only'"
                        v-bind:ref-select="'ruleSelect'"
                        v-bind:initial-item="{{ json_encode(\Illuminate\Support\Arr::get($deviceForm::FRONT_MODELS, 'rule', [])) }}">
                        <label slot="label" for="">{{ __('validation.attributes.rule_notify_times') }}</label>
                        <input slot="content" slot-scope="item" type="text" :value="(item.item.value) ? item.item.notify_times + '{{ __('label.unit.times') }}' : ''" placeholder="" disabled />
                    </select-sync-content>

                    <pop-out-select v-bind:selector-id="'mailAddrSel'"
                                    v-bind:place-holder="'{{ __('label.placeholder.device.notice_address') }}'"
                                    v-bind:max-out="{{ Auth::user()->getMaxNotifyTargets() }}"
                                    ref="addressList"
                                    v-bind:initial-items="{{ json_encode($contacts) }}">
                        <label slot="before" for="mailAddrSel" class="">{{ __('label.notice_address') }}</label>
                        @if ($errors->has('device_notification_targets'))
                            <span slot="after" class="text-form-notice text-attention">{{ $errors->first('device_notification_targets') }}</span>
                        @endif
                    </pop-out-select>

                    <div class="form-item-group">
                        <label for=""></label>
                        <pop-in-list v-bind:ref-shared-list-compo="'addressList'"
                                     v-bind:hidden-slot-name="'device_notification_targets[]'"
                                     v-bind:initial-items="{{ json_encode($contacts) }}">
                            <div slot="list-content" class="text-with-icon" slot-scope="item">
                                <svg role="img" class="icon-prefix icon-l icon-circle-l" aria-hidden="true"><use xlink:href="#email_tilted"></use><title>email</title></svg>
                                <span>@{{ item.item.text }}</span>
                            </div>
                        </pop-in-list>
                    </div>

                    <div class="form-item-group">
                        <ul class="layout-h-btn-box">
                            <li class=""><button type="submit" class="btn btn-theme-single-main" @click="showModal('loading')">{{ __('label.btn.ok') }}</button></li>
                            <li class="btn btn-theme-single-tint-orange-flip"><a href="{{ route('device.list') }}">{{ __('label.btn.cancel') }}</a></li>
                        </ul>
                    </div>

                    @if($device->id)
                    <div class="form-item-group">
                        <ul class="layout-h-btn-box">
                            <li class="btn btn-theme-single-tint-orange-flip">
                                <a href="#"
                                   @click.prevent="showConfirmationModel('deleteForm', {{ json_encode(explode('\n', __('message.confirm.delete'))) }})">{{ __('label.btn.delete') }}</a>
                            </li>
                        </ul>
                    </div>
                    @endif

                    @component('components.ie_flex_stretch') @endcomponent

                </div><!--.form-items-l-->
            </div><!--//layout-form-group-h-column-->
        </form>

        @if($device->id)
            <form id="deleteForm" action="{{ route('device.delete', ['id' => $device->id]) }}" method="post" style="display: none;">{{ csrf_field() }}</form>
        @endif

    </div><!--/.layout-contents-->

</div><!-- /.container -->
@endsection
