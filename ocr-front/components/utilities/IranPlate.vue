<template>
  <div class="w-100 pa-2 rounded-lg justify-center d-flex flex-wrap" style="border: 1px dashed rgba(0, 0, 0, 0.2)">
    <h2 class="font-weight-bold">پلاک ایران</h2>
    <div class="col-12 px-0 custom-plate">
      <DynamicForm v-model="form" :fields="fields" :edit-item="editItem" />
    </div>
    <!-- <IranPlateView :plate="form" /> -->
  </div>
</template>

<script>
import { DynamicForm } from 'majra'
import { iranPlateLetters } from '@/helpers/plateInfo'
import validation from '@/helpers/validations'

export default {
  components: { DynamicForm },

  props: {
    plateAttributes: {
      type: Object,
    },
  },

  data: () => ({
    form: {
      letter: 'ein',
      type: 'iran',
    },
    fields: [
      {
        title: '',
        field: 'third',
        type: 'text',
        col: { md: 3 },
        props: {
          id: 'in3',
          type: 'number',
          rules: [validation.digits(2)],
          autofocus: false,
          placeholder: '78',
        },
      },
      {
        title: '',
        field: 'second',
        type: 'text',
        col: { md: 4 },
        props: {
          id: 'in2',
          type: 'number',
          rules: [validation.digits(3)],
          placeholder: '365',
        },
        events: {
          input: (...args) => {
            if (args[0].length === 3) {
              document.getElementById('in3')?.focus()
            }
          },
        },
      },
      {
        title: 'حرف',
        field: 'letter',
        type: 'select',
        rel: false,
        values: iranPlateLetters,
        col: { md: 3 },
        default: 'ein',
      },
      {
        title: '',
        field: 'first',
        type: 'text',
        col: { md: 2 },
        props: {
          id: 'in1',
          type: 'number',
          rules: [validation.digits(2)],
          autofocus: true,
          placeholder: '87',
        },
        events: {
          input: (...args) => {
            if (args[0].length === 2) {
              document.getElementById('in2')?.focus()
            }
          },
        },
      },
    ],
    editItem: {},
  }),

  watch: {
    form(newVal) {
      const normalPlate =
        newVal.first + (newVal.letter || 'ein') + newVal.second + newVal.third

      const isValid = this.isValid({
        first: newVal.first,
        letter: newVal.letter || 'ein',
        second: newVal.second,
        third: newVal.third,
      })

      this.$emit('update', { type: 'iran', normalPlate, isValid })
    },
  },

  created() {
    if (
      typeof this.plateAttributes !== 'object' &&
      this.plateAttributes.normalPlate
    )
      return

    const plateNumber = this.plateAttributes.normalPlate

    const first = plateNumber.substr(0, 2)
    const letter = plateNumber.substr(2, 3)
    const second = plateNumber.substr(5, 3)
    const third = plateNumber.substr(8, 2)

    this.editItem = {
      third,
      second,
      letter,
      first,
    }
  },

  methods: {
    isValid({ first, second, third }) {
      first = +first
      second = +second
      third = +third

      return (
        typeof first === 'number' &&
        typeof second === 'number' &&
        typeof third === 'number' &&
        first.toString().length === 2 &&
        second.toString().length === 3 &&
        third.toString().length === 2
      )
    },
  },
}
</script>

<style>
@media only screen and (max-width: 1000px) {
  .custom-plate .row {
    flex-direction: column-reverse !important;
  }
}

.custom-plate>form>div>div {
  padding-left: 2px !important;
  padding-right: 2px !important;
}
</style>
