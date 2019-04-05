<template>
    <transition name="collapse-v" appear>
    <div v-bind:class="{'navi-message-box': true, 'bg-orange': hasNotice()}" v-show="messages.length && isActive">
        <ul>
            <li v-for="message in messages">
                <svg role="img" class="icon icon-circle-l icon-white">
                    <use v-bind:xlink:href="getTypeIcon(message.type)"></use></svg>
                {{ message.text }}
            </li>
        </ul>
    </div>
    </transition>
</template>

<script>
    /*
     * Display for 2000 ms then collapse.
     */
    export default {
        name: "AppInformPanel",

        data: function () {
            return {
                dismissTimer: false,
                isActive: true
            };
        },

        props: {
            messages: {
                type: Array,
                default: {
                    type: Object,
                    default: {
                        'type': 'info',
                        'text': ''
                    }
                }
            },
            activeTime: {
                type: Number,
                default: 2000
            }
        },

        created: function () {
            var vm = this;

            this.dismissTimer = window.setTimeout( function () {
                vm.isActive = false;
                window.clearTimeout(vm.dismissTimer);
            }, this.activeTime);
        },

        methods: {
            getTypeIcon: function (type) {
                return type === 'notice' ? '#caution' : '#info';
            },

            hasNotice: function () {
                var hasNotice = false;

                // NOTE: forEach has not 'break'...
                Array.prototype.forEach.call(this.messages, function (msg, idx) {
                    if (msg.type === 'notice') {
                        hasNotice = true;
                    }
                });

                return hasNotice;
            }
        }
    }
</script>
