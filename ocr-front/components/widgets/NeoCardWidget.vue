<template>
  <div
    class="neo pa-5"
    :style="`position: relative; border: 1px solid var(--v-${color}-base)`"
  >
    <div
      class="d-flex flex-column align-center"
      style="position: absolute; top: 0; left: 0; width: 100%"
    >
      <div class="px-4 pa-1 white--text rounded-b-lg" :class="color">{{ title }}</div>
    </div>

    <slot></slot>

    <!-- <div class="shine"></div> -->
  </div>
</template>

<script>
export default {
  props: { title: {}, color: { default: "info" } },
};
</script>

<style scoped>
.neo {
  border-radius: 9px;
  background: #ffffff;
  box-shadow: 5px 5px 12px #dbdbdb, -5px -5px 12px #ffffff;
  overflow: hidden;
  transition: all 0.4s !important;
}
.neo:hover {
  transform: scale(1.02);
  box-shadow: 5px 5px 120px #dbdbdb, -5px -5px 12px #ffffff;
}
.neo:hover .shine {
  transform: skewX(20deg) translateX(1000px);
}
.shine {
  position: absolute;
  top: 0;
  left: -70px;
  height: 508px;
  width: 50px;
  background: rgba(255, 255, 255, 0.4);
  transition: all 0.3s linear;
  transform: skewX(20deg) translateX(0);
}
</style>
