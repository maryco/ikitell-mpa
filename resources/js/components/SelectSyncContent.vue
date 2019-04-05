<template>
    <div :class="rootClass">
        <slot name="label"></slot>
        <slot name="content" :item="item"></slot>
    </div>
</template>

<script>
    /**
     * Use this component must with CustomSelect compo.
     * Referring the CustomSelect compo's data of 'selectedItem' for watch.
     */
    export default {
        name: "SelectSyncContent",

        data: function () {
            return {
                item: this.initialItem,
                refSelectCompo: undefined
            };
        },

        props: {
            rootClass: {
                type: String,
                default: ''
            },
            initialItem: {
                type: Object
            },
            refSelect: {
                type: String,
                required: true
            }
        },

        watch: {
            'refSelectCompo.selectedItem': function () {
                this.updateSelectedItem();
            }
        },

        mounted: function () {
            this.refSelectCompo = this.$root.$refs[this.refSelect];
            this.updateSelectedItem();
        },

        methods: {
            updateSelectedItem() {
                if (this.refSelectCompo && this.refSelectCompo.selectedItem !== undefined) {
                    this.item = this.refSelectCompo.selectedItem;
                }

                // console.log(this.refSelectCompo);
                // console.log(this.refSelectCompo.selectedItem);
            }
        }
    }
</script>
