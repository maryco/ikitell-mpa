<template>
    <div class="modal-wrap-screen"
         v-show="isActive"
         v-bind:style="{ height: fullHeight + 'px' }">
        <transition name="animatecss-appear-zoom"
                    enter-active-class="animated zoomIn faster"
                    leave-active-class="animated zoomOut faster"
                    v-on:after-leave="hideWrapScreen">
            <div class="loading-indicator-v1" v-show="isActiveInner">
                <svg role="img" class="indicator-item"><use xlink:href="#ikitell"></use></svg>
            </div>
        </transition>
    </div><!--//.modal-wrap-screen-->
</template>

<script>
    export default {
        name: "LoadingPanel",

        data: function () {
          return {
              fullHeight: 0,
              innerPosY: this.scrolledPositionY,
              isActive: this.doActive,
              isActiveInner: false
          };
        },

        props: {
            doActive: false,
            scrolledPositionY: {
              type: Number,
              default: 0,
              required: true
            },
        },

        watch: {
            doActive: function (newCmd, currentCmd) {
                this.isActiveInner = newCmd;

                // Reset position and window height.
                this.innerPosY = this.scrolledPositionY;
                this.fullHeight = document.getElementsByTagName('body')[0].clientHeight;

                /**
                 * NOTE:
                 * Hide wrap screen must after modal container is hidden.
                 * So switch to false 'isActive' is at the method hideWrapScreen().
                 */
                if(newCmd === true) {
                    this.isActive = newCmd;
                }
            },
        },

        methods: {
            dismiss() {
                this.$root.$emit('hide-modal', 'loading');
            },

            hideWrapScreen () {
                this.isActive = false;
            },
        }
    }
</script>

<style scoped>
.loading-indicator-v1 {
    position: fixed;
    top: 200px;
    width: 100%;
    text-align: center;
}

.loading-indicator-v1 .indicator-item {
    fill: #fff;
    margin: 0 auto;
    animation: anm-rolling;
    -webkit-animation: anm-rolling infinite 1.2s ease-in-out;
}

@keyframes anm-rolling {
    from {transform: rotateZ(0deg);}
    to {transform: rotateZ(360deg);}
}
@-webkit-keyframes anm-rolling {
    from {transform: rotateZ(0deg);}
    to {transform: rotateZ(360deg);}
}
</style>
