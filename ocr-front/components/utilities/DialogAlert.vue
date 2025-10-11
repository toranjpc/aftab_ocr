<template>
  <v-dialog v-model="dialog" width="600">
    <v-card>
      <v-card-title class="pl-0 headline white--text py-1 secondary">
        <h6>حذف</h6>
        <v-spacer />
        <v-btn dark text @click="dialog = false">
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </v-card-title>

      <v-card-text class="mt-5">
        <h2 class="font-weight-bold">{{ data.text }}</h2>
        <DynamicForm v-if="fields.length > 0" :fields="fields" v-model="form" />
      </v-card-text>

      <v-divider></v-divider>

      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn class="px-6" color="success" small @click="dialog = false">
          <span>لغو</span>
        </v-btn>
        <v-btn class="px-6" color="error" small @click="ans()">
          <span>تایید</span>
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
import { DynamicForm } from 'majra'

export default {
  components: { DynamicForm },

  data: () => ({
    form: {},
    fields: [],
    alert: false,
    dialog: false,
    confirm: () => {},
    data: { text: '', color: '' },
  }),

  created() {
    this._listen(
      'DialogAlert',
      (payload) => {
        if (payload.fields) {
          this.fields = payload.fields
        }

        this.form = {}
        this.alert = true
        this.dialog = true
        this.data = payload
        this.confirm = payload.confirm
      },
      true
    )
  },

  methods: {
    ans() {
      this.dialog = false
      this.confirm(this.form)
      this.confirm = () => {}
      this._event('DialogAlertAns', {
        ans: true,
      })
    },
  },
}
</script>
