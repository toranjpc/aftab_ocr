<template>
  <v-card style="cursor: pointer" :class="{ 'active-inline-card': active }">
    <v-card-text class="d-flex justify-center align-center" style="font-size: 1rem">
      <i class="ml-2" :class="icon"></i>
      <div>
        <slot></slot>
      </div>
    </v-card-text>
  </v-card>
</template>

<script>
export default {
  name: "MenuItem",

  props: { icon: { default: "" }, active: { default: false } },
};
</script>

<style scoped></style>
