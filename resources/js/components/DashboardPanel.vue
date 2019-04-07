<template>
    <collapse-panel
        v-bind:panelBaseClass="basePanelClass"
        v-bind:initial-open="isOpen">
        <div slot="panelTitle" class="panel-title">
            <div class="title-with-icon-m">
                <svg role="img"
                     v-if="usePresetImage()"
                     class="icon-m icon-circle-l bg-whitish"
                     style="border: none;"
                     :class="deviceImage.class">
                    <use :xlink:href="'#' + deviceImage.ref"></use>
                </svg>
                <svg v-if="isAlert" role="img" class="badge badge-alert"><use xlink:href="#caution"></use></svg>
                <h3>{{ initialDeviceInfo.name }}</h3>
            </div>
            <span class="text-xs sp-hide">最終更新日時 /<br class="md-break"> {{ deviceInfo.lastResetAt }}</span>
        </div>

        <template slot="panelBody">
            <div class="limit-gauge-box">
                <circle-gauge
                    :base-width="300"
                    :initial-gauge-pct="initialGaugePct"
                    :renew-gauge-pct="renewGaugePct"></circle-gauge>
                <div class="gauge-timer-info">
                    <p>{{ deviceInfo.remainingTime }}<span class="unit">h</span></p>
                    <span class="text-s">{{ deviceInfo.resetLimitAt }}</span>
                </div>
            </div>
            <div class="operation-box">
                <a href="#" class="btn" :class="[(isRunning || !enableReset) ? 'btn-disabled' : 'btn-theme-orange']"
                   v-on:click.prevent="onResetGauge()"
                   :disabled="(isRunning || !enableReset)">{{ deviceInfo.resetWord }}</a>
                <a :href="editPath" class="btn btn-theme-main">設定編集</a>
            </div>
        </template>
    </collapse-panel>
</template>

<script>
    export default {
        name: "DashboardPanel",

        data: function () {
            return {
                initialGaugePct: Math.round((this.initialDeviceInfo.remainingTime / this.initialDeviceInfo.limitTime) * 100),
                renewGaugePct: 0,
                isRunning: false,
                isOpen: this.isFirst,
                isAlert: this.initialDeviceInfo.isAlert,
                isSuspend: this.initialDeviceInfo.isSuspend,
                isDemo: this.initialDeviceInfo.isDemo,
                deviceInfo: this.initialDeviceInfo,
                deviceImage: this.initialDeviceInfo.image,
                basePanelClass: {
                    'panel-theme-main': !this.initialDeviceInfo.isAlert && !this.initialDeviceInfo.isSuspend,
                    'panel-theme-alert': this.initialDeviceInfo.isAlert,
                    'panel-theme-inactive': !this.initialDeviceInfo.isAlert && this.initialDeviceInfo.isSuspend
                },
                editPath: (this.initialDeviceInfo.isDemo) ? '#' : '/device/'+this.initialDeviceInfo.id+'/edit',
                reportPath: (this.initialDeviceInfo.isDemo) ? '/device/try/report' : '/device/'+this.initialDeviceInfo.id+'/report'
            };
        },

        props: {
            isFirst: {
                type: Boolean,
                default: false
            },
            initialDeviceInfo: {
                type: Object,
                required: true,
                default: {
                    id: '',
                    deviceImage: undefined,
                    name: '',
                    lastResetAt: '',
                    isAlert: false,
                    isSuspend: false,
                    isDemo: false,
                    enableReset: true,
                    remainingTime: 0,
                    limitTime: 0,
                    resetLimitAt: '',
                    resetWord: 'Reset Timer'
                }
            }
        },

        computed: {
            enableReset: function () {
                 return this.deviceInfo.enableReset;
             }
        },

        components: {
            'circle-gauge': require('./CircleGauge.vue').default,
            'collapse-panel': require('./CollapsePanel.vue').default
        },

        watch: {
            isOpen: function () {
                this.basePanelClass = this.resetBasePanelClass();
            },
            isAlert: function () {
                this.basePanelClass = this.resetBasePanelClass();
            },
            isRunning: function (newVal, oldVal) {
                if (newVal === true) {
                    this.$root.$emit('show-loading');
                } else {
                    this.$root.$emit('dismiss-loading');
                }
            }
        },

        methods: {
            onResetGauge: function () {
                if (!this.enableReset) {
                    return false;
                }

                var vm = this;

                this.isRunning = true;

                axios
                    .post(vm.reportPath, {})
                    .then(function (res) {
                        //console.log('Response(status) : ' + res.status);
                        //console.log('Response(data) : ' + res.data.message);
                        if (res.status == 200) {
                            vm.renewGaugePct = 100;
                            vm.isAlert = false;
                            vm.deviceInfo = res.data.deviceInfo;
                        }

                        // For iOS 9 finally issue(?)
                        vm.isRunning = false;
                    })
                    .catch(function (err) {
                        var msg = 'なんらかのエラーが発生しました [CODE:' + err.response.status + ']';
                        vm.$root.showInstMessage(msg, window.$ikitell.MSG_THEMES.WARNING);

                        // For iOS 9 finally issue(?)
                        vm.isRunning = false;
                    })
                    .finally(function () {
                        // NOTE: 'finally' is not working iOS 9?
                        vm.isRunning = false;
                    });
            },

            resetBasePanelClass: function () {
                return {
                    'panel-theme-main': !this.isAlert && !this.isSuspend,
                    'panel-theme-alert': this.isAlert,
                    'panel-theme-inactive': !this.isAlert && this.isSuspend
                };
            },

            usePresetImage: function () {
                if (this.deviceImage === undefined
                    || null === this.deviceImage) {
                    return false;
                }

                return this.deviceImage.hasOwnProperty('class') && this.deviceImage.hasOwnProperty('ref');
            }
        }
    }
</script>
