<template>
  <v-dialog max-width="500" v-model="dialog">
    <v-card id="dialog">
      <v-card-title>
        <span ref="mockTitle">خطا!</span>
        <v-spacer />
        <v-btn color="error" icon @click="dialog = false">
          <v-icon>fal fa-times</v-icon>
        </v-btn>
      </v-card-title>
      <v-card-text class="mt-4">
        <v-alert dark color="error" icon="fal fa-exclamation-triangle">
          وارد کردن موارد زیر الزامی است.
        </v-alert>
        <v-chip v-for="error in errors" :key="error" class="ma-1">
          {{ error }}
        </v-chip>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<script>
export default {
  name: 'ErrorBox',

  data: () => ({
    dialog: false,
    errors: [],
  }),

  created() {
    this._listen(
      'showErrors',
      (errors) => {
        this.dialog = true
        this.errors = errors
      },
      true
    )
  },
}
</script>
