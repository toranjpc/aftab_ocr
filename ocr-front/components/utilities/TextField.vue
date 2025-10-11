<template>
  <div class="text-justify" v-html="field.title"></div>
</template>

<script>
import { AbstractField } from "majra";

export default {
  name: "TextField",

  extends: AbstractField,
};
</script>
