<template>
    <form v-bind:method="formAttr.method" v-bind:action="formAttr.action">
        <p v-show="responseMessage"
           class="text-center"
           :class="{'text-attention': responseIsError}">{{ responseMessage }}</p>
        <slot name="formParts"
              :doAjaxSubmit="doAjaxSubmit"
              :hasError="hasError"
              :getErrorMsg="getErrorMsg">

        </slot>
    </form>
</template>

<script>
    export default {
        name: "CustomFormAjax",

        data: function () {
            return {
                formAttr: {
                    method: this.method,
                    action: this.action,
                },
                formEls: {},
                senderElem: undefined,
                senderActiveClass: '',
                responseIsError: false,
                responseMessage: ''
            };
        },

        props: {
            action: {
                type: String,
                required: true,
                default: '/'
            },
            method: {
                type: String,
                default: 'post'
            },
            sender: {
                required: true,
                type: String,
            },
            senderDisabledClass: {
                type: String,
                default: 'btn btn-disabled'
            },
            senderRunningClass: {
                type: String,
                default: 'state-running'
            },
            slotScopeProps: {
                doAjaxSubmit: function () { return this.doAjaxSubmit() },
                hasError: function () { return this.hasError() },
                getErrorMsg: function () {  return this.getErrorMsg() }
            }
        },

        mounted: function () {
            this.initFormEls();

            this.senderElem = document.getElementById(this.sender);
            if (this.senderElem !== undefined) {
                this.senderActiveClass = this.senderElem.getAttribute('class');
            }
        },

        methods: {
            initFormEls: function () {
                this.buildData();
            },

            doAjaxSubmit: function () {
                this.buildData();
                var data = this.onlyFormValues();
                var token = document.querySelector('meta[name="csrf-token"]');

                if (!this.switchSenderState(false)) {
                    return false;
                }

                this.$root.$emit('show-loading');

                // Clear current response data.
                this.responseIsError = false;
                this.responseMessage = '';

                var vm = this;
                axios
                    .post(vm.formAttr.action, data, {
                        headers: {
                            'X-CSRF-TOKEN': token.getAttribute('content'),
                        }
                    })
                    .then(function (res) {
                        // TODO: Implements when it's to be need.
                        //console.log('Response(status) : ' + res.status);
                        //console.log('Response(data) : ' + res.data.message);

                        // For iOS 9 finally issue(?)
                        vm.$root.$emit('dismiss-loading');
                        vm.switchSenderState(true);
                    })
                    .catch(function (err) {
                        // For iOS 9 finally issue(?)
                        vm.$root.$emit('dismiss-loading');
                        vm.switchSenderState(true);

                        if (!err.response) {
                            // Unknown error?
                            vm.responseIsError = true;
                            vm.responseMessage = 'なんらかのエラーが発生しました (CODE:???)';
                            return;
                        }

                        // '303' assume success, then redirect to the specified location
                        if (parseInt(err.response.status) === 303 && err.response.data.hasOwnProperty('location')) {
                            window.location.href = err.response.data.location;
                            return;
                        }

                        // '422' Validation error (Laravel)
                        if (parseInt(err.response.status) === 422) {
                            vm.setValidationErrors(err.response);
                            return;
                        }

                        //console.log(err.response.status + ':' + err.response.statusText);
                        vm.responseIsError = true;
                        vm.responseMessage = 'なんらかのエラーが発生しました (CODE:'+err.response.status+')';
                    })
                    .finally(function () {
                        // NOTE: 'finally' is not working iOS 9?
                        vm.$root.$emit('dismiss-loading');
                        vm.switchSenderState(true);
                    });
            },

            switchSenderState: function (active) {
                if (this.senderElem === undefined) {
                    return false;
                }

                if (active) {
                    this.senderElem.setAttribute('disabled', false);
                    this.senderElem.setAttribute('class', this.senderActiveClass);
                } else {
                    if (this.senderElem.getAttribute('disabled') === true) {
                        return false;
                    }

                    this.senderElem.setAttribute('disabled', true);
                    this.senderElem.setAttribute('class',
                        this.senderDisabledClass + ' ' + this.senderRunningClass);
                }

                return true;
            },

            buildData: function () {
                /**
                 * TODO:
                 *  - Support <select> multiple
                 */
                var namedEls = this.$el.querySelectorAll('[name]');
                var data = {};

                Array.prototype.forEach.call(namedEls, function (el, index) {

                    if (!data.hasOwnProperty(el.name)) {
                        data[el.name] = {
                            value: '',
                            error: ''
                        };
                    }

                    if (el.tagName.toLowerCase() === 'select') {
                        var ops = el.querySelectorAll('option:checked');

                        Array.prototype.forEach.call(ops, function (op, index) {
                            //console.log('option: ' + op.textContent + ' = ' + op.value);
                            data[el.name].value = op.value;
                        });
                    } else if (el.type.toLowerCase() === 'checkbox') {
                        if (!Array.isArray(data[el.name].value)) {
                            data[el.name].value = [];
                        }

                        if (el.checked) {
                            data[el.name].value.push(el.value);
                        }
                    } else if (el.type.toLowerCase() === 'radio') {
                        if (el.checked) {
                            data[el.name].value = el.value;
                        }
                    } else {
                        // text/textarea
                        data[el.name].value = el.value;
                    }
                });

                this.formEls = data;
            },

            onlyFormValues: function () {
                // If use application/x-www-form-urlencoded
                // see: https://github.com/axios/axios#using-applicationx-www-form-urlencoded-format
                //var params = new URLSearchParams();

                var values = {};

                if (this.formEls === undefined) {
                    return params;
                }

                for (var propName in this.formEls) {
                    if(this.formEls.hasOwnProperty(propName)) {
                        values[propName] = this.formEls[propName].value;
                    }
                }
                //console.log('Params = ' + params);
                //console.log('Values = ' + values);

                return values;
            },

            hasError: function (targetName) {
                return this.getErrorMsg(targetName).length > 0;
            },

            getErrorMsg: function (targetName) {
                for (var propName in this.formEls) {
                    if (propName !== targetName) {
                        continue;
                    }

                    if(this.formEls.hasOwnProperty(propName)) {
                        var tmp = this.formEls[propName];
                        return (tmp.hasOwnProperty('error') && tmp.error.length > 0) ? tmp.error : '';
                    }
                }

                return '';
            },

            /**
             * Set error messages from
             * the laravel validation error response.
             * NOTE:
             * - 'Validate error' = '422'
             * - Using only first error message of each attributes.
             *
             * @param axiosRes
             */
            setValidationErrors: function (axiosRes)
            {
                if (!axiosRes.hasOwnProperty('status') || parseInt(axiosRes.status) !== 422) {
                    return;
                }

                if (!axiosRes.hasOwnProperty('data')) {
                    return;
                }

                for(var propName in this.formEls) {
                    if (!this.formEls.hasOwnProperty(propName)) {
                        continue;
                    }
                    if (!axiosRes.data.errors.hasOwnProperty((propName))) {
                        continue;
                    }

                    this.formEls[propName].error = axiosRes.data.errors[propName][0];
                }

                // console.log(' status: ' + axiosRes.status);
                // console.log(' data message: ' + axiosRes.data.message);
                // console.log(' data errors: ' + axiosRes.data.errors);
            }
        }
    }

</script>
