<template>
    <transition name="animatecss-slide-h"
                enter-active-class="animated slideInRight fast"
                leave-active-class="animated slideOutRight faster"
                v-on:enter="changeVisibility(true)">
        <div class="layout-main-menu" :id="id">
            <slot></slot>
        </div>
    </transition>
</template>

<script>
    /**
     * This Component is not using.
     * @see menu.blade.php
     */
    export default {
        name: "MainMenuPanel",

        data: function () {
            return {
            };
        },

        props: {
            isActive: {
                type: Boolean,
                default: true
            },
            id: {
                required: true,
                default: ''
            }
        },

        methods: {
            changeVisibility: function (visible) {
                var container = document.getElementById(this.id);
                if (!container) {
                    return;
                }

                container.style.display = (visible) ? 'block' : 'none';
            }
        }
    }
</script>
