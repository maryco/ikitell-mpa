<template>
    <div class="form-items-s">
        <transition name="animatecss-appear-fade"
                    enter-active-class="animated fadeIn fast"
                    leave-active-class="animated fadeOut faster">
        <div class="form-item-group form-item-group-theme-frame" v-show="errorMessage">
            <svg role="img" class="icon icon-circle-l icon-white bg-orange icon-frame-title"><use xlink:href="#caution"></use></svg>
            <span class="text-form-notice text-attention text-center">{{ errorMessage }}</span>
        </div>
        </transition>
        <div class="form-item-group form-item-group-theme-frame" v-show="messages">
            <!--<svg role="img" class="icon icon-circle-l icon-white bg-orange icon-frame-title"><use xlink:href="#caution"></use></svg>-->
            <span class="text-form-notice text-center" v-for="message in messages">{{ message }}</span>
        </div>
        <div class="form-item-group">
            <ul class="layout-h-btn-box" v-if="isOnlyMessage">
                <li class="btn btn-theme-single-main"><a href="#" @click.prevent="dismiss">{{ labelOk }}</a></li>
            </ul>
            <ul class="layout-h-btn-box" v-else>
                <li class="btn btn-theme-single-tint-orange-flip"><a href="#" @click.prevent="dismiss">{{ labelCancel }}</a></li>
                <li class="btn btn-theme-single-main"><a href="#" @click.prevent="doSubmit()">{{ labelOk }}</a></li>
            </ul>
        </div>
    </div>
</template>

<script>
    export default {
        name: "ConfirmationPanel",

        data: function () {
            return {
                targetFormId: '',
                messages: [],
                isOnlyMessage: this.onlyMessage,
                errorMessage: '',
                labelOk: this.label.ok,
                labelCancel: this.label.cancel
            };
        },

        props: {
            label: {
                ok: {
                    default: 'OK'
                },
                cancel: {
                    default: 'CANCEL'
                }
            },
        },

        methods: {
            init: function (formId, messages, onlyMessage = false) {
                this.targetFormId = formId;
                this.messages = messages;
                this.isOnlyMessage = onlyMessage;
            },

            dismiss: function () {
                this.errorMessage = '';
                this.$root.$emit('hide-modal', 'confirmation');
            },

            doSubmit: function () {
                try {
                    if (!this.targetFormId) {
                        throw "UndefinedFormId";
                    }

                    var form = document.getElementById(this.targetFormId);
                    if (!form || form.tagName.toLowerCase() !== 'form') {
                        throw "InvalidForm";
                    }

                    this.$root.$emit('show-loading');
                    form.submit();

                } catch (e) {
                    //this.errorMessage = 'Target is not a form.';
                    //this.errorMessage = 'Not found submit target.';
                    this.errorMessage = e;
                }
            }
        }
    }
</script>
