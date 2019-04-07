<template>
    <div class="modal-wrap-screen"
         v-show="isActive" @click.self="dismiss"
         v-bind:style="{ height: fullHeight + 'px' }">
        <transition name="animatecss-appear-v"
                    enter-active-class="animated fadeInDownBig"
                    leave-active-class="animated fadeOutUp"
                    v-on:after-leave="hideWrapScreen"
                    v-on:after-enter="adjustPosition">
            <div class="modal"
                 v-show="isActiveInner"
                 v-bind:style="{ position: 'relative', top: innerPosY + 'px' }"
                 ref="modalContainer">
                <div class="modal-header">
                    <button name="close" type="button" class="btn-close act-close-model" @click.prevent.stop="dismiss"><svg role="img"><use xlink:href="#cross"></use></svg></button>
                </div>
                <div class="modal-body">
                    <slot></slot>
                </div><!--.modal-body-->
            </div><!--.modal-->
        </transition>
    </div><!--//.modal-wrap-screen-->
</template>

<script>
    export default {
        name: "ModalWindow",

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

                //debugHeights();

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
                // Issue event of the dismiss modal to the parent
                var target = (this.$attrs.id).replace('#cmp-modal-', '');
                this.$root.$emit('hide-modal', target);
            },

            hideWrapScreen () {
                this.isActive = false;
            },

            adjustPosition () {
                var containerElem = this.$refs.modalContainer;

                if (!containerElem || containerElem.scrollHeight <= 0) {
                    console.log('Not found container or height attr is not computed.');
                    return;
                }

                if (this.innerPosY + containerElem.scrollHeight > this.fullHeight) {
                    var surplus = this.innerPosY + containerElem.scrollHeight - this.fullHeight;
                    this.innerPosY = this.innerPosY - (surplus + 44);
                    window.scrollTo(0, this.innerPosY);
                }
            }
        }
    }

    // function debugHeights()
    // {
    //     console.log('window.outerHeight = ' + window.outerHeight);
    //     console.log('window.innerHeight = ' + window.innerHeight);
    //     console.log('document #app.clientHeight = ' + document.getElementById('app').clientHeight);
    //     console.log('document body.clientHeight = ' + document.getElementsByTagName('body')[0].clientHeight);
    // }
</script>
