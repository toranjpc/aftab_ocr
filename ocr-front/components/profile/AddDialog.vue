<template>
  <v-dialog max-width="500" :value="value" @input="$emit('input', $event)">
    <v-card id="dialog">
      <v-card-title>
        <span ref="mockTitle">{{ title }}</span>
        <v-spacer />
        <v-btn color="error" icon @click="$emit('input', false)">
          <v-icon>fal fa-times</v-icon>
        </v-btn>
      </v-card-title>
      <v-card-text class="mt-4">
        <div class="d-flex flex-column">
          <v-form v-model="isValid">
            <slot></slot>
            <v-spacer />
            <v-btn
              :disabled="!isValid"
              small
              class="px-6"
              color="success"
              @click="[$emit('add'), $emit('input', false)]"
            >
              افزودن
            </v-btn>
          </v-form>
        </div>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<script>
export default {
  props: {
    value: { default: false },
    title: {},
  },

  data() {
    return {
      isValid: false,
    };
  },
};
</script>
