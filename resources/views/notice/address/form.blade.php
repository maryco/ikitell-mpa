@extends('layouts.app')

@section('pageTitle', __('label.menu.notice_address'))

@section('content')
<div class="container">

@include('layouts.menu')

    <!--contents-->
    <div class="layout-contents">

        <div class="layout-section-title mt-section">
            <svg role="img" class="icon-prefix icon-m" aria-hidden="true"><use xlink:href="#email"></use><title>E-Mail</title></svg>
            <h2 class="title-with-icon-m"><span>{{ __('label.menu.notice_address') }}</span></h2>
        </div>

        <form method="post"
              action="{{ (!$contact->id) ? route('notice.address.create') : route('notice.address.edit', ['id' => $contact->id]) }}">
            @csrf
            <input type="hidden" name="contact_total" value="0">

            <div class="layout-form-group-h-column">
                <div class="form-items-l">
                    @component('components.help')
                        承認依頼メールの送信後および承認完了後は変更できません。
                    @endcomponent
                    <div class="form-item-group{{ !$contact->enableEditEmail() ? ' state-disp-only' : '' }}">
                        <label for="inputEmail" class="mark-required">{{ __('validation.attributes.contact_email') }}</label>
                        @if($contact->enableEditEmail())
                            <input type="text" name="contact_email" id="inputEmail" value="{{ $errors->any() ? old('contact_email') : $contact->email }}" placeholder="{{ __('label.placeholder.email') }}" />
                        @else
                            <input type="text" name="" id="inputEmail" value="{{ $contact->email }}" placeholder="" disabled />
                            <input type="hidden" name="contact_email" value="{{ $contact->email }}" />
                        @endif

                        @if ($errors->has('contact_email'))
                            <span class="text-form-notice text-attention">{{ $errors->first('contact_email') }}</span>
                        @endif
                        @if ($errors->has('contact_total'))
                            <span class="text-form-notice text-attention">{{ $errors->first('contact_total') }}</span>
                        @endif
                    </div>

                    <div class="form-item-group">
                        <label for="inputName" class="mark-required">{{ __('validation.attributes.contact_name') }}</label>
                        <input type="text" name="contact_name" id="inputName" value="{{ $errors->any() ? old('contact_name') : $contact->name }}" placeholder="" />
                        @if ($errors->has('contact_name'))
                            <span class="text-form-notice text-attention">{{ $errors->first('contact_name') }}</span>
                        @endif
                    </div>

                    <div class="form-item-group">
                        <label for="inputDescription">{{ __('validation.attributes.contact_description') }}</label>
                        <textarea name="contact_description" id="inputDescription">{{ $errors->any() ? old('contact_description') : $contact->description }}</textarea>
                        @if ($errors->has('contact_description'))
                            <span class="text-form-notice text-attention">{{ $errors->first('contact_description') }}</span>
                        @endif
                    </div>

                    <div class="form-item-group">
                        <ul class="layout-h-btn-box">
                            <li class=""><button type="submit" class="btn btn-theme-single-main" @click="showModal('loading')">{{ __('label.btn.ok') }}</button></li>
                            <li class="btn btn-theme-single-tint-orange-flip"><a href="{{ route('notice.address.list') }}">{{ __('label.btn.cancel') }}</a></li>
                        </ul>
                    </div>

                    @if($contact->id)
                    @component('components.help', ['type' => 'alert'])
                        【注意】承認依頼メールは連続して送信できません。({{ config('specs.send_contacts_verify_interval') }}分に1回までです)
                    @endcomponent
                    <div class="form-item-group state-disp-only">
                        <label for="">{{ __('承認依頼メール送信日') }}</label>
                        <input type="text" name="" id="" value="{{ $contact->getDate('send_verify_at') }}" placeholder="" disabled />

                        <div class="form-item-right">
                            @if($contact->enableSendVerify())
                            <ul class="layout-h-btn-box">
                                <li class="">
                                    <a href="#"
                                       @click.prevent="showMailPreviewModal(['inputName'])"
                                       class="btn btn-inline-icon-only btn-theme-single-green">
                                        <svg role="img" class="icon-center" aria-hidden="true">
                                            <use xlink:href="#email_loupe"><title>Preview</title></use>
                                        </svg></a>
                                </li>
                                <li>
                                    <a href="#"
                                       @click.prevent="showConfirmationModel('sendVerifyForm', {{ json_encode(explode('\n', __('message.confirm.send_verify_request'))) }})"
                                       class="btn btn-inline-no-icon btn-theme-single-green">{{ __('label.btn.send_verify_request') }}</a>
                                </li>
                            </ul>
                            @else
                                <a href="#"
                                   @click.prevent=""
                                   class="btn btn-inline-no-icon btn-disabled">{{ __('label.btn.send_verify_request') }}</a>
                                @component('components.help', ['type' => 'alert'])
                                    {{ __('message.error.send_verify_request', ['minutes' => config('specs.send_contacts_verify_interval')]) }}
                                @endcomponent
                            @endif
                        </div>
                    </div>

                    <div class="form-item-group state-disp-only">
                        <label for="">{{ __('label.verified_date') }}</label>
                        <input type="text" name="" id="" value="{{ $contact->getDate('email_verified_at') }}" placeholder="" disabled />
                    </div>

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

        @if($contact->id)
            <form id="deleteForm" action="{{ route('notice.address.delete', ['id' => $contact->id]) }}" method="post" style="display: none;">{{ csrf_field() }}</form>
            <form id="sendVerifyForm" action="{{ route('notice.address.verify.send', ['id' => $contact->id]) }}" method="post" style="display: none;">{{ csrf_field() }}</form>
        @endif

    </div><!--/.layout-contents-->

</div><!-- /.container -->
@endsection

@section('add-on-content')
    @if($contact->id)
        @include('modals.mail_preview',
        ['previewUrl' => route('notice.address.verify.preview', ['id' => $contact->id])])
    @endif
@endsection
