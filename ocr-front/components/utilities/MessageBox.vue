<template>
  <v-dialog max-width="500" v-model="dialog">
    <v-card id="dialog">
      <v-card-title>
        <span ref="mockTitle">{{ data.title }}</span>
        <v-spacer />
        <v-btn color="error" icon @click="dialog = false">
          <v-icon>fal fa-times</v-icon>
        </v-btn>
      </v-card-title>
      <v-card-text class="mt-4" v-html="data.content"></v-card-text>
    </v-card>
  </v-dialog>
</template>

<script>
export default {
  name: "MessageBox",

  data: () => ({
    dialog: false,
    keys: [],
    data: { title: "", content: "" },
  }),

  created() {
    this._listen(
      "messageBox",
      (payload) => {
        if (this.keys.includes(payload.key)) return;
        this.data = payload;
        this.dialog = true;
        this.keys.push(payload.key);
        this.$nextTick(() => {
          if (this.$refs.mockTitle) this.$refs.mockTitle.click();
        });
      },
      true
    );
  },
};
</script>
