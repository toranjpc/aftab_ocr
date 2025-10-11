<template>
  <div class="w-100 pa-2 justify-center d-flex flex-wrap" style="border: 1px dashed rgba(0, 0, 0, 0.2)">
    <h2 class="font-weight-bold">پلاک افغان</h2>
    <div class="col-12">
      <DynamicForm v-model="form" :fields="fields" :edit-item="editItem" />
    </div>
    <AfghanPlateView :plate="form" />
  </div>
</template>

<script>
import { DynamicForm } from 'majra'
import AfghanPlateView from './AfghanPlateView.vue'
import { afghanCities } from '@/helpers/plateInfo'
import validations from '~/helpers/validations'

export default {
  components: { DynamicForm, AfghanPlateView },

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
        props: {
          type: 'number',
          rules: [validations.between(3, 5)],
        },
        col: { md: 6 },
      },
      {
        title: 'شهر',
        field: 'city',
        type: 'select',
        rel: false,
        values: afghanCities,
        col: { md: 6 },
      },
    ],
    editItem: { city: null, number: null },
  }),

  computed: {
    vehicle_number() {
      if (this.plateAttributes) return this.plateAttributes.normalPlate.split(',')
      else {
        return [null, null]
      }
    },
  },

  watch: {
    form(newVal) {
      const normalPlate = newVal.city + ',' + newVal.number + ',L'

      this.$emit('update', { type: 'afghan', normalPlate, isValid: false })
    },
  },

  created() {
    const plateNumber = this.plateAttributes.normalPlate
    const [city, number, l] = plateNumber.split(',')

    this.editItem = { city, number }
  },
}
</script>
