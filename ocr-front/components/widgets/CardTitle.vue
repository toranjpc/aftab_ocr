<template>
  <v-card style="position: relative" outlined class="shadow">
    <div
      style="position: absolute; left: 0; top: -25px; width: 100%"
      class="d-flex justify-center"
    >
      <div class="primary px-4 py-1 rounded-lg">{{ title }}</div>
    </div>
    <slot></slot>
  </v-card>
</template>

<script>
export default {
  props: ['title'],
}
</script>

<style>
.shadow {
  transition: all 0.5s;
}
.shadow:hover {
  box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.5);
}
</style>
