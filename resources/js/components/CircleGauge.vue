<template>
    <div class="svg-wrapper">
        <svg :width="baseWidth" :height="baseWidth" :viewBox="'0 0 ' + baseWidth + ' ' + baseWidth">
            <filter id="dropShadowFilter">
                <feDropShadow dx="0" dy="0" stdDeviation="2" flood-color="#000000" flood-opacity="0.25" />
            </filter>
            <circle class="gauge-base"
                    :r="gaugeRadius"
                    :cx="centerX"
                    :cy="centerY"
                    :stroke-width="gaugeWidth">
            </circle>
            <circle class="gauge-base"
                    filter="url(#dropShadowFilter)"
                    :r="gaugeRadius"
                    :cx="centerX"
                    :cy="centerY"
                    :stroke-width="gaugeWidth">
            </circle>
            <circle class="gauge-scale" v-for="pos in scalePositions"
                    :r="scaleRadius"
                    :cx="pos.cx"
                    :cy="pos.cy">
            </circle>
            <path :d="gauge.pathDef"
                  fill="none"
                  stroke="#fa8658"
                  :stroke-width="gaugeWidth">

            </path>
        </svg>
        <!--<button v-on:click.prevent="onResetGauge()">Reset</button>-->
    </div>
</template>

<script>
    export default {
        name: "CircleGauge",

        data: function () {
            return {
                centerX: this.baseWidth / 2,
                centerY: this.baseWidth / 2,
                radius: this.baseWidth * 0.8 / 2,
                scalePositions: [],
                gaugeRadius: 0,
                gauge: {
                    color: "",
                    angle: 0,
                    pathDef: ""
                },
                gaugeAngle: 100
            }
        },

        props: {
            baseWidth: {
                type: Number,
                default: 300
            },
            bgColor: {
                type: String,
                default: '#cccccc'
            },
            gaugeWidth: {
                type: Number,
                default: 14
            },
            scaleRadius: {
                type: Number,
                default: 6
            },
            initialGaugePct: {
                type: Number,
                default: 100
            },
            renewGaugePct: {
                type: Number,
                default: 100
            }
        },

        mounted: function () {
            this.initializeData();
        },

        watch: {
            gaugeAngle: function () {
                function animate() {
                    if (Tween.update()) {
                        requestAnimationFrame(animate);
                    }
                }

                var vm = this;

                new Tween.Tween(this.gauge)
                    .to({angle: this.gaugeAngle}, 2000)
                    .onUpdate(function (gaugeObj) {
                        gaugeObj.pathDef = vm.makePathArc(gaugeObj.angle);
                    })
                    .start();

                animate();
            },

            renewGaugePct: function () {
                this.gaugeAngle = this.convertToAngle(this.renewGaugePct);
            }
        },

        methods: {
            initializeData: function () {
                this.scalePositions = this.generateGaugeScalePos(12);
                this.gaugeRadius = this.radius - (this.gaugeWidth / 2);

                // Show full angle gauge.
                this.gaugeAngle = this.convertToAngle(100);
                this.gauge.angle = this.gaugeAngle;
                this.gauge.pathDef = this.makePathArc(this.gaugeAngle);

                // Set initial angle.
                this.gaugeAngle = this.convertToAngle(this.initialGaugePct);
            },

            convertToAngle: function (pct) {
                if (pct <= 0) {
                    return -269;
                }
                if (pct >= 100) {
                    return 89;
                }

                return 360 * (pct / 100) - 270;
            },

            makePathArc: function (arcAngle) {
                var startX = this.centerX;
                var startY = this.centerY + this.gaugeRadius;

                var radian = arcAngle * Math.PI / 180;
                var largeArcFlag = (arcAngle >= -90) ? 1 : 0;

                var endX = this.centerX + Math.cos(radian) * this.gaugeRadius;
                var endY = this.centerY + Math.sin(radian) * this.gaugeRadius;

                return "M" + startX + " " + startY + " "
                    + "A " + this.gaugeRadius + " " + this.gaugeRadius + " 0 " + largeArcFlag + " 1 "
                    + endX + " " + endY;
            },

            generateGaugeScalePos: function (amount) {
                var positions = [];
                var posRadius = this.radius + this.scaleRadius + (this.gaugeWidth / 2);

                for (var i = 360 ; i >= 0 ; i -= 360 / amount) {
                    var radian = i * Math.PI / 180;
                    var cx = this.centerX + Math.cos(radian) * posRadius;
                    var cy = this.centerY + Math.sin(radian) * posRadius;
                    positions.push({cx: cx, cy: cy});
                }

                return positions;
            }
        }
    }
</script>

<style scoped>
    svg {
        display: block;
    }

    .gauge-base {
        stroke: #ffffff;
        fill: transparent;
    }

    .gauge-scale {
        fill: #ffffff;
    }

    .svg-wrapper svg {
        width: 100%;
        height: 100%;
    }
</style>
