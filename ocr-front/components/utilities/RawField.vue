<template>
  <div class="text-justify">
    <v-text-field :value="value" outlined dense readonly hide-details></v-text-field>
  </div>
</template>

<script>
import { AbstractField } from "majra";

export default {
  name: "RawField",

  extends: AbstractField,
};
</script>
