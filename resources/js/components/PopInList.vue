<template>
    <transition-group name="animatecss-pop"
                      tag="ul"
                      enter-active-class="animated bounceIn fast"
                      leave-active-class="animated fadeOut fast"
                      v-on:after-leave="donePutBack">
        <li class="list-item-with-btn"
            v-for="item in items"
            v-show="item.isPop"
            v-bind:key="item.value">
                <slot name="list-content" :item="item"></slot>
                <input type="hidden" :name="hiddenSlotName" :value="item.value" v-bind:disabled="!item.isPop">
                <button type="button"
                        class="btn btn-inline-icon-only btn-theme-single-tint-orange-flip"
                        v-on:click.prevent="putBack(item.value)">
                    <svg role="image" class="icon-center">
                        <use xlink:href="#minus"></use><title>Remove</title>
                    </svg>
                </button>
        </li>
    </transition-group>
</template>

<script>
    /**
     * Use this component must with PopOutSelect compo to be pair.
     * Share the array data on the PopOutSelect compo by prop.refSharedListCompo.
     *
     * FIXME: "list-content" slot will access value (i.e. 'item.item.text')
     */

    export default {
        name: "PopInList",

        data: function () {
            return {
                items: this.initialItems,
                sharedListCompo: undefined
            };
        },

        props: {
            refSharedListCompo: {
                type: String,
                required: true
            },
            hiddenSlotName: {
                type: String
            },
            initialItems: {
                type: Array,
                default: [window.popListStructure]
            }
        },

        created: function () {
            this.items = this.$parent.$refs[this.refSharedListCompo].items;
            this.sharedListCompo = this.$parent.$refs[this.refSharedListCompo];
        },

        methods: {
            putBack: function (value) {
                this.sharedListCompo.putBack(value);
            },

            donePutBack: function () {
                this.sharedListCompo.clearReaction();
            }
        }
    }
</script>
