@extends('layouts.app')

@section('pageTitle', __('label.menu.profile'))

@section('content')
<div class="container">

    @include('layouts.menu')

    <!--contents-->
    <div class="layout-contents">

        <div class="layout-section-title mt-section">
            <svg role="img" class="icon-prefix icon-m" aria-hidden="true"><use xlink:href="#profile"></use><title>Profile</title></svg>
            <h2 class="title-with-icon-m"><span>{{ __('label.menu.profile') }}</span></h2>
        </div>

        <form method="post" action="{{ route('profile.edit') }}">
            @csrf
            <div class="layout-form-group-h-column">
                <div class="form-set-image-picker">
                    <div class="image-preview"><svg role="img" class="device-icon-mobile-1"><use xlink:href="#profile"></use><title>Profile</title></svg></div>
                    {{--<button type="button" class="btn btn-inline-icon-only btn-theme-single-main"><svg role="img" class="icon-center"><use xlink:href="#edit"></use><title>Edit profile image</title>></svg></button>--}}
                </div>
                <div class="form-items-s">
                    <div class="form-item-group state-disp-only">
                        <label for="">{{ __('validation.attributes.profile_id') }}</label>
                        <input type="text" value="{{ $profile->email }}" placeholder="" disabled/>
                    </div>
                    @component('components.help') 通知メール内で使用されます @endcomponent
                    <div class="form-item-group">
                        <label for="inputName">{{ __('validation.attributes.profile_name') }}
                        </label>
                        <input name="profile_name" id="inputName" type="text" value="{{ $errors->any() ? old('profile_name') : $profile->name }}" placeholder=""/>
                        @if ($errors->has('profile_name'))
                            <span class="text-form-notice text-notice">{{ $errors->first('profile_name') }}</span>
                        @endif
                    </div>

                    <div class="form-item-right">
                        <button type="submit" class="btn btn-theme-single-main" @click="showModal('loading')">{{ __('label.btn.update') }}</button>
                    </div>

                    <div class="form-item-right mt-section">
                        <a href="#"
                           class="btn btn-inline-no-icon btn-theme-single-green"
                           @click.prevent="showConfirmationModel('passwordRequestForm', {{ json_encode(explode('\n', __('message.confirm.password_request'))) }})">{{ __('label.link.reset_password') }}</a>
                    </div>
                    <div class="form-item-right">
                        <a href="#"
                           class="btn btn-inline-no-icon btn-theme-single-tint-orange-flip"
                           @click.prevent="showConfirmationModel('deleteForm', {{ json_encode(explode('\n', __('message.confirm.delete'))) }})">{{ __('label.link.delete_account') }}</a>
                    </div>
                </div>
            </div>
        </form>

        <form id="deleteForm" action="{{ route('account.delete') }}" method="post" style="display: none;">{{ csrf_field() }}</form>
        <form id="passwordRequestForm" action="{{ route('account.password.email') }}" method="post" style="display: none;">
            {{ csrf_field() }}
            <input type="hidden" name="email" value="{{ $profile->email }}">
        </form>

    </div><!--/.layout-contents-->

</div><!-- /.container -->
@endsection
