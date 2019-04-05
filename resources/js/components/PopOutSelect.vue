<template>
    <div class="form-item-group">
        <slot name="before"></slot>
        <select
            :id="selectorId"
            v-model="selected"
            :class="[{'animated bounce fast': isItemChanged}]"
            readonly="readonly">
            <option value="">{{ placeHolder }}</option>
            <option
                v-for="option in items"
                :value="option.value"
                v-show="!option.isPop"
                v-bind:key="option.value">
                {{ option.text }}
            </option>
        </select>
        <div class="form-item-right">
            <button type="button"
                    class="btn btn-inline-icon-only btn-theme-single-green"
                    v-on:click.prevent="popOut">
                <svg role="img" class="icon-center">
                    <use xlink:href="#fetch"></use>
                    <title>Add</title>>
                </svg>
            </button>
        </div>
        <slot name="after"></slot>
        <instant-message
            ref="maxMsg"
            v-bind:theme="maxMsgTheme"
            v-bind:message="maxMessage">
        </instant-message>
    </div>
</template>

<script>
    /*
     * Default row object structure.
     */
    const popListStructure = {text: '', value: '', isPop: false};

    export default {
        name: "PopOutSelect",

        data: function () {
            return {
                selected: '',
                items: this.initialItems,
                isItemChanged: false,
                maxMsgTheme: $ikitell.MSG_THEMES.WARNING
            };
        },

        computed: {
            poppedCount: function () {
                var counter = 0;
                Array.prototype.forEach.call(this.items, function (item, idx) {
                    if (item.isPop === true) {
                        counter = counter + 1;
                    }
                });
                return counter;
            },
            isMaxOut: function () {
                return this.maxOut > 0 && this.poppedCount >= this.maxOut;
            }
        },

        props: {
            initialItems: {
                type: Array,
                default: [popListStructure]
            },
            selectorId: {
                type: String
            },
            placeHolder: {
                type: String,
                default: ''
            },
            maxOut: {
                type: Number,
                default: -1
            },
            maxMessage: {
                type: String,
                default: 'これ以上追加できません'
            }
        },

        methods: {
            popOut: function () {
                if (this.isMaxOut) {
                    this.$refs['maxMsg'].show();
                    return;
                }

                var vm = this;
                Array.prototype.forEach.call(vm.items, function (item, idx) {
                    if (vm.selected === item.value) {
                        vm.items[idx].isPop = true;
                        vm.selected = "";
                    }
                });
            },

            putBack: function (value) {
                var vm = this;
                Array.prototype.forEach.call(vm.items, function (item, idx) {
                    if (item.value === value) {
                        /*
                         * NOTE: If list is no reactive, see below and use splice().
                         * https://jp.vuejs.org/v2/guide/list.html#%E6%B3%A8%E6%84%8F%E4%BA%8B%E9%A0%85
                         */
                        vm.items[idx].isPop = false;
                    }
                });
                this.isItemChanged = true;
            },

            clearReaction: function () {
                this.isItemChanged = false;
            }
        }
    }
</script>
