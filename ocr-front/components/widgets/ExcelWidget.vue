<template>
  <v-dialog v-model="dialog" max-width="500">
    <v-card>
      <v-card-title>
        <span>آپلود فایل اکسل</span>
        <v-spacer />
        <v-btn color="error" icon @click="dialog = false">
          <v-icon>fal fa-times</v-icon>
        </v-btn>
      </v-card-title>
      <v-card-text>
        <DynamicForm v-model="form" :fields="fields" />
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn class="px-10" small color="success" @click="save"> ثبت </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
import { DynamicForm } from "majra";

export default {
  components: { DynamicForm },

  props: {
    route: {},
    extraData: {},
  },

  data() {
    return {
      fields: [
        {
          title: "فایل",
          field: "excel",
          type: "uploadbox",
          uploadPath: process.env.baseURL + "api/upload-file",
        },
      ],
      form: {},
      dialog: false,
    };
  },

  created() {
    this._listen("excelDialog", () => {
      this.dialog = true;
    });
  },

  methods: {
    async save() {
      await this.$axios.$post(this.route, { ...this.form, ...this.extraData });
      this.dialog = false;
    },
  },
};
</script>
