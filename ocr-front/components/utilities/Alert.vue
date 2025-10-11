<template>
  <v-snackbar v-model="alert" :color="data.color" timeout="6000">
    {{ data.text }}
    <template #action="{ attrs }">
      <v-btn text v-bind="attrs" @click="alert = false">
        <v-icon>fal fa-times</v-icon>
      </v-btn>
    </template>
  </v-snackbar>
</template>

<script>
export default {
  name: "Alert",

  data: () => ({
    alert: false,
    data: { text: "", color: "" },
  }),

  created() {
    this._listen(
      "alert",
      (payload) => {
        this.data = payload;
        this.alert = true;
      },
      true
    );
  },
};
</script>
