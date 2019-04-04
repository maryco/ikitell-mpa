<modal-window v-bind:do-active="modalStates.register"
              v-bind:scrolled-position-y="posY"
              id="#cmp-modal-register">
    <custom-form-ajax
        action="{{ route('register') }}"
        method="post"
        v-bind:sender="'registerSubmit'"
        v-on:submit.prevent>
        <div class="form-items-s" slot="formParts" slot-scope="{ doAjaxSubmit, hasError, getErrorMsg }">
            <div class="form-item-group">
                <label for="inputEmail" class="mark-required">{{ __('label.email') }}</label>
                <input type="text" name="email" id="inputEmail" value="{{ old('email') }}" placeholder="{{ __('label.placeholder.email') }}"
                       required autofocus autocomplete="off" />
                <span v-show="hasError('email')" class="text-form-notice text-attention">@{{ getErrorMsg('email') }}</span>
            </div>

            <div class="form-item-group">
                <label for="inputPassword" class="mark-required">{{ __('label.password') }}</label>
                <input type="password" name="password" id="inputPassword" value=""
                       required autocomplete="off" />
                <span v-show="hasError('password')" class="text-form-notice text-attention">@{{ getErrorMsg('password') }}</span>
            </div>

            <div class="form-item-group">
                <label for="inputPasswordConf">{{ __('label.password_confirm') }}</label>
                <input type="password" name="password_confirmation" id="inputPasswordConf" value=""
                       required autocomplete="off" />
            </div>

            <div class="form-item-group">
                <ul class="layout-h-btn-box">
                    {{--<li class="btn btn-theme-single-tint-orange-flip"><a href="#" @click.prevent="hideModal('register')">{{ __('label.btn.cancel') }}</a></li>--}}
                    <li>
                        <p class="form-checkbox-items w-100">
                            {{--TODO: fix activateButton() ajax issue (app.js)--}}
                            <input type="checkbox" name="acceptTerms" class="" id="acceptCheck" @change="activateButton('acceptCheck', 'registerSubmit')">
                            <label for="acceptCheck">
                                「利用規約」に同意して
                                <a href="{{ route('terms') }}" target="_blank"><svg role="img" class="icon badge icon-white badge-info"><use xlink:href="#info"></use><title>Info</title></svg></a></label>
                        </p>
                    </li>

                    <li><a href="#" id="registerSubmit"
                           class="btn btn-theme-single-main btn-disabled"
                           disabled
                           @click.prevent="doAjaxSubmit()">{{ __('label.btn.register_account') }}</a></li>
                </ul>
            </div>
        </div>
    </custom-form-ajax>
</modal-window>
