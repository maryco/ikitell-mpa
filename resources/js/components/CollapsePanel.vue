<template>
    <div class="layout-panel" :class="panelClassOptional">
        <slot name="panelTitle"></slot>

        <transition name="collapse-v">
            <div class="panel-body" :class="panelClassBody" v-show="isOpen">
                <slot name="panelBody"></slot>
            </div>
        </transition>

        <div class="panel-footer">
            <a href="#"
               :class="{ 'mark-allow-up': isOpen, 'mark-allow-down': !isOpen }"
               v-on:click.prevent="isOpen = !isOpen"></a>
        </div>
    </div><!--./layout-panel-->
</template>

<script>
    export default {
        name: "CollapsePanel",

        data: function () {
            return {
                isOpen: this.initialOpen,
                panelClassOptional: this.panelBaseClass,
                panelClassBody: this.panelBodyClass
            };
        },

        props: {
            initialOpen: {
                type: Boolean,
                default: false
            },
            panelBaseClass: {
                type: Object,
                default: function () {
                    return {};
                }
            },
            panelBodyClass: {
                type: Object,
                default: function () {
                    return {};
                }
            }
        }
    }
</script>
