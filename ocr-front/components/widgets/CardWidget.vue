<template>
  <v-card style="height: 100%">
    <v-card-title style="width: 100%">
      <div
        class="primary"
        style="
          height: 40px;
          padding: 2px;
          border-radius: 10px 0px 0px 10px;
          position: absolute;
          right: 0;
        "
      ></div>
      <span v-if="$slots.title"><slot name="title"></slot></span>
      <span v-else v-html="title"></span>
      <v-spacer></v-spacer>
      <slot name="actions"></slot>
    </v-card-title>
    <v-card-text><slot></slot></v-card-text>
  </v-card>
</template>

<script>
export default {
  props: { title: {} },
}
</script>
