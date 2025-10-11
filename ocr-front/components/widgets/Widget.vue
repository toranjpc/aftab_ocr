<template>
  <v-col class="d-flex align-center mb-0" style="position: relative">
    <v-row
      class="ma-0 elevation-1 pa-0 product-item pa-2 rounded-lg white"
      style="max-width: 100%"
    >
      <v-col cols="8" class="pa-0 d-flex flex-column justify-center">
        <h4 class="px-3 font-weight-bold mt-3 d-flex align-self-first">
          {{ title }}
        </h4>
        <p class="px-3 color-3 my-2 mb-0">{{ subtitle }}</p>
      </v-col>
      <v-col cols="4" class="pa-0 d-flex justify-center align-center">
        <v-icon
          :color="color"
          :class="color + ' lighten-5'"
          class="pa-4 rounded-circle"
          size="30px"
        >
          fal fa-{{ icon }}
        </v-icon>
      </v-col>
    </v-row>
  </v-col>
</template>

<script>
export default {
  props: ["title", "icon", "subtitle", "color"],
};
</script>
