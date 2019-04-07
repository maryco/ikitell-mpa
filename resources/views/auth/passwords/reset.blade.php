@extends('layouts.app')

@section('pageTitle', __('label.page_title.reset_password'))

@section('header')
@endsection

@section('content')
<div class="container">

    <div class="layout-contents">
        <form method="post" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div class="layout-form-group-h-column">
                <div class="form-items-l m-h-center">
                    <div class="form-item-group">
                        <label for="inputEmail">{{ __('label.email') }}</label>
                        <input type="text" name="email" id="inputEmail" value="{{ $email ?? old('email') }}"
                               placeholder="{{ __('label.placeholder.email') }}" required autofocus/>
                        @if ($errors->has('email'))
                            <span class="text-form-notice text-attention">{{ $errors->first('email') }}</span>
                        @endif
                    </div>

                    <div class="form-item-group">
                        <label for="inputPassword">{{ __('label.password') }}</label>
                        <input type="password" name="password" id="inputPassword" value="" required/>
                        @if ($errors->has('password'))
                            <span class="text-form-notice text-attention">{{ $errors->first('password') }}</span>
                        @endif
                    </div>

                    <div class="form-item-group">
                        <label for="inputPasswordConf">{{ __('label.password_confirm') }}</label>
                        <input type="password" name="password_confirmation" id="inputPasswordConf" value=""
                               required/>
                    </div>

                    <div class="form-item-group">
                        <ul class="layout-h-btn-box">
                            <li class="">
                                <button type="submit" class="btn btn-theme-single-main">{{ __('label.btn.reset_password') }}</button>
                            </li>
                        </ul>
                    </div>

                    @component('components.ie_flex_stretch') @endcomponent

                </div><!--//.form-items-l-->
            </div><!--//.layout-form-group-h-column-->
        </form>
    </div><!--//.layout-contents-->
</div>
@endsection
