<template>
  <div class="rounded my-6 col-12 dashed-bordered">
    <div class="d-flex justify-center mb-4">
      <span
        class="text-center rounded elevation-3 py-1 px-8 dashed-bordered light-back"
        style="margin-top: -29px"
        v-html="title"
      >
      </span>
    </div>
    <slot></slot>

    <div class="d-flex flex-row justify-end">
      <v-btn
        v-if="items.length < count"
        elevation="0"
        text
        class="button-box"
        :block="$vuetify.breakpoint.mdAndDown"
        color="black "
        @click="$emit('add')"
      >
        <v-icon left>fal fa-plus</v-icon>
        <span>افزودن</span>
      </v-btn>
    </div>
    <!-- end item -->
  </div>
</template>

<script>
export default {
  props: {
    title: { default: () => "" },
    items: { default: () => [] },
    count: { default: 0 },
  },
};
</script>

<style>
.dashed-bordered {
  border: 1px dashed black;
}
.light-back {
  background-color: #f8f9fa;
}
</style>
