<template>
    <div class="form-set-image-picker">
        <div class="image-preview">
            <svg role="img" :class="pickedItem.class">
                <use v-bind:xlink:href="'#' + pickedItem.ref"></use>
            </svg>
        </div>
        <button type="button"
                class="btn btn-inline-icon-only btn-theme-single-main"
                v-on:click.prevent="$root.showModal('deviceImage')">
            <svg role="img" class="icon-center">
                <use xlink:href="#edit"></use>
                <title>Edit Image</title>>
            </svg>
        </button>

        <modal-window v-bind:do-active="$root.modalStates.deviceImage"
                      v-bind:scrolled-position-y="$root.posY"
                      id="#cmp-modal-deviceImage">
            <div class="form-items-s">
                <div class="form-item-group">
                    <ul class="layout-h-btn-box">
                        <li class="btn btn-theme-single-main">
                            <a href="#" v-on:click.prevent="dismiss(picked)">OK</a></li>
                    </ul>
                </div>

                <div class="form-item-group">
                    <ul class="form-panel-radio">
                        <li class="panel-radio-item" v-for="preset in presetImages">
                            <input type="radio" name="device_image_preset" class=""
                                   v-model="picked"
                                   :id="preset.key"
                                   :value="preset.value"
                                   @click="updatePickedItem(preset.value)">
                            <label :for="preset.key">
                                <svg role="img" class="radio-label" :class="preset.class" aria-hidden="true">
                                    <use v-bind:xlink:href="'#' + preset.ref"></use>
                                </svg>
                            </label>
                        </li>
                    </ul>
                </div>
            </div>
        </modal-window>

    </div>
</template>

<script>
    export default {
        name: "DeviceImagePicker",

        date: function () {
            return {
                pickedItem: this.getPresetItem(this.initialPicked)
            };
        },

        props: {
            presetImages: {
                type: Array,
                default: function () {
                    return [
                        {
                            key: 'presetImg001',
                            ref: 'mobile',
                            class: 'device-icon-mobile-1',
                            value: '1',
                        }
                    ];
                }
            },
            initialPicked: {
                default: 1
            }
        },

        created: function () {
            this.picked = this.initialPicked;
            this.updatePickedItem(this.initialPicked);
        },

        methods: {
            dismiss: function (value) {
                if (value !== undefined) {
                    this.updatePickedItem(value);
                }

                this.$root.$emit('hide-modal', 'deviceImage');
            },

            getPresetItem: function (value) {
                var vm = this;
                var pickedItem = undefined;

                Array.prototype.forEach.call(this.presetImages, function (item, idx) {
                    if (String(item.value) === String(value)) {
                        pickedItem = item;
                    }
                });

                return pickedItem;
            },

            updatePickedItem: function (value) {
                var preset = this.getPresetItem(value);
                if (preset !== undefined) {
                    this.pickedItem = preset;
                } else {
                    this.pickedItem = this.presetImages[0];
                }
            }
        }
    }
</script>
