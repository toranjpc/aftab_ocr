<template>
  <div class="d-flex flex-column">
    <span class="mb-2">{{ getFromField("title") }}</span>
    <div class="d-flex flex-row">
      <v-text-field
        :value="ashar"
        style="max-width: 90px"
        dense
        label="اعشار"
        outlined
        type="number"
        :rules="[validations.numBetween(0, 99), validations.isInt()]"
        @input="updateAshar"
      />
      <span class="px-2 mt-1"> / </span>
      <v-text-field
        :value="sahih"
        style="max-width: 90px"
        dense
        label="صحیح"
        outlined
        type="number"
        :rules="[validations.numBetween(12, 20), validations.isInt()]"
        @input="updateSahih"
      />
    </div>
  </div>
</template>

<script>
import { AbstractField } from "majra";
import validations from "~/helpers/validations";

export default {
  extends: AbstractField,

  data: () => ({
    validations,
  }),

  computed: {
    sahih() {
      const splited = (this.value + "").split(".");
      return +splited[0] >= 0 ? +splited[0] : 0;
    },
    ashar() {
      const splited = (this.value + "").split(".");

      return splited.length > 1 ? +splited[1] : 0;
    },
  },

  methods: {
    updateAshar(value) {
      this.updateField(this.sahih + "." + value);
    },
    updateSahih(value) {
      this.updateField(value + "." + this.ashar);
    },
  },
};
</script>
