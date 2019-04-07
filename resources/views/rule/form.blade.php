@extends('layouts.app')

@section('pageTitle', __('label.menu.rule'))

@section('content')
<div class="container">

@include('layouts.menu')

    <!--contents-->
    <div class="layout-contents">

        <div class="layout-section-title mt-section">
            <svg role="img" class="icon-prefix icon-m" aria-hidden="true"><use xlink:href="#alert"></use></svg>
            <h2 class="title-with-icon-m"><span>{{ __('label.menu.rule') }}</span></h2>
        </div>

        <div class="layout-panel panel-flat text-center">
            <a href="{{ route('about', ['open' => 'how_alert']) }}#how_alert" target="_blank" class="btn btn-theme-single-tint-orange-flip">もしタイマーの期限が切れた場合？</a>
        </div>

        <form method="post"
              action="{{ (!$rule->id) ? route('rule.create') : route('rule.edit', ['id' => $rule->id]) }}">
            @csrf
            <input type="hidden" name="rule_total" value="0">

            <div class="layout-form-group-h-column">
                <div class="form-items-l">

                    <div class="form-item-group">
                        <label for="inputName" class="mark-required">{{ __('validation.attributes.rule_name') }}</label>
                        <input type="text" name="rule_name" id="inputName" value="{{ $errors->any() ? old('rule_name') : $rule->name }}" placeholder="" />
                        @if ($errors->has('rule_name'))
                            <span class="text-form-notice text-attention">{{ $errors->first('rule_name') }}</span>
                        @endif
                    </div>

                    <div class="form-item-group">
                        <label for="inputDescription">{{ __('validation.attributes.rule_description') }}</label>
                        <textarea name="rule_description" id="inputDescription">{{ $errors->any() ? old('rule_description') : $rule->description }}</textarea>
                        @if ($errors->has('rule_description'))
                            <span class="text-form-notice text-attention">{{ $errors->first('rule_description') }}</span>
                        @endif
                    </div>

                    <div class="form-item-group">
                        <label for="selectTimeLimits" class="mark-required">
                            {{ __('validation.attributes.rule_time_limits') }}
                        </label>
                        <select name="rule_time_limits" id="selectTimeLimits">
                            @foreach($ruleForm::getTimeLimitsValues() as $value)
                                <option value="{{ $value }}" {{ old('rule_time_limits', $rule->time_limits) == $value ? 'selected' : ''}}>{{ $value / 24 }}{{ __('label.unit.day') }}</option>
                            @endforeach
                        </select>
                        @if ($errors->has('rule_time_limits'))
                            <span class="text-form-notice text-attention">{{ $errors->first('rule_time_limits') }}</span>
                        @endif
                    </div>

                    <div class="form-item-group">
                        <label for="selectNotifyTimes">{{ __('validation.attributes.rule_notify_times') }}</label>
                        <select name="rule_notify_times" id="selectNotifyTimes">
                            @foreach($ruleForm::getNotifyTimesValues() as $value)
                                <option value="{{ $value }}" {{ old('rule_notify_times', $rule->notify_times) == $value ? 'selected' : ''}}>{{ $value }}{{ __('label.unit.times') }}</option>
                            @endforeach
                        </select>
                        @if ($errors->has('rule_notify_times'))
                            <span class="text-form-notice text-attention">{{ $errors->first('rule_notify_times') }}</span>
                        @endif
                    </div>

                    <div class="form-item-group">
                        <label for="inputEmbeddedMessage">{{ __('validation.attributes.rule_embedded_message') }}</label>
                        <textarea name="rule_embedded_message" id="inputEmbeddedMessage">{{ $errors->any() ? old('rule_embedded_message') : $rule->embedded_message }}</textarea>
                        @if ($errors->has('rule_embedded_message'))
                            <span class="text-form-notice text-attention">{{ $errors->first('rule_embedded_message') }}</span>
                        @endif
                    </div>

                    <div class="form-item-group">
                        <label for="selectMessage" class="mark-required">{{ __('validation.attributes.rule_message_id') }}</label>
                        <custom-select name="rule_message_id"
                                       ref="msgSelect"
                                       id="msgSelect"
                                       v-bind:place-holder="'{{ __('label.placeholder.rule.message_id') }}'"
                                       v-bind:initial-selected="'{{ $errors->any() ? old('rule_message_id', '') : $rule->message_id ?? '' }}'"
                                       v-bind:item-structure="{{ json_encode(['text' => '', 'value' => '', 'subject' => '']) }}"
                                       v-bind:initial-items="{{ json_encode($ruleForm->messagesToArray()) }}">
                        </custom-select>

                        <div class="form-item-right">
                            <button type="button"
                                    class="btn btn-inline-icon-only btn-theme-single-green"
                                    @click.prevent="showMailPreviewModal(['selectTimeLimits', 'selectNotifyTimes', 'inputEmbeddedMessage'])">
                                <svg role="img" class="icon-center"><use xlink:href="#email_loupe"></use><title>Preview</title></svg>
                            </button>
                        </div>
                        @if ($errors->has('rule_message_id'))
                            <span class="text-form-notice text-attention">{{ $errors->first('rule_message_id') }}</span>
                        @endif
                    </div>

                    <div class="form-item-group">
                        <ul class="layout-h-btn-box">
                            <li class=""><button type="submit" class="btn btn-theme-single-main" @click="showModal('loading')">{{ __('label.btn.ok') }}</button></li>
                            <li class="btn btn-theme-single-tint-orange-flip"><a href="{{ route('rule.list') }}">{{ __('label.btn.cancel') }}</a></li>
                        </ul>
                    </div>

                    @if($rule->id)
                    <div class="form-item-group">
                        <ul class="layout-h-btn-box">
                            @if(count($rule->device) === 0)
                            <li class="btn btn-theme-single-tint-orange-flip">
                                <a href="#"
                                   @click.prevent="showConfirmationModel('deleteForm', {{ json_encode(explode('\n', __('message.confirm.delete'))) }})">{{ __('label.btn.delete') }}</a>
                            </li>
                            @else
                            @component('components.help', ['type' => 'alert'])
                                このルールが設定されている端末があるので削除できません
                            @endcomponent
                            <li class="btn btn-disabled">{{ __('label.btn.delete') }}</li>
                            @endif
                        </ul>
                    </div>
                    @endif

                    @component('components.ie_flex_stretch') @endcomponent

                </div><!--.form-items-l-->
            </div><!--//layout-form-group-h-column-->

        </form>

        @if($rule->id)
            @if (count($rule->device) === 0)
                <form id="deleteForm" action="{{ route('rule.delete', ['id' => $rule->id]) }}" method="post" style="display: none;">{{ csrf_field() }}</form>
            @endif
        @endif

    </div><!--/.layout-contents-->

</div><!-- /.container -->
@endsection

@section('add-on-content')
    @include('modals.mail_preview')
@endsection
