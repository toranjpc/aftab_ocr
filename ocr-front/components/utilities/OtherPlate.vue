<template>
  <div
    class="w-100 pa-2 justify-center d-flex flex-wrap"
    style="border: 1px dashed rgba(0, 0, 0, 0.2)"
  >
    <h2 class="font-weight-bold">پلاک کشور های دیگر</h2>
    <div class="col-12">
      <DynamicForm v-model="form" :fields="fields" :edit-item="editItem" />
    </div>
  </div>
</template>

<script>
import { DynamicForm } from 'majra'
import validations from '~/helpers/validations'

export default {
  components: { DynamicForm },

  props: {
    plateAttributes: {
      type: Object,
      required: true,
    },
  },

  data: () => ({
    form: {},
    fields: [
      {
        title: 'شماره پلاک',
        field: 'number',
        type: 'text',
        col: { md: 12 },
      },
    ],
    editItem: { number: null },
  }),

  watch: {
    form(newVal) {
      const normalPlate = newVal.number

      this.$emit('update', { type: 'other', normalPlate, isValid: false })
    },
  },

  created() {
    this.editItem = { number: this.plateAttributes.normalPlate }
  },
}
</script>
