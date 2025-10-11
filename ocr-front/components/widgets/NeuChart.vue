<template>
  <div class="neumorphic-chart">
    <div class="chart-container">
      <svg class="chart" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
        <circle class="outer-circle" r="40" cx="50" cy="50" />
        <circle
          class="inner-circle"
          :style="{ strokeDasharray: circumference, strokeDashoffset: offset }"
          r="40"
          cx="50"
          cy="50"
        />
        <text class="percentage" x="50" y="58">{{ percentage }}%</text>
      </svg>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    percentage: {
      type: Number,
      required: true,
    },
  },
  computed: {
    circumference() {
      return Math.PI * 2 * 40;
    },
    offset() {
      return this.circumference * (1 - this.percentage / 100);
    },
  },
};
</script>

<style scoped>
.neumorphic-chart {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 120px;
  height: 120px;
  border-radius: 10000px;
  box-shadow: 5px 5px 12px #dbdbdb, -5px -5px 12px #ffffff;
}

.chart-container {
  position: relative;
  width: 100%;
  height: 100%;
}

.chart {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
}

.outer-circle {
  fill: none;
  stroke: #c5c5c5;
  stroke-width: 13;
}

.inner-circle {
  fill: none;
  stroke: #00bcd4;
  stroke-width: 13;
  stroke-linecap: round;
  transition: stroke-dasharray 0.5s ease-out, stroke-dashoffset 0.5s ease-out;
}

.percentage {
  font-size: 28px;
  text-anchor: middle;
  fill: #555;
}
</style>
