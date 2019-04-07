@extends('layouts.app')

@section('pageTitle', __('label.page_title.login'))

@section('content')
<div class="container">

    <div class="layout-contents">
        <form method="post" action="{{ route('login') }}">
            @csrf
            <div class="layout-form-group-h-column">
                <div class="form-items-l">

                    <div class="form-item-group">
                        <label for="inputEmail">{{ __('label.email') }}</label>
                        <input type="text" name="email" id="inputEmail" value="{{ old('email') }}" placeholder="{{ __('label.placeholder.email') }}" required autofocus />
                        @if ($errors->has('email'))
                        <span class="text-form-notice text-attention">{{ $errors->first('email') }}</span>
                        @endif
                    </div>
                    <div class="form-item-group">
                        <label for="inputPassword">{{ __('label.password') }}</label>
                        <input type="password" name="password" id="inputPassword" value="" required />
                        @if ($errors->has('password'))
                        <span class="text-form-notice text-attention">{{ $errors->first('password') }}</span>
                        @endif
                    </div>

                    <div class="form-item-group">
                        <ul class="layout-h-btn-box">
                            <li>
                                <button type="submit" class="btn btn-theme-single-main">{{ __('label.btn.login') }}</button>
                            </li>
                            <li>
                                <p class="form-checkbox-items w-100">
                                    <input type="checkbox" name="remember" class="" id="rememberCheck" {{ old('remember') ? 'checked' : '' }}>
                                    <label for="rememberCheck" >{{ __('label.reminder') }}</label>
                                </p>
                            </li>
                        </ul>
                    </div>

                    @if (Route::has('password.request'))
                    <div class="form-item-right">
                        <a class="" href="{{ route('password.request') }}">
                            {{ __('label.link.ask_password_reset') }}
                        </a>
                    </div>
                    @endif

                    @component('components.ie_flex_stretch') @endcomponent

                </div><!--.form-items-l-->
            </div><!--//.layout-form-group-h-column-->

        </form>

    </div><!--//.layout-contents-->
</div><!--//.container-->
@endsection
