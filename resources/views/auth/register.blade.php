@extends('layouts.app')

@section('pageTitle', __('label.page_title.register_account'))

@section('content')
<div class="container">
    <div class="layout-contents">

        <form method="post" action="{{ route('register') }}">
            @csrf
            <div class="layout-form-group-h-column">
                <div class="form-items-l">
                    <div class="form-item-group">
                        <label for="inputEmail" class="mark-required">{{ __('label.email') }}</label>
                        <input type="text" name="email" id="inputEmail" value="{{ old('email') }}"
                               placeholder="{{ __('label.placeholder.email') }}"
                               required autofocus autocomplete="off" />
                        @if ($errors->has('email'))
                            <span class="text-form-notice text-attention">{{ $errors->first('email') }}</span>
                        @endif
                    </div>

                    <div class="form-item-group">
                        <label for="inputPassword" class="mark-required">{{ __('label.password') }}</label>
                        <input type="password" name="password" id="inputPassword" value=""
                               required autocomplete="off" />
                        @if ($errors->has('password'))
                            <span class="text-form-notice text-attention">{{ $errors->first('password') }}</span>
                        @endif
                    </div>

                    <div class="form-item-group">
                        <label for="inputPasswordConf">{{ __('label.password_confirm') }}</label>
                        <input type="password" name="password_confirmation" id="inputPasswordConf" value=""
                               required autocomplete="off" />
                    </div>

                    <div class="form-item-group">
                        <ul class="layout-h-btn-box">
                            <li>
                                <p class="form-checkbox-items w-100">
                                    <input type="checkbox" name="acceptTerms" class="" id="acceptCheck" @click="activateButton('acceptCheck', 'btnRegister')">
                                    <label for="acceptCheck" >
                                        「利用規約」に同意して
                                        <a href="{{ route('terms') }}" target="_blank"><svg role="img" class="icon badge icon-white badge-info"><use xlink:href="#info"></use><title>Info</title></svg></a></label>
                                </p>
                            </li>
                            <li class="">
                                <button id="btnRegister" type="submit" class="btn btn-theme-single-main btn-disabled" disabled>
                                    {{ __('label.btn.register') }}
                                </button>
                            </li>
                        </ul>
                    </div>

                    @component('components.ie_flex_stretch') @endcomponent
                </div>
            </div>
        </form>

    </div>
</div>
@endsection

@section('add-on-content')
    @include('modals.login')
@endsection

