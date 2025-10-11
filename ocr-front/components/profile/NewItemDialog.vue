<template>
  <v-dialog v-model="dialog" max-width="500">
    <v-card>
      <v-card-title>
        <span>{{ title }}</span>
        <v-spacer />
        <v-btn color="error" icon @click="dialog = false">
          <v-icon>fal fa-times</v-icon>
        </v-btn>
      </v-card-title>
      <v-card-text class="mt-2">
        <DynamicForm v-model="form" :fields="fields" />
        <div class="d-flex px-2">
          <v-spacer />
          <v-btn class="mt-5" small :loading="loading" color="info" @click="save">
            <v-icon small left>fal fa-plus</v-icon>
            <span>ذخیره</span>
          </v-btn>
        </div>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<script>
import { DynamicForm } from "majra";
import { get as getSafe } from "lodash";

export default {
  components: { DynamicForm },

  data: () => ({
    dialog: false,
    form: {},
    model: "",
    title: "",
    field: "",
    server: true,
    loading: false,
    fields: [{ title: "عنوان", field: "name", type: "text" }],
  }),

  created() {
    this._listen(
      "newItem",
      ({ model, field, title, server }) => {
        this.dialog = true;
        this.model = model;
        this.field = field;
        this.title = title;
        if (server === false) {
          this.server = false;
        }
      },
      true
    );
  },

  methods: {
    save() {
      if (!this.server) {
        return this.saveWithoutReq();
      }

      this.loading = true;

      this.$axios
        .$post("/" + this.model, { [this.model]: this.form })
        .then((res) => {
          this._event("alert", { text: getSafe(res, "message"), color: "success" });
          const newItem = getSafe(res, this.model);
          this.$majra.addNewItem({ key: this.model, value: newItem });
          this._event("changeField." + this.model, {
            field: this.field,
            value: getSafe(newItem, "id"),
          });
        })
        .catch((err) => {
          this._event("alert", {
            text: getSafe(err, "response.data.message"),
            color: "error",
          });
        })
        .finally(() => {
          this.loading = false;
          this.dialog = false;
          this.form.name = "";
        });
    },

    saveWithoutReq() {
      this._event("alert", { text: "اضافه شد", color: "success" });
      this.$majra.addNewItem({ key: this.model, value: this.form.name });
      this._event("changeField." + this.model, {
        field: this.field,
        value: this.form.name,
      });
      this.loading = false;
      this.dialog = false;
      this.form.name = "";
    },
  },
};
</script>
