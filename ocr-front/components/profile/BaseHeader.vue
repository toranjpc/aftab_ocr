<template>
  <v-stepper-header>
    <template v-for="(title, index) in steps">
      <v-stepper-step
        color="green"
        :key="`${index + 1}-step`"
        :complete="value > index + 1"
        :step="index + 1"
        editable
      >
        {{ title }}
      </v-stepper-step>

      <v-divider v-if="index + 1 !== steps" :key="index + 1"></v-divider>
    </template>
    <v-progress-linear
      color="green"
      :value="(value / steps.length) * 100"
    ></v-progress-linear>
  </v-stepper-header>
</template>

<script>
export default {
  props: ["value", "steps"],
};
</script>

<style>
@media only screen and (max-width: 959px) {
  .v-stepper:not(.v-stepper--vertical) .v-stepper__label {
    display: flex !important;
    margin-right: 10px;
  }
}
</style>
