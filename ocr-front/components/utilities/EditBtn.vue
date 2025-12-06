<template>
  <div>
    <v-btn icon small>
      <v-icon small @click="dialog = true">fal fa-edit</v-icon>
    </v-btn>

    <v-dialog v-model="dialog" max-width="600">
      <v-card id="dialog">
        <v-card-title>
          <span>ویرایش</span>
          <v-spacer />
          <v-btn color="error" icon @click="dialog = false">
            <v-icon>fal fa-times</v-icon>
          </v-btn>
        </v-card-title>
        <v-card-text class="mt-4">
          <DynamicForm v-model="form" :fields="fields" :edit-item="editItem" />
        </v-card-text>
        <v-card-actions>
          <v-btn small class="px-6" color="success" @click="edit">
            ویرایش
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
import { DynamicForm } from 'majra'

export default {
  components: { DynamicForm },

  props: {
    editItem: {},
    fields: { default: () => [] },
  },

  data() {
    return {
      dialog: false,
      form: {},
    }
  },

  methods: {
    async edit() {
      try {
        this._event('loading')

        if ('plate_number_edit' in this.form) {
          this.form.plate_number_edit = this.form?.plate_number_edit?.normalPlate
          this.form.type = this.form?.plate_number_edit?.type
        }

        const res = await this.$axios.$patch('/ocr-match/' + this.form.id, {
          OcrMatch: this.form,
        })
        console.log(res)

        this.$emit('item-updated', res.data)

        this._event('loading', false)
        this.dialog = false

        this._event("alert", {
          text: 'تغییرات با موفقیت ذخیره شد',
          color: "green",
        });

      } catch (error) {
        this._event('loading', false);

        let errorMessage = "خطایی رخ داده است";

        if (error.response) {
          errorMessage = error.response.data.message || "خطای سرور";
        } else if (error.request) {
          errorMessage = "پاسخی از سرور دریافت نشد";
        }

        this._event("alert", {
          text: errorMessage,
          color: "red",
        });

      }
    },
  }
}
</script>
