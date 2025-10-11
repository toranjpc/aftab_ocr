<template>
  <FieldSet label="کد کانتینر" class="w-100 pa-2 rounded-lg justify-center d-flex flex-wrap">
    <div class="d-flex flex-column custom-container">
      <DynamicForm v-model="containerForm" :fields="containerFields" :edit-item="editItem" />
    </div>
  </FieldSet>
</template>

<script>
import { AbstractField, DynamicForm, FieldSet } from 'majra'

export default {
  extends: AbstractField,

  components: { DynamicForm, FieldSet },

  props: {
    value: {
      type: String,
      default: '',
      validator(val) {
        return val === '' || typeof val === 'string';
      }
    }
  },

  data() {
    return {
      containerForm: {
        part_1: '',
        part_2: '',
        part_3: '',
        part_4: ''
      },

      containerFields: [
        {
          title: 'حروف',
          field: 'part_1',
          type: 'text',
          col: { md: 3 },
        },
        {
          title: 'اعداد',
          field: 'part_2',
          type: 'text',
          col: { md: 3 },
        },
        {
          title: 'رقم کنترلی',
          field: 'part_3',
          type: 'text',
          col: { md: 3 },
        },
        {
          title: 'نوع',
          field: 'part_4',
          type: 'text',
          col: { md: 3 },
        },
      ],
      editItem: {},
      isInitialized: false
    }
  },

  watch: {
    containerForm(newVal) {
      // console.log(this.isInitialized)
      if (this.isInitialized)
        this.updateField(
          newVal.part_1 + '_' + newVal.part_2 + '_' + newVal.part_3 + '_' + newVal.part_4
        )
    }
  },

  mounted() {
    setTimeout(() => {
      this.parseContainerCode(this.value);
      this.isInitialized = true
    }, 500)
  },

  methods: {
    parseContainerCode(code) {
      if (code !== null) {
        code = code.split("_")
        this.editItem = {
          part_1: code[0],
          part_2: code[1],
          part_3: code[2],
          part_4: code[3]
        }
      }
    }
  },
}
</script>

<style>
@media only screen and (max-width: 1000px) {
  .custom-container .row {
    flex-direction: column-reverse !important;
  }
}

.custom-container .row {
  flex-direction: row-reverse !important;
}
</style>
