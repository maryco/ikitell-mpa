<template>
    <select v-model="selected" :name="name" :id="id">
        <option value="" v-show="placeHolder">{{ placeHolder }}</option>
        <option v-for="item in items"
                v-bind:value="item.value"
                v-bind:key="item.value">
            {{ item.text }}
        </option>
    </select>
</template>

<script>
    /*
     * Default row object structure.
     */
    const defaultItemStructure = { text: '', value: '' };

    export default {
        name: "CustomSelect",

        data: function () {
            return {
                selected: this.initialSelected,
                selectedItem: undefined,
                items: this.initialItems
            };
        },

        props: {
            initialItems: {
                type: Array,
                default: {
                    type: Object,
                    default: defaultItemStructure
                }
            },
            itemStructure: {
                type: Object,
                required: true
            },
            initialSelected: {
                type: String,
                default: ''
            },
            placeHolder: {
                type: String,
                default: ''
            },
            name: {
                type: String
            },
            id: {
                type: String
            },
            refSyncContent: {
                type: String
            }
        },

        mounted: function () {
            this.setSelectedItem();
        },

        watch: {
            selected: function () {
                this.setSelectedItem();
            }
        },

        methods: {
            setSelectedItem () {
                var vm = this;

                Array.prototype.forEach.call(this.initialItems, function (item, idx) {
                    // NOTE: Use '===' is too strict..
                    if (vm.selected == item.value) {
                        vm.selectedItem = item;
                    }
                });

                // Set default
                if (!this.selected) {
                    this.selected = '';
                    this.selectedItem = this.itemStructure;
                }
            },
        }
    }
</script>
