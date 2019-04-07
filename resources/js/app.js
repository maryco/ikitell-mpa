
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

/**
 * Constants bag for register by components.
 * FIXME: Is better to define here ?
 */
window.$ikitell = {};

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

Vue.component('flat-pickr', VueFlatPickr);

Vue.component('custom-select', require('./components/CustomSelect.vue').default);
Vue.component('select-sync-content', require('./components/SelectSyncContent.vue').default);
Vue.component('confirmation-panel', require('./components/ConfirmationPanel.vue').default);
Vue.component('loading-panel', require('./components/LoadingPanel.vue').default);
Vue.component('collapse-panel', require('./components/CollapsePanel.vue').default);
Vue.component('instant-message', require('./components/InstantMessage.vue').default);
Vue.component('app-inform-panel', require('./components/AppInformPanel.vue').default);
Vue.component('custom-form-ajax', require('./components/CustomFormAjax.vue').default);
Vue.component('modal-window', require('./components/ModalWindow.vue').default);
Vue.component('pop-out-select', require('./components/PopOutSelect.vue').default);
Vue.component('pop-in-list', require('./components/PopInList.vue').default);
Vue.component('mail-preview-panel', require('./components/MailPreviewPanel.vue').default);
Vue.component('dashboard-panel', require('./components/DashboardPanel.vue').default);
Vue.component('device-image-picker', require('./components/DeviceImagePicker.vue').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',

    data: function () {
        return {
            // For MainMenuPanel
            isMenuActive: true,

            // For Common modalWindow
            posY: 0,
            modalStates: {
                login: false,
                register: false,
                deviceImage: false,
                mailPreview: false,
                confirmation: false,
                loading: false
            },

            // For Common instantMessage
            appInstMsg: '',
            appInstMsgTheme: $ikitell.MSG_THEMES.INFO,
            appInstMsgActiveTime: 3000
        };
    },

    computed: {
        isIE: function () {
            var isIE = document.querySelector('meta[name="seems-ie"]');
            return parseInt(isIE.getAttribute('content')) === 1;
        }
    },

    mounted: function () {
        this.$on('hide-modal', this.hideModal);
        this.$on('show-inst-message', this.showInstMessage);

        this.$on('show-loading', function () {
           this.showModal('loading');
        });
        this.$on('dismiss-loading', function () {
            this.hideModal('loading');
        });

        // Init menu visibility.
        // NOTE: It's use 'nextTick' because of the IE(11) sometimes menu disappear.
        // see: https://jp.vuejs.org/v2/guide/reactivity.html
        var vm = this;
        Vue.nextTick(function () {
            vm.isMenuActive = !vm.isMobile();
        });
    },

    methods: {
        /**
         * Check PC or Mobile depends on the element visibility.
         * @returns {boolean}
         */
        isMobile: function () {
            var spMenuBtn = document.getElementById('btnSpToggleMenu');
            if (!spMenuBtn) {
                return false;
            }

            var style = window.getComputedStyle(spMenuBtn);
            return style && style.display === 'block';
        },

        /**
         * @returns {HTMLElement}
         */
        getMainMenuElm: function () {
            return document.getElementById('panelMainMenu');
        },

        /**
         * Toggle Main Menu Panel
         */
        toggleMenu: function () {
            if (!this.isMobile) {
                return;
            }

            var menuElem = this.getMainMenuElm();
            if (!menuElem) {
                return;
            }

            // FIXME: Force show the menu
            // (Solution for the hide by the responsive style.)
            var style = window.getComputedStyle(menuElem);
            if (this.isMenuActive === true && (style && style.display === 'none')) {
                this.changeMenuVisibility(true);
                return;
            }

            this.isMenuActive = !this.isMenuActive;
        },

        /**
         * Switch the menu visibility.
         *
         * @param visible boolean
         */
        changeMenuVisibility: function (visible) {
            var menu = this.getMainMenuElm();
            if (!menu) {
                return;
            }

            menu.style.display = (visible) ? 'block' : 'none';
        },

        /**
         * For Modal Window Component
         *
         * @param name
         */
        showModal: function (name) {
            if (this.isIE) {
                this.posY = document.documentElement.scrollTop;
            } else {
                this.posY = window.scrollY;
            }

            this.modalStates[name] = true;
        },

        hideModal: function (name) {
            if (this.modalStates[name] === undefined) {
                return;
            }
            this.modalStates[name] = false;
        },

        showMailPreviewModal: function (formItemIds) {
            if (!this.$refs['mailPreview']) {
                this.showModal('mailPreview');
                return;
            }

            this.$root.$refs['mailPreview'].rebuildForm(formItemIds);

            var vm = this;
            Vue.nextTick(function () {
                vm.showModal('mailPreview');
            });
        },

        showConfirmationModel: function (formId, messages, onlyMessage = false) {
            if (!this.$refs['confirmationPanel']) {
                this.showModal('confirmation');
                return;
            }

            this.$root.$refs['confirmationPanel'].init(formId, messages, onlyMessage);

            var vm = this;
            Vue.nextTick(function () {
                vm.showModal('confirmation');
            });
        },

        /**
         * Update instantMessage props and show
         *
         * @param message
         * @param theme
         * @param activeTime
         */
        showInstMessage: function (message, theme, activeTime) {
            if (!this.$refs['appInstantMsg'] === undefined) {
                return;
            }

            this.appInstMsg = message;
            this.appInstMsgTheme = theme;

            if (activeTime && activeTime > 0) {
                this.appInstMsgActiveTime = activeTime;
            }

            this.$refs['appInstantMsg'].show();
        },

        activateButton: function (controlId, btnId) {
            // TODO: Support a ajax response.

            var targetBtn = document.getElementById(btnId);
            var controlElm = document.getElementById(controlId);

            if (controlElm === undefined) {
                return false;
            }

            if (targetBtn === undefined) {
                return;
            }

            var disabled = targetBtn.getAttribute('disabled');
            var classes = targetBtn.getAttribute('class');

            if (disabled) {
                classes = classes.replace(' btn-disabled', '');
                targetBtn.removeAttribute('disabled');
            } else {
                classes = classes + ' btn-disabled';
                targetBtn.setAttribute('disabled', true);
            }

            targetBtn.setAttribute('class', classes);
        }
    }
});
