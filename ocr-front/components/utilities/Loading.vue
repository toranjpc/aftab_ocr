<template>
  <v-overlay z-index="500" :value="loading">
    <v-icon class="mx-auto">fal fa-spinner fa-spin</v-icon>
  </v-overlay>
</template>

<script>
export default {
  data() {
    return {
      loading: false
    };
  },
  created() {
    this._listen("loading", (loading = true) => {
      this.loading = loading;
    }, true);
  }
};
</script>
