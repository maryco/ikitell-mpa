@extends('layouts.app')

@section('pageTitle', __('label.page_title.reset_password'))

@section('content')
<div class="container">
    <div class="layout-contents">
        <form method="post" action="{{ route('password.email') }}">
            @csrf
            <div class="layout-form-group-h-column">
                <div class="form-items-l">
                    @if (session('status')) <p>{{ session('status') }}</p> @endif

                    <div class="form-item-group">
                        <label for="inputEmail">{{ __('label.email') }}</label>
                        <input type="text" name="email" id="inputEmail" value="{{ old('email') }}" placeholder="{{ __('label.placeholder.email') }}" required />
                        @if ($errors->has('email'))
                            <span class="text-form-notice text-attention">{{ $errors->first('email') }}</span>
                        @endif
                    </div>

                    <div class="form-item-group">
                        <ul class="layout-h-btn-box">
                            <li>
                                <button type="submit" class="btn btn-theme-single-main">
                                    {{ __('label.btn.request_reset_password') }}
                                </button>
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

@section('add-on-content')
    @include('modals.login')
@endsection
