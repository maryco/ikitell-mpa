<template>
    <div>
        <p v-show="subject">件名：{{ subject }}</p>
        <p class="text-attention text-xs">[注]メール内のリンクはダミーです</p>
        <iframe src="" name="mail-render-iframe" class="mail-preview-body"></iframe>
        <form :action="baseUrl"
              :id="formId"
              method="post"
              target="mail-render-iframe"
              style="display:none;">
            <input type="hidden" v-for="(val, key, idx) in formItems" :name="key" :value="val" />
            <input type="hidden" v-if="messageId" name="rule_message_id" :value="messageId" />
        </form>
    </div>
</template>

<script>
    export default {
        name: "MailPreviewPanel",

        data: function () {
            return {
                previewFormId: this.formId,
                formItems: this.initialFormItems
            };
        },

        props: {
            baseUrl: {
                type: String,
                required: true
            },
            isActive: {
                type: Boolean,
                default: false
            },
            formId: {
                type: String,
                default: 'mailPreviewForm'
            },
            messageId: {
                default: 0
            },
            subject: {
                default: ''
            },
            initialFormItems: {
                required: true,
                default: {
                    _token: {
                        type: String,
                        required: true
                    },
                }
            },
        },

        watch: {
            isActive: function (newVal, currentVal) {
                if (newVal === true) {
                    this.postPreview();
                }
            },
        },

        methods: {
            postPreview: function () {
                var formElm = document.getElementById(this.formId);

                if (formElm && formElm.tagName.toLowerCase() === 'form') {
                    formElm.submit();
                }
            },

            rebuildForm: function (formItemIds) {
                var vm = this;
                Array.prototype.forEach.call(formItemIds, function (selector, idx) {
                    var elem = document.getElementById(selector);

                    if (!elem) {
                        return;
                    }

                    if (elem.tagName.toLowerCase() === 'select'
                        || elem.tagName.toLowerCase() === 'textarea') {
                        if (!elem.value) {
                            Vue.delete(vm.formItems, elem.name)
                        } else {
                            Vue.set(vm.formItems, elem.name, elem.value);
                        }
                    }

                    // if (elem.tagName.toLowerCase() === 'textarea') {
                    //     Vue.set(vm.formItems, elem.name, elem.value);
                    // }

                    if (elem.tagName.toLowerCase() === 'input'
                        && elem.getAttribute('type') === 'text') {
                        Vue.set(vm.formItems, elem.name, elem.value);
                    }
                });
            }
        }
    }
</script>
