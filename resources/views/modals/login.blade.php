<modal-window v-bind:do-active="modalStates.login"
              v-bind:scrolled-position-y="posY"
              id="#cmp-modal-login">
    <custom-form-ajax
        action="{{ route('login') }}"
        method="post"
        v-bind:sender="'loginSubmit'"
        v-on:submit.prevent>
            <div class="form-items-s" slot="formParts" slot-scope="slotScopeProps">
            <div class="form-item-group">
                <label for="inputEmailLogin">{{ __('label.email') }}</label>
                <input type="text" name="email" id="inputEmailLogin" value="{{ old('email') }}"
                       placeholder="{{ __('label.placeholder.email') }}" required autocomplete="off"/>
                <span v-show="slotScopeProps.hasError('email')" class="text-form-notice text-attention">@{{ slotScopeProps.getErrorMsg('email') }}</span>
            </div>
            <div class="form-item-group">
                <label for="inputPasswordLogin">{{ __('validation.attributes.password') }}</label>
                <input type="password" name="password" id="inputPasswordLogin" value=""
                       required autocomplete="off" />
                <span v-show="slotScopeProps.hasError('password')" class="text-form-notice text-attention">@{{ slotScopeProps.getErrorMsg('password') }}</span>
            </div>

            <div class="form-item-group">
                <label for=""></label>
                <div class="form-checkbox-items w-100 text-right">
                    <input type="checkbox" name="remember" class="" id="rememberCheck" {{ old('remember') ? 'checked' : '' }}>
                    <label for="rememberCheck" >{{ __('label.reminder') }}</label>
                </div>
            </div>

            <div class="form-item-group">
                <ul class="layout-h-btn-box">
                    <li class="btn btn-theme-single-tint-orange-flip"><a href="#" @click.prevent="hideModal('login')">{{ __('label.btn.cancel') }}</a></li>
                    <li class=""><a href="#" id="loginSubmit"
                                    class="btn btn-theme-single-main"
                                    @click.prevent="slotScopeProps.doAjaxSubmit">{{ __('label.btn.ok') }}</a></li>
                </ul>
            </div>

            @if (Route::has('password.request'))
            <div class="w-100 text-right"><a href="{{ route('password.request') }}">{{ __('label.link.ask_password_reset') }}</a></div>
            @endif
        </div>
    </custom-form-ajax>
</modal-window>
