<template>
    <transition name="animatecss-slide-h"
                enter-active-class="animated slideInLeft fast"
                leave-active-class="animated slideOutLeft faster"
                appear>
        <div :class="'instant-msg ' + themeClass"
             v-bind:style="{ display: 'block', position: 'fixed', top: appearPosY + 'px' }"
            v-show="isActive">
            {{ message }}
        </div>
    </transition>
</template>

<script>
    /**
     * TODO: UI修正(レスポンシブな位置)
     */

    /**
     * Available themes for a appearance.
     */
    window.$ikitell.MSG_THEMES = {
        INFO: 1,
        WARNING: 2
    };

    export default {
        name: "InstantMessage",

        data: function () {
            return {
                appearPosY: 20,
                dismissTimer: false,
                isActive: this.showInitial
            };
        },

        props: {
            message: {
                type: String,
                default: ''
            },
            theme: {
                type: Number,
                default: $ikitell.MSG_THEMES.INFO
            },
            activeTime: {
                type: Number,
                default: 2000
            },
            showInitial: {
                type: Boolean,
                default: false
            }
        },

        computed: {
            themeClass: function () {
                if (this.theme === $ikitell.MSG_THEMES.WARNING) {
                    return 'msg-warning';
                }
                return 'msg-info';
            }
        },

        created: function () {
            if (this.isActive === false) {
                return;
            }

            this.show();
        },

        methods: {
            show: function () {
                this.isActive = true;
                var vm = this;

                this.dismissTimer = window.setTimeout( function () {
                    vm.isActive = false;
                    window.clearTimeout(vm.dismissTimer);
                }, this.activeTime);
            }
        }
    }
</script>
